<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MercadoPagoCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_subscription_and_invoice_models_persist_with_tenant(): void
    {
        $unique = uniqid();
        $company = Company::create([
            'name' => "Empresa Teste {$unique}",
            'plan' => 'pro',
            'status' => 'active',
            'max_users' => 100,
        ]);

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'mp_payment_id' => "mp_pay_{$unique}",
            'amount' => 1290.00,
            'payment_method' => 'credit_card',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'company_id' => $company->id,
            'status' => 'active',
            'amount' => 1290.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => $company->id,
            'mp_payment_id' => "mp_pay_{$unique}",
            'status' => 'paid',
        ]);

        $this->assertTrue($company->hasActiveSubscription());
        $this->assertEquals($subscription->id, $company->activeSubscription()->id);
        $this->assertTrue($invoice->isPaid());
    }

    public function test_mercadopago_service_creates_checkout_preference(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Startup {$unique}", 'plan' => 'starter']);
        $admin = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$unique}@startup.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => "pref_{$unique}",
                'init_point' => "https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=pref_{$unique}",
                'sandbox_init_point' => "https://sandbox.mercadopago.com.br/checkout/v1/redirect?pref_id=pref_{$unique}",
            ], 201),
        ]);

        $service = new MercadoPagoService();
        $preference = $service->createCheckoutPreference($company, 'pro', $admin);

        $this->assertEquals("pref_{$unique}", $preference['id']);
        $this->assertStringContainsString("pref_{$unique}", $preference['init_point']);

        Http::assertSent(function ($request) use ($company) {
            $data = $request->data();
            return $request->url() === 'https://api.mercadopago.com/checkout/preferences'
                && $data['items'][0]['unit_price'] === 1290.0
                && $data['external_reference'] === "company_{$company->id}_plan_pro"
                && isset($data['back_urls']['success']);
        });
    }

    public function test_mercadopago_service_creates_preapproval(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Tech Corp {$unique}", 'plan' => 'starter']);
        $admin = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$unique}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        Http::fake([
            'https://api.mercadopago.com/preapproval' => Http::response([
                'id' => "preapproval_{$unique}",
                'status' => 'authorized',
            ], 201),
        ]);

        $service = new MercadoPagoService();
        $result = $service->createPreapproval($company, 'starter', $admin, 'card_token_123');

        $this->assertEquals("preapproval_{$unique}", $result['id']);
        $this->assertEquals('authorized', $result['status']);
    }

    public function test_billing_return_screens_render_successfully(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Enterprise {$unique}"]);
        $admin = User::create([
            'name' => 'Diretor RH',
            'email' => "diretor_{$unique}@enterprise.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $responseSuccess = $this->actingAs($admin)->get('/empresa/faturamento/sucesso?payment_id=987654');
        $responseSuccess->assertOk();
        $responseSuccess->assertSee('Assinatura Corporativa Ativada com Sucesso!');
        $responseSuccess->assertSee('987654');

        $responsePending = $this->actingAs($admin)->get('/empresa/faturamento/pendente');
        $responsePending->assertOk();
        $responsePending->assertSee('Pagamento em Análise');

        $responseFailure = $this->actingAs($admin)->get('/empresa/faturamento/erro');
        $responseFailure->assertOk();
        $responseFailure->assertSee('Não Foi Possível Concluir o Pagamento');
    }

    public function test_unauthenticated_user_cannot_access_billing_return(): void
    {
        $response = $this->get('/empresa/faturamento/sucesso');
        $response->assertRedirect('/login');
    }
}
