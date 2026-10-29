<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\CourseManager;
use App\Livewire\Instructor\CourseEditor;
use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCourseManagerTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $instructor;
    private Company $company1;
    private Company $company2;
    private Course $course1;
    private Course $course2;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company1 = Company::create([
            'name' => 'Empresa Alfa ' . uniqid(),
            'status' => 'active',
            'cnpj' => '11.111.111/0001-' . rand(10, 99),
        ]);

        Subscription::create([
            'company_id' => $this->company1->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'amount' => 299.00,
        ]);

        $this->company2 = Company::create([
            'name' => 'Empresa Beta ' . uniqid(),
            'status' => 'active',
            'cnpj' => '22.222.222/0001-' . rand(10, 99),
        ]);

        Subscription::create([
            'company_id' => $this->company2->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 199.00,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin Gestor',
            'company_id' => $this->company1->id,
            'role' => UserRole::ADMIN_ALVORA,
            'email' => 'admin_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);

        $this->instructor = User::create([
            'name' => 'Instrutor Alfa',
            'company_id' => $this->company1->id,
            'role' => UserRole::INSTRUCTOR,
            'email' => 'instructor_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);

        $this->category = Category::create([
            'name' => 'Tecnologia & Dados ' . uniqid(),
            'slug' => 'tech-dados-' . uniqid(),
            'icon' => 'code-bracket',
            'is_active' => true,
        ]);

        $this->course1 = Course::create([
            'company_id' => $this->company1->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'title' => 'Curso de Laravel 13 Avançado',
            'slug' => 'laravel-13-avancado-' . uniqid(),
            'description' => 'Arquitetura modular e enterprise',
            'status' => 'published',
        ]);

        $this->course2 = Course::create([
            'company_id' => $this->company2->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'title' => 'Gestão Ágil com Scrum',
            'slug' => 'scrum-gestao-' . uniqid(),
            'description' => 'Práticas ágeis para líderes',
            'status' => 'draft',
        ]);
    }

    public function test_super_admin_can_access_admin_courses_index_with_admin_layout(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/cursos');

        $response->assertStatus(200);
        $response->assertSee('Gestão Global de Cursos');
        $response->assertSee('Painel do Administrador');
        $response->assertSee('Super Admin');
        $response->assertSee('Governança');
        $response->assertSee($this->course1->title);
        $response->assertSee($this->course2->title);
    }

    public function test_super_admin_on_instructor_courses_is_redirected_to_admin_courses(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/instrutor/cursos');

        $response->assertRedirect('/admin/cursos');
    }

    public function test_instructor_cannot_access_admin_courses_index(): void
    {
        $response = $this->actingAs($this->instructor)->get('/admin/cursos');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_create_and_edit_under_admin_layout(): void
    {
        $responseCreate = $this->actingAs($this->superAdmin)->get('/admin/cursos/novo');
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Super Admin');

        $responseEdit = $this->actingAs($this->superAdmin)->get("/admin/cursos/{$this->course1->id}/editar");
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Super Admin');
        $responseEdit->assertSee($this->course1->title);
    }

    public function test_admin_sidebar_links_point_to_admin_courses_and_certificates(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee(route('admin.courses.index'));
        $response->assertSee(route('admin.certificates.index'));
        $response->assertDontSee(route('instructor.courses.index'));
    }

    public function test_livewire_course_manager_filters_and_search(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(CourseManager::class)
            ->assertSee($this->course1->title)
            ->assertSee($this->course2->title)
            ->set('search', 'Laravel')
            ->assertSee($this->course1->title)
            ->assertDontSee($this->course2->title)
            ->set('search', '')
            ->set('companyFilter', (string) $this->company2->id)
            ->assertSee($this->course2->title)
            ->assertDontSee($this->course1->title);
    }

    public function test_livewire_course_manager_toggle_status_and_export_csv(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(CourseManager::class)
            ->call('togglePublish', $this->course2->id)
            ->assertDispatched('toast');

        $this->assertEquals('published', $this->course2->fresh()->status);

        $response = Livewire::actingAs($this->superAdmin)
            ->test(CourseManager::class)
            ->call('exportCsv');

        $this->assertNotNull($response);
    }
}
