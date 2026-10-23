<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorPanelAndNavbarUrlsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $instructor;
    protected User $student;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme EdTech Lab',
            'cnpj' => '11.222.333/0001-44',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        Subscription::create([
            'company_id' => $this->company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->admin = User::create([
            'name' => 'Admin Plataforma',
            'email' => 'admin@alvora.test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->instructor = User::create([
            'name' => 'Prof. Instrutor Empresa',
            'email' => 'instrutor@acme.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->company->id,
        ]);

        $this->student = User::create([
            'name' => 'Aluno Colaborador',
            'email' => 'aluno@acme.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_instructor_can_access_colaboradores_at_instrutor_prefix(): void
    {
        $response = $this->actingAs($this->instructor)->get('/instrutor/colaboradores');

        $response->assertOk();
        $response->assertSee('Colaboradores & Acessos', false);
    }

    public function test_legacy_empresa_colaboradores_redirects_to_instrutor_colaboradores(): void
    {
        $response = $this->actingAs($this->instructor)->get('/empresa/colaboradores');

        $response->assertRedirect('/instrutor/colaboradores');
    }

    public function test_navbar_for_instructor_contains_correct_instrutor_urls_and_badge(): void
    {
        $response = $this->actingAs($this->instructor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Painel do Instrutor');
        $response->assertSee('/instrutor/colaboradores');
        $response->assertSee('/instrutor/trilhas');
        $response->assertSee('/instrutor/sala-de-situacao');
        $response->assertSee('/instrutor/faturamento');
        $response->assertSee('/instrutor/cursos');
        $response->assertSee('Instrutor');
    }

    public function test_student_cannot_access_instructor_management_routes(): void
    {
        $this->actingAs($this->student)->get('/instrutor/colaboradores')->assertForbidden();
        $this->actingAs($this->student)->get('/instrutor/trilhas')->assertForbidden();
        $this->actingAs($this->student)->get('/instrutor/sala-de-situacao')->assertForbidden();
        $this->actingAs($this->student)->get('/instrutor/faturamento')->assertForbidden();
    }

    public function test_login_page_renders_sandbox_in_exact_order_admin_instrutor_aluno(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $content = $response->getContent();

        $adminPos = strpos($content, 'admin@alvora.com.br');
        $instrutorPos = strpos($content, 'professor@acme.com.br');
        $alunoPos = strpos($content, 'aluno@acme.com.br');

        $this->assertNotFalse($adminPos, 'Admin login button present');
        $this->assertNotFalse($instrutorPos, 'Instrutor login button present');
        $this->assertNotFalse($alunoPos, 'Aluno login button present');

        $this->assertTrue($adminPos < $instrutorPos, 'Admin button appears before Instrutor button');
        $this->assertTrue($instrutorPos < $alunoPos, 'Instrutor button appears before Aluno button');
    }
}
