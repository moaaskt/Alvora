<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Instructor\CourseEditor;
use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CourseEditorFixTest extends TestCase
{
    use DatabaseTransactions;

    public function test_instructor_can_create_new_course_and_redirects_to_curriculum(): void
    {
        $company = Company::create(['name' => 'Empresa Instrutor', 'status' => 'active']);
        $instructor = User::create([
            'name' => 'Prof. Instrutor Teste',
            'email' => 'prof_fix_' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $company->id,
        ]);

        $title = 'Treinamento de Arquitetura de Software ' . uniqid();

        Livewire::actingAs($instructor)
            ->test(CourseEditor::class)
            ->set('title', $title)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $course = Course::where('title', $title)->first();
        $this->assertNotNull($course);
        $this->assertEquals($company->id, $course->company_id);
        $this->assertEquals($instructor->id, $course->instructor_id);
    }

    public function test_superadmin_without_company_can_create_new_course_with_company_fallback(): void
    {
        $company = Company::first() ?? Company::create(['name' => 'Empresa Default', 'status' => 'active']);
        $admin = User::create([
            'name' => 'Super Admin Teste',
            'email' => 'admin_fix_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $title = 'Treinamento Global Alvora ' . uniqid();

        Livewire::actingAs($admin)
            ->test(CourseEditor::class)
            ->set('title', $title)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $course = Course::where('title', $title)->first();
        $this->assertNotNull($course);
        $this->assertEquals($company->id, $course->company_id);
    }

    public function test_instructor_course_editor_screen_renders_stitch_layout_faithfully(): void
    {
        $company = Company::create(['name' => 'Empresa Teste Layout', 'status' => 'active']);
        $instructor = User::create([
            'name' => 'Prof. Carlos Eduardo',
            'email' => 'carlos_layout_' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $company->id,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Inteligência Artificial Corporativa',
            'slug' => 'inteligencia-artificial-corporativa-' . uniqid(),
            'description' => 'Curso completo de IA para negócios.',
            'status' => 'published',
        ]);

        $response = $this->actingAs($instructor)
            ->get(route('instructor.courses.edit', $course));

        $response->assertStatus(200);
        $response->assertSee('Atualizar: Inteligência Artificial Corporativa');
        $response->assertSee('Dados Gerais do Treinamento');
        $response->assertSee('Objetivos de Aprendizagem');
        $response->assertSee('Competências');
        $response->assertSee('Resumo Rápido');
        $response->assertSee('Saúde do Curso');
        $response->assertSee('Parâmetros de Acesso');
        $response->assertSee('Publicação');
        $response->assertSee('Alvora Copilot Insights');
        $response->assertSee('Matrículas Ativas');
        $response->assertSee('Total de Aulas');
        $response->assertSee('lg:col-span-8');
        $response->assertSee('lg:col-span-4');
    }
}

