<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\SituationRoom;
use App\Models\AiIntervention;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\RiskScore;
use App\Models\Subscription;
use App\Models\User;
use App\Services\EvasionInterventionService;
use App\Services\EvasionRiskCalculatorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class SituationRoomAndInterventionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_high_risk_score_automatically_triggers_ai_intervention(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Intervencao {$unique}", 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Em Risco',
            'email' => "risco_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $riskScore = RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'score' => 85, // Alto risco
            'level' => 'high',
            'breakdown' => ['inactivity' => ['score' => 90]],
            'calculated_at' => now(),
        ]);

        $interventionService = new EvasionInterventionService();
        $intervention = $interventionService->triggerForRiskScore($riskScore);

        $this->assertNotNull($intervention);
        $this->assertEquals($student->id, $intervention->user_id);
        $this->assertEquals($company->id, $intervention->company_id);
        $this->assertEquals($riskScore->id, $intervention->risk_score_id);
        $this->assertEquals('sent', $intervention->status);
        $this->assertStringContainsString('Aluno', $intervention->message);

        $this->assertDatabaseHas('ai_interventions', [
            'user_id' => $student->id,
            'company_id' => $company->id,
            'status' => 'sent',
        ]);
    }

    public function test_interventions_are_throttled_within_48_hours(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Throttle {$unique}", 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Throttled',
            'email' => "throttled_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $riskScore = RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'score' => 90,
            'level' => 'high',
            'breakdown' => [],
            'calculated_at' => now(),
        ]);

        $service = new EvasionInterventionService();

        // Primeira intervenção dispara
        $first = $service->triggerForRiskScore($riskScore);
        $this->assertNotNull($first);

        // Segunda intervenção imediata deve ser evitada (throttled)
        $second = $service->triggerForRiskScore($riskScore);
        $this->assertNull($second);
    }

    public function test_only_company_admin_can_access_situation_room(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Auth {$unique}", 'status' => 'active']);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $admin = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $student = User::create([
            'name' => 'Aluno Comum',
            'email' => "student_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        // Aluno não pode acessar
        $this->actingAs($student)->get('/instrutor/sala-de-situacao')->assertForbidden();

        // Acesso legado a /empresa/sala-de-situacao redireciona para /instrutor/sala-de-situacao
        $this->actingAs($admin)->get('/empresa/sala-de-situacao')->assertRedirect('/instrutor/sala-de-situacao');

        // Instrutor / Gestor pode acessar com sucesso
        $this->actingAs($admin)->get('/instrutor/sala-de-situacao')
            ->assertOk()
            ->assertSee('Sala de Situação')
            ->assertSee('Heatmap de Engajamento');
    }

    public function test_situation_room_renders_kpis_and_permits_manual_intervention(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Livewire {$unique}", 'status' => 'active']);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $admin = User::create([
            'name' => 'Gestor RH Livewire',
            'email' => "rh_livewire_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $student = User::create([
            'name' => 'Aluno Alvo',
            'email' => "alvo_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'score' => 88,
            'level' => 'high',
            'breakdown' => ['inactivity' => ['score' => 90]],
            'calculated_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(SituationRoom::class)
            ->assertSee('Aluno Alvo')
            ->assertSee('88/100')
            ->call('openInterventionModal', $student->id)
            ->set('manualMessage', 'Mensagem personalizada da coordenação de RH para apoio.')
            ->call('sendManualIntervention')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ai_interventions', [
            'user_id' => $student->id,
            'company_id' => $company->id,
            'message' => 'Mensagem personalizada da coordenação de RH para apoio.',
        ]);
    }

    public function test_situation_room_respects_tenant_isolation(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Tenant Alfa {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Tenant Beta {$unique}", 'status' => 'active']);

        $adminA = User::create([
            'name' => 'RH Alfa',
            'email' => "rh_a_{$unique}@alfa.com",
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $studentB = User::create([
            'name' => 'Aluno Exclusivo Beta',
            'email' => "beta_{$unique}@beta.com",
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($adminA);

        Livewire::test(SituationRoom::class)
            ->assertDontSee('Aluno Exclusivo Beta');
    }
}
