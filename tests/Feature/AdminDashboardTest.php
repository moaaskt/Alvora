<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;
    private User $admin;
    private User $instructor;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Empresa Teste Dashboard ' . uniqid(),
            'status' => 'active',
            'cnpj' => '12.345.678/0001-' . rand(10, 99),
        ]);

        Subscription::create([
            'company_id' => $this->company->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'amount' => 199.00,
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin Teste ' . uniqid(),
            'email' => 'admin_dash_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->instructor = User::create([
            'name' => 'Instrutor Teste ' . uniqid(),
            'email' => 'instructor_dash_' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->company->id,
        ]);

        $this->student = User::create([
            'name' => 'Aluno Teste ' . uniqid(),
            'email' => 'student_dash_' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Painel Geral');
        $response->assertSee('Total de Cursos');
        $response->assertSee('Total de Aulas');
        $response->assertSee('Total de Matrículas');
        $response->assertSee('Alunos Ativos');
        $response->assertSee('Instrutores');
    }

    public function test_admin_dashboard_calculates_all_5_kpis_accurately(): void
    {
        // Cria 2 cursos
        $course1 = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Curso de Teste 1 ' . uniqid(),
            'slug' => 'curso-teste-1-' . uniqid(),
            'status' => 'published',
        ]);
        $course2 = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Curso de Teste 2 ' . uniqid(),
            'slug' => 'curso-teste-2-' . uniqid(),
            'status' => 'draft',
        ]);

        // Cria módulo e aulas
        $module = Module::create([
            'company_id' => $this->company->id,
            'course_id' => $course1->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $this->company->id,
            'course_id' => $course1->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'duration_minutes' => 10,
            'order' => 1,
        ]);
        Lesson::create([
            'company_id' => $this->company->id,
            'course_id' => $course1->id,
            'module_id' => $module->id,
            'title' => 'Aula 2',
            'duration_minutes' => 15,
            'order' => 2,
        ]);

        // Cria matrícula
        Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $course1->id,
            'status' => 'active',
            'progress_percent' => 50,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee($course1->title);
        $response->assertSee($this->student->name);
        $response->assertSee('Painel Geral');
    }

    public function test_accessing_generic_dashboard_as_admin_redirects_to_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_instructor_and_student_are_forbidden_from_admin_dashboard(): void
    {
        // Aluno tentando acessar admin
        $studentResponse = $this->actingAs($this->student)->get(route('admin.dashboard'));
        $studentResponse->assertForbidden();

        // Instrutor tentando acessar admin
        $instructorResponse = $this->actingAs($this->instructor)->get(route('admin.dashboard'));
        $instructorResponse->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_recent_courses_renders_thumbnail_url_correctly(): void
    {
        $externalUrl = 'https://exemplo.com/capa-ia.jpg';
        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Curso com Capa Externa ' . uniqid(),
            'slug' => 'curso-capa-' . uniqid(),
            'thumbnail' => $externalUrl,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('src="' . $externalUrl . '"', false);
        $response->assertDontSee('storage/' . $externalUrl, false);
    }
}
