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
    protected User $companyAdmin;
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

        // Instrutor corporativo (papel didático ativo em company_users)
        $this->instructor = User::create([
            'name' => 'Prof. Instrutor Empresa',
            'email' => 'instrutor@acme.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->company->id,
        ]);
        $this->instructor->companies()->attach($this->company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        // Gestor corporativo (papel de gestão ativo em company_users)
        $this->companyAdmin = User::create([
            'name' => 'Gestor RH Empresa',
            'email' => 'gestor@acme.test',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $this->company->id,
        ]);
        $this->companyAdmin->companies()->attach($this->company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        // Aluno / Colaborador (papel de estudante ativo em company_users)
        $this->student = User::create([
            'name' => 'Aluno Colaborador',
            'email' => 'aluno@acme.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
        $this->student->companies()->attach($this->company->id, [
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    public function test_company_admin_can_access_colaboradores_at_canonical_empresa_route(): void
    {
        $response = $this->actingAs($this->companyAdmin)->get('/empresa/colaboradores');

        $response->assertOk();
        $response->assertSee('Colaboradores & Acessos', false);
    }

    public function test_legacy_instrutor_colaboradores_redirects_to_canonical_empresa_colaboradores(): void
    {
        // 1. Gestor corporativo acessando alias legado é redirecionado suavemente para o canônico
        $response = $this->actingAs($this->companyAdmin)->get('/instrutor/colaboradores');
        $response->assertRedirect('/empresa/colaboradores');

        $canonicalResponse = $this->actingAs($this->companyAdmin)->get('/empresa/colaboradores');
        $canonicalResponse->assertOk();
    }

    public function test_instructor_without_management_role_cannot_access_company_management(): void
    {
        // Instrutor puro não possui autorização de gestão corporativa
        $this->actingAs($this->instructor)->get('/empresa/colaboradores')->assertForbidden();

        // Alias legado redireciona para o canônico onde é barrado
        $response = $this->actingAs($this->instructor)->get('/instrutor/colaboradores');
        $response->assertRedirect('/empresa/colaboradores');
    }

    public function test_navbar_for_instructor_contains_didactic_urls_and_badge(): void
    {
        $response = $this->actingAs($this->instructor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Painel do Instrutor');
        $response->assertSee('/instrutor/cursos');

        // Garante que instrutor puro não vê links de gestão corporativa na navegação do dashboard
        $response->assertDontSee('/empresa/colaboradores');
        $response->assertDontSee('/empresa/faturamento');

        // Valida contrato da navbar layouts.navigation
        $view = $this->actingAs($this->instructor)->view('layouts.navigation');
        $view->assertSee('Instrutor');
        $view->assertSee('/instrutor/cursos');
        $view->assertDontSee('/empresa/colaboradores');
    }

    public function test_navbar_for_company_admin_contains_management_urls_and_badge(): void
    {
        $response = $this->actingAs($this->companyAdmin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('/empresa/colaboradores');
        $response->assertSee('/empresa/trilhas');
        $response->assertSee('/empresa/sala-de-situacao');
        $response->assertSee('/empresa/faturamento');

        // Valida contrato da navbar layouts.navigation
        $view = $this->actingAs($this->companyAdmin)->view('layouts.navigation');
        $view->assertSee('Gestor Corporativo');
        $view->assertSee('Colaboradores & Convites', false);
        $view->assertSee('/empresa/colaboradores');
        $view->assertSee('/empresa/trilhas');
        $view->assertSee('/empresa/sala-de-situacao');
        $view->assertSee('/empresa/faturamento');
    }

    public function test_student_cannot_access_management_or_didactic_routes(): void
    {
        // Rotas corporativas canônicas são proibidas para alunos
        $this->actingAs($this->student)->get('/empresa/colaboradores')->assertForbidden();
        $this->actingAs($this->student)->get('/empresa/trilhas')->assertForbidden();
        $this->actingAs($this->student)->get('/empresa/sala-de-situacao')->assertForbidden();
        $this->actingAs($this->student)->get('/empresa/faturamento')->assertForbidden();

        // Aliases legados redirecionam para as canônicas
        $this->actingAs($this->student)->get('/instrutor/colaboradores')->assertRedirect('/empresa/colaboradores');
        $this->actingAs($this->student)->get('/instrutor/trilhas')->assertRedirect('/empresa/trilhas');
        $this->actingAs($this->student)->get('/instrutor/sala-de-situacao')->assertRedirect('/empresa/sala-de-situacao');
        $this->actingAs($this->student)->get('/instrutor/faturamento')->assertRedirect('/empresa/faturamento');

        // Rota didática é proibida para alunos
        $this->actingAs($this->student)->get('/instrutor/cursos')->assertForbidden();
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
