<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Instructor\CourseEditor;
use App\Livewire\Instructor\CourseList;
use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_instructor_and_company_admin_can_access_course_catalog(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $this->actingAs($instructor);

        $response = $this->get(route('instructor.courses.index'));
        $response->assertStatus(200);
        $response->assertSee('Catálogo de Treinamentos');
        $response->assertSee('Criar Novo Curso');
    }

    public function test_student_cannot_access_instructor_courses_catalog(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Lucas',
            'email' => 'lucas@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('instructor.courses.index'));
        $response->assertStatus(403);
    }

    public function test_instructor_can_create_course_with_auto_slug(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $this->actingAs($instructor);

        Livewire::test(CourseEditor::class)
            ->set('title', 'Boas Práticas de Engenharia de Software')
            ->set('slug', 'boas-praticas-de-engenharia-de-software')
            ->set('description', 'Diretrizes arquiteturais para projetos corporativos.')
            ->set('status', 'draft')
            ->set('instructor_id', $instructor->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('courses', [
            'company_id' => $company->id,
            'title' => 'Boas Práticas de Engenharia de Software',
            'slug' => 'boas-praticas-de-engenharia-de-software',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);
    }

    public function test_instructor_can_toggle_course_status(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Treinamento de Onboarding',
            'slug' => 'treinamento-de-onboarding',
            'status' => 'draft',
        ]);

        $this->actingAs($instructor);

        Livewire::test(CourseList::class)
            ->call('togglePublish', $course->id)
            ->assertDispatched('toast');

        $course->refresh();
        $this->assertEquals('published', $course->status);
        $this->assertTrue($course->isPublished());
    }

    public function test_courses_are_strictly_isolated_by_tenant(): void
    {
        $companyA = Company::create(['name' => 'Empresa A', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Empresa B', 'status' => 'active']);

        $instructorA = User::create([
            'name' => 'Instrutor A',
            'email' => 'a@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $instructorB = User::create([
            'name' => 'Instrutor B',
            'email' => 'b@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $courseA = Course::create([
            'company_id' => $companyA->id,
            'instructor_id' => $instructorA->id,
            'title' => 'Curso Confidencial Empresa A',
            'slug' => 'curso-confidencial',
            'status' => 'published',
        ]);

        $courseB = Course::create([
            'company_id' => $companyB->id,
            'instructor_id' => $instructorB->id,
            'title' => 'Curso Estratégico Empresa B',
            'slug' => 'curso-estrategico',
            'status' => 'published',
        ]);

        // When acting as Instructor A
        $this->actingAs($instructorA);

        $visibleCourses = Course::all();
        $this->assertCount(1, $visibleCourses);
        $this->assertEquals($courseA->id, $visibleCourses->first()->id);
        $this->assertFalse($visibleCourses->contains('id', $courseB->id));
    }

    public function test_instructor_can_switch_tabs_and_update_media_and_publication(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Microsserviços',
            'slug' => 'curso-de-microsservicos',
            'status' => 'draft',
        ]);

        $this->actingAs($instructor);

        Livewire::test(CourseEditor::class, ['course' => $course])
            ->assertSet('activeTab', 'basic')
            ->call('setTab', 'publish')
            ->assertSet('activeTab', 'publish')
            ->set('thumbnail', 'https://exemplo.com/capa.jpg')
            ->set('status', 'published')
            ->call('save')
            ->assertDispatched('toast');

        $course->refresh();
        $this->assertEquals('https://exemplo.com/capa.jpg', $course->thumbnail);
        $this->assertEquals('published', $course->status);
    }

    public function test_instructor_creating_course_advances_to_curriculum(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Carlos',
            'email' => 'carlos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $this->actingAs($instructor);

        Livewire::test(CourseEditor::class)
            ->set('title', 'Inteligência Artificial Aplicada')
            ->set('description', 'Aprenda IA do zero ao avançado.')
            ->set('instructor_id', $instructor->id)
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'company_id' => $company->id,
            'title' => 'Inteligência Artificial Aplicada',
            'slug' => 'inteligencia-artificial-aplicada',
            'status' => 'draft',
        ]);
    }
}
