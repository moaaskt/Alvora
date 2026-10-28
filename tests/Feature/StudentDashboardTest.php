<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Student\Catalog as StudentCatalog;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_access_student_dashboard(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno João',
            'email' => 'joao@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Meu Aprendizado');
        $response->assertSee('Olá, Aluno João');
        $response->assertSee('Continuar Aprendendo');
        $response->assertSee('Explore o catálogo da sua empresa');
    }

    public function test_student_dashboard_redirects_from_generic_dashboard(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno João',
            'email' => 'joao@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_student_can_enroll_in_published_course(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Ana',
            'email' => 'ana@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno João',
            'email' => 'joao@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Segurança da Informação B2B',
            'slug' => 'seguranca-da-informacao-b2b',
            'status' => 'published',
        ]);

        $this->actingAs($student);

        Livewire::test(StudentCatalog::class)
            ->call('enroll', $course->id)
            ->assertSee('Matrícula realizada com sucesso no curso Segurança da Informação B2B!');

        $this->assertDatabaseHas('enrollments', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);
    }

    public function test_student_cannot_enroll_in_course_from_another_tenant(): void
    {
        $companyA = Company::create(['name' => 'Tech Corp', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Outra Corp', 'status' => 'active']);

        $instructorB = User::create([
            'name' => 'Instrutor Beta',
            'email' => 'beta@outra.com',
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $studentA = User::create([
            'name' => 'Aluno Alpha',
            'email' => 'alpha@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $courseB = Course::create([
            'company_id' => $companyB->id,
            'instructor_id' => $instructorB->id,
            'title' => 'Curso Confidencial Empresa B',
            'slug' => 'curso-confidencial-empresa-b',
            'status' => 'published',
        ]);

        $this->actingAs($studentA);

        try {
            Livewire::test(StudentCatalog::class)
                ->call('enroll', $courseB->id);
        } catch (\Throwable $e) {
            // Se o Livewire não capturar, a exceção é pega aqui
        }

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $studentA->id,
            'course_id' => $courseB->id,
        ]);
    }

    public function test_student_dashboard_displays_accurate_progress(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno João',
            'email' => 'joao@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Introdução à IA Corporativa',
            'slug' => 'introducao-a-ia-corporativa',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Fundamentos',
            'order' => 1,
        ]);

        $lesson1 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: O que é IA',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 2: Redes Neurais',
            'order' => 2,
        ]);

        $enrollment = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        // Completa aula 1
        LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
            'completed_at' => now(),
        ]);

        // Recalcula progresso (1 de 2 = 50%)
        $percent = $enrollment->recalculateProgress();
        $this->assertEquals(50, $percent);

        $this->actingAs($student);

        Livewire::test(StudentDashboard::class)
            ->assertSee('Progresso Geral')
            ->assertSee('50%')
            ->assertSee('Introdução à IA Corporativa')
            ->assertSee('Continuar Aula');
    }

    public function test_student_dashboard_empty_states_and_catalog_search(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno João',
            'email' => 'joao@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($student);

        // Dashboard sem cursos em andamento exibe mensagem de estado vazio
        Livewire::test(StudentDashboard::class)
            ->assertSee('Nenhum curso em andamento no momento.')
            ->assertSee('Explorar o catálogo');

        // Cria um curso publicado para testar busca no catálogo
        Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Arquitetura Cloud Corporativa',
            'slug' => 'arquitetura-cloud-corporativa',
            'category' => 'Infraestrutura',
            'status' => 'published',
        ]);

        Livewire::test(StudentCatalog::class)
            ->assertSee('Arquitetura Cloud Corporativa')
            ->set('search', 'InexistenteXYZ')
            ->assertDontSee('Arquitetura Cloud Corporativa')
            ->assertSee('Nenhum curso encontrado para a sua busca.');
    }
}
