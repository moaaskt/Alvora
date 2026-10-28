<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AiIntervention;
use App\Models\Company;
use App\Models\Course;
use App\Models\RiskScore;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_access_dashboard_without_errors(): void
    {
        $unique = uniqid();

        // 1. Criar empresa com plano e cursos
        $company = Company::create([
            'name' => "Tenant Alpha {$unique}",
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $instructor = User::create([
            'name' => "Instrutor {$unique}",
            'email' => "inst_{$unique}@alpha.com",
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => "Aluno {$unique}",
            'email' => "aluno_{$unique}@alpha.com",
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'score' => 75,
            'level' => 'high',
            'breakdown' => [
                'inactivity' => 30,
                'stagnation' => 25,
                'quizzes' => 10,
                'peer_deviation' => 10,
            ],
            'calculated_at' => now(),
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Teste {$unique}",
            'slug' => "curso-teste-{$unique}",
            'status' => 'published',
        ]);

        // 2. Criar Super Admin Alvora (company_id = null)
        $superAdmin = User::create([
            'name' => "Super Admin {$unique}",
            'email' => "admin_{$unique}@alvora.com.br",
            'password' => bcrypt('password123'),
            'company_id' => null,
            'role' => UserRole::ADMIN_ALVORA,
        ]);

        // 3. Acessar o Dashboard como Super Admin
        $response = $this->actingAs($superAdmin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Painel de Gestão Global');
        $response->assertSee('Super Admin Alvora');
        $response->assertSee('Global (Todos os Clientes)');
        $response->assertSee('Ambiente');
        $response->assertSee('Super Admin');
        $response->assertSee($course->title);
    }

    public function test_company_admin_accesses_dashboard_with_tenant_quota_and_name(): void
    {
        $unique = uniqid();

        $company = Company::create([
            'name' => "Tenant Beta {$unique}",
            'status' => 'active',
            'plan' => 'enterprise',
            'max_users' => 100,
        ]);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'amount' => 2490.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $companyAdmin = User::create([
            'name' => "Gestor RH {$unique}",
            'email' => "rh_{$unique}@beta.com",
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $student = User::create([
            'name' => "Aluno Beta {$unique}",
            'email' => "aluno_beta_{$unique}@beta.com",
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $response = $this->actingAs($companyAdmin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Painel do Instrutor');
        $response->assertSee($company->name);
        $response->assertSee('100 vagas contratadas');
    }
}

