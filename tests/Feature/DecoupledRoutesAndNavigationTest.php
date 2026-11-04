<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecoupledRoutesAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Empresa Teste Desacoplamento',
            'cnpj' => '12.345.678/0001-99',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        $this->subscription = Subscription::create([
            'company_id' => $this->company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    public function test_company_manager_can_access_empresa_routes_and_is_forbidden_from_instructor_routes(): void
    {
        $manager = User::create([
            'name' => 'Gestor RH',
            'email' => 'gestor@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $this->company->id,
        ]);

        $manager->companies()->attach($this->company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->actingAs($manager);

        // Acesso autorizado a rotas corporativas /empresa/*
        $this->get('/empresa/colaboradores')->assertOk()->assertSee('Colaboradores & Acessos', false);
        $this->get('/empresa/trilhas')->assertOk();
        $this->get('/empresa/faturamento')->assertOk();
        $this->get('/empresa/sala-de-situacao')->assertOk();
        $this->get('/empresa/insights-executivos')->assertOk();

        // Acesso restrito: Gestor corporativo puro não pode acessar criação didática sem capacidade de instrutor
        $this->get('/instrutor/cursos')->assertForbidden();
    }

    public function test_instructor_can_access_instructor_routes_and_is_forbidden_from_empresa_routes(): void
    {
        $instructor = User::create([
            'name' => 'Prof. Didático',
            'email' => 'professor@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->company->id,
        ]);

        $instructor->companies()->attach($this->company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->actingAs($instructor);

        // Acesso autorizado a rotas didáticas /instrutor/*
        $this->get('/instrutor/cursos')->assertOk();
        $this->get('/instrutor/cursos/novo')->assertOk();

        // Acesso restrito: Instrutor puro não pode gerenciar colaboradores, finanças ou sala de situação
        $this->get('/empresa/colaboradores')->assertForbidden();
        $this->get('/empresa/trilhas')->assertForbidden();
        $this->get('/empresa/faturamento')->assertForbidden();
        $this->get('/empresa/sala-de-situacao')->assertForbidden();
        $this->get('/empresa/insights-executivos')->assertForbidden();
    }

    public function test_contextual_roles_strictly_separate_corporate_management_and_teaching(): void
    {
        // 1. Usuário com papel contextual company_admin pode gerenciar, mas NÃO pode lecionar
        $managerUser = User::create([
            'name' => 'Gestor Corporativo',
            'email' => 'gestor-estrito@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
        $managerUser->companies()->attach($this->company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->actingAs($managerUser);
        $this->get('/empresa/colaboradores')->assertOk();
        $this->get('/instrutor/cursos')->assertForbidden();

        // 2. Usuário com papel contextual instructor pode lecionar, mas NÃO pode gerenciar
        $instructorUser = User::create([
            'name' => 'Instrutor Corporativo',
            'email' => 'instrutor-estrito@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
        $instructorUser->companies()->attach($this->company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->actingAs($instructorUser);
        $this->get('/instrutor/cursos')->assertOk();
        $this->get('/empresa/colaboradores')->assertForbidden();
    }

    public function test_student_is_forbidden_from_both_empresa_and_instructor_routes(): void
    {
        $student = User::create([
            'name' => 'Aluno Colaborador',
            'email' => 'aluno@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($student);

        $this->get('/empresa/colaboradores')->assertForbidden();
        $this->get('/empresa/trilhas')->assertForbidden();
        $this->get('/empresa/faturamento')->assertForbidden();
        $this->get('/empresa/sala-de-situacao')->assertForbidden();
        $this->get('/instrutor/cursos')->assertForbidden();
    }

    public function test_super_admin_has_bypass_to_both_portals(): void
    {
        $admin = User::create([
            'name' => 'Super Administrador',
            'email' => 'admin@alvora.test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->actingAs($admin);

        // Super Admin tem bypass nas rotas de empresa
        $this->get('/empresa/colaboradores')->assertOk();

        // No portal de cursos, CourseEditor e CourseList redirecionam o super admin para a governança global
        $this->get('/instrutor/cursos/novo')->assertRedirect(route('admin.courses.create'));
        $this->get('/instrutor/cursos')->assertRedirect(route('admin.courses.index'));
    }

    public function test_backward_compatibility_redirects_from_instrutor_management_to_empresa(): void
    {
        $manager = User::create([
            'name' => 'Gestor RH',
            'email' => 'gestor-redirect@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $this->company->id,
        ]);

        $manager->companies()->attach($this->company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->actingAs($manager);

        $this->get('/instrutor/colaboradores')->assertRedirect('/empresa/colaboradores');
        $this->get('/instrutor/trilhas')->assertRedirect('/empresa/trilhas');
        $this->get('/instrutor/faturamento')->assertRedirect('/empresa/faturamento');
        $this->get('/instrutor/faturamento/sucesso')->assertRedirect('/empresa/faturamento/sucesso');
        $this->get('/instrutor/faturamento/pendente')->assertRedirect('/empresa/faturamento/pendente');
        $this->get('/instrutor/faturamento/erro')->assertRedirect('/empresa/faturamento/erro');
        $this->get('/instrutor/sala-de-situacao')->assertRedirect('/empresa/sala-de-situacao');
        $this->get('/instrutor/insights-executivos')->assertRedirect('/empresa/insights-executivos');
        $this->get('/empresa/certificados/templates')->assertRedirect('/instrutor/certificados/templates');
    }

    public function test_navigation_bar_renders_appropriate_links_and_badges_per_context(): void
    {
        // 1. Gestor Corporativo Puro
        $manager = User::create([
            'name' => 'Gestor RH Puro',
            'email' => 'gestor-puro@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
        $manager->companies()->attach($this->company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->actingAs($manager);
        $navManager = $this->view('layouts.navigation');
        $navManager->assertSee('Gestor Corporativo');
        $navManager->assertSee(route('employees.index'));
        $navManager->assertSee(route('company.tracks.index'));
        $navManager->assertSee(route('company.situation-room'));
        $navManager->assertSee(route('company.billing'));
        $navManager->assertDontSee(route('instructor.courses.index'));

        // 2. Instrutor Didático Puro
        $instructor = User::create([
            'name' => 'Instrutor Didático Puro',
            'email' => 'instrutor-puro@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
        $instructor->companies()->attach($this->company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->actingAs($instructor);
        $navInstructor = $this->view('layouts.navigation');
        $navInstructor->assertSee('Instrutor');
        $navInstructor->assertSee(route('instructor.courses.index'));
        $navInstructor->assertDontSee(route('employees.index'));
        $navInstructor->assertDontSee(route('company.situation-room'));

        // 3. Super Admin possui bypass e vê ferramentas administrativas globais
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin-nav@alvora.test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->actingAs($admin);
        $navAdmin = $this->view('layouts.navigation');
        $navAdmin->assertSee('Administrador Alvora');
        $navAdmin->assertSee(route('admin.billing'));

        // 4. Colaborador / Aluno Puro
        $student = User::create([
            'name' => 'Aluno Colaborador Puro',
            'email' => 'aluno-puro@empresa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($student);
        $navStudent = $this->view('layouts.navigation');
        $navStudent->assertSee('Aluno');
        $navStudent->assertSee('Meu Aprendizado');
        $navStudent->assertDontSee(route('employees.index'));
        $navStudent->assertDontSee(route('instructor.courses.index'));
    }
}
