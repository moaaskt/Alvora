<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\AdminBilling;
use App\Livewire\Company\CompanyBilling;
use App\Models\Company;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class BillingWebhookAndDashboardTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper to generate valid Mercado Pago HMAC x-signature header.
     */
    protected function generateSignatureHeaders(string $dataId): array
    {
        $ts = time();
        $requestId = 'req_' . uniqid();
        $secret = config('services.mercadopago.webhook_secret', 'fake-webhook-secret');
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $hash = hash_hmac('sha256', $manifest, $secret);

        return [
            'x-signature' => "ts={$ts},v1={$hash}",
            'x-request-id' => $requestId,
        ];
    }

    public function test_webhook_payment_approved_activates_company_and_quota(): void
    {
        $unique = uniqid();
        $company = Company::create([
            'name' => "Empresa Nova {$unique}",
            'status' => 'suspended',
            'plan' => 'starter',
            'max_users' => 20,
        ]);

        $paymentId = "pay_app_{$unique}";

        Http::fake([
            "https://api.mercadopago.com/v1/payments/{$paymentId}" => Http::response([
                'id' => $paymentId,
                'status' => 'approved',
                'transaction_amount' => 1290.00,
                'payment_method_id' => 'pix',
                'external_reference' => "company_{$company->id}_plan_pro",
            ], 200),
        ]);

        $headers = $this->generateSignatureHeaders($paymentId);

        $response = $this->withHeaders($headers)->postJson('/api/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => [
                'id' => $paymentId,
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        $company->refresh();
        $this->assertEquals('active', $company->status);
        $this->assertEquals('pro', $company->plan);
        $this->assertEquals(100, $company->max_users);

        $this->assertDatabaseHas('subscriptions', [
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'company_id' => $company->id,
            'mp_payment_id' => $paymentId,
            'status' => 'paid',
            'amount' => 1290.00,
        ]);
    }

    public function test_webhook_payment_rejected_starts_5_day_grace_period(): void
    {
        $unique = uniqid();
        $company = Company::create([
            'name' => "Empresa Ativa {$unique}",
            'status' => 'active',
            'plan' => 'starter',
            'max_users' => 20,
        ]);

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan' => 'starter',
            'status' => 'active',
            'amount' => 490.00,
            'current_period_start' => now()->subDays(25),
            'current_period_end' => now()->addDays(5),
        ]);

        $paymentId = "pay_rej_{$unique}";

        Http::fake([
            "https://api.mercadopago.com/v1/payments/{$paymentId}" => Http::response([
                'id' => $paymentId,
                'status' => 'rejected',
                'transaction_amount' => 490.00,
                'payment_method_id' => 'credit_card',
                'external_reference' => "company_{$company->id}_plan_starter",
            ], 200),
        ]);

        $headers = $this->generateSignatureHeaders($paymentId);

        $response = $this->withHeaders($headers)->postJson('/api/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => [
                'id' => $paymentId,
            ],
        ]);

        $response->assertOk();

        $company->refresh();
        $this->assertEquals('overdue', $company->status);
        $this->assertTrue($company->isOverdue());

        $subscription->refresh();
        $this->assertEquals('overdue', $subscription->status);
        $this->assertNotNull($subscription->grace_period_ends_at);
        $this->assertTrue($subscription->grace_period_ends_at->isFuture());
    }

    public function test_middleware_blocks_students_when_company_is_suspended_and_allows_company_admin_to_billing(): void
    {
        $unique = uniqid();
        $company = Company::create([
            'name' => "Empresa Bloqueada {$unique}",
            'status' => 'suspended',
            'plan' => 'starter',
            'max_users' => 20,
        ]);

        $instructor = User::create([
            'name' => 'Prof Instrutor',
            'email' => "prof_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Teste Bloqueio',
            'slug' => "curso-teste-bloqueio-{$unique}",
            'status' => 'published',
        ]);

        $student = User::create([
            'name' => 'Aluno Bloqueado',
            'email' => "aluno_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $companyAdmin = User::create([
            'name' => 'Gestor Suspenso',
            'email' => "gestor_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        // 1. Aluno tentando acessar aulas tem redirecionamento para o dashboard do aluno com erro
        $studentResponse = $this->actingAs($student)->get("/aluno/cursos/{$course->slug}/aulas");
        $studentResponse->assertRedirect(route('student.dashboard'));
        $studentResponse->assertSessionHas('error');

        // 2. Instrutor / Gestor tentando acessar rotas internas operacionais é redirecionado para a tela de faturamento
        $adminResponse = $this->actingAs($companyAdmin)->get('/instrutor/colaboradores');
        $adminResponse->assertRedirect(route('company.billing'));
        $adminResponse->assertSessionHas('warning');

        // Testar que acesso a /empresa/colaboradores quando suspenso redireciona para faturamento
        $this->actingAs($companyAdmin)->get('/empresa/colaboradores')->assertRedirect(route('company.billing'));

        // 3. Instrutor / Gestor pode acessar a rota de faturamento para regularizar a assinatura
        $billingResponse = $this->actingAs($companyAdmin)->get('/instrutor/faturamento');
        $billingResponse->assertOk();
    }

    public function test_company_billing_livewire_component_renders_and_handles_cancellation(): void
    {
        $unique = uniqid();
        $company = Company::create([
            'name' => "Empresa Livewire {$unique}",
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 100,
        ]);

        $companyAdmin = User::create([
            'name' => 'Gestora Mariana',
            'email' => "mariana_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'mp_payment_id' => "mp_inv_{$unique}",
            'amount' => 1290.00,
            'payment_method' => 'pix',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Livewire::actingAs($companyAdmin)
            ->test(CompanyBilling::class)
            ->assertSee('Plano Pro')
            ->assertSee('Ativo')
            ->assertSee('R$ 1.290,00')
            ->call('openCancelModal')
            ->assertSet('showCancelModal', true)
            ->call('cancelSubscription')
            ->assertSet('showCancelModal', false);

        $company->refresh();
        $this->assertEquals('suspended', $company->status);

        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
    }

    public function test_admin_billing_livewire_component_calculates_mrr_and_restricts_access(): void
    {
        $unique = uniqid();

        $adminAlvora = User::create([
            'name' => 'Super Admin',
            'email' => "super_{$unique}@alvora.com",
            'password' => bcrypt('password'),
            'company_id' => null,
            'role' => UserRole::ADMIN_ALVORA,
        ]);

        $company = Company::create([
            'name' => "Empresa Cliente {$unique}",
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 100,
        ]);

        $companyAdmin = User::create([
            'name' => 'Gestor RH',
            'email' => "gestor_{$unique}@cliente.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
            'mp_preapproval_id' => "preapp_{$unique}",
        ]);

        // 1. Gestor RH não tem acesso ao painel do Super Admin (403 Forbidden)
        $unauthResponse = $this->actingAs($companyAdmin)->get('/admin/faturamento');
        $unauthResponse->assertForbidden();

        // 2. Super Admin acessa com sucesso e visualiza MRR e métricas consolidadas
        Http::fake([
            "https://api.mercadopago.com/preapproval/*" => Http::response([
                'status' => 'authorized',
            ], 200),
        ]);

        Livewire::actingAs($adminAlvora)
            ->test(AdminBilling::class)
            ->assertSee('Gestão Global de Faturamento B2B')
            ->assertSee('R$ 1.290,00')
            ->assertSee($company->name)
            ->call('syncSubscriptions')
            ->assertSee('Sincronização concluída com sucesso');
    }
}
