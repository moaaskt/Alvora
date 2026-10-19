<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Instructor\CurriculumBuilder;
use App\Models\Company;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CurriculumTest extends TestCase
{
    use DatabaseTransactions;

    public function test_instructor_can_access_curriculum_builder(): void
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
            'title' => 'Curso de Arquitetura de Software',
            'slug' => 'curso-de-arquitetura-de-software',
            'status' => 'draft',
        ]);

        $this->actingAs($instructor);

        $response = $this->get(route('instructor.courses.curriculum', $course));
        $response->assertStatus(200);
        $response->assertSee('Curso de Arquitetura de Software');
        $response->assertSee('Adicionar Módulo');
    }

    public function test_instructor_can_add_modules_and_lessons(): void
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
            'title' => 'Treinamento de Liderança',
            'slug' => 'treinamento-de-lideranca',
            'status' => 'draft',
        ]);

        $this->actingAs($instructor);

        // Add Module 1
        Livewire::test(CurriculumBuilder::class, ['course' => $course])
            ->set('newModuleTitle', 'Módulo 1: Introdução à Gestão')
            ->set('newModuleDescription', 'Princípios fundamentais')
            ->call('addModule')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('modules', [
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Módulo 1: Introdução à Gestão',
            'order' => 1,
        ]);

        $module = Module::where('course_id', $course->id)->first();

        // Add Lesson in Module 1
        Livewire::test(CurriculumBuilder::class, ['course' => $course])
            ->set('targetModuleId', $module->id)
            ->set('newLessonTitle', 'Aula 1.1: O Papel do Líder')
            ->set('newLessonVideoUrl', 'https://vimeo.com/123456789')
            ->set('newLessonDuration', 25)
            ->set('newLessonIsPreview', true)
            ->call('addLesson')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Aula 1.1: O Papel do Líder',
            'video_url' => 'https://vimeo.com/123456789',
            'duration_minutes' => 25,
            'order' => 1,
            'is_free_preview' => true,
        ]);
    }

    public function test_instructor_can_reorder_modules_and_lessons(): void
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
            'title' => 'Curso de Vendas B2B',
            'slug' => 'curso-de-vendas-b2b',
            'status' => 'draft',
        ]);

        $module1 = Module::create([
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $module2 = Module::create([
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Módulo 2',
            'order' => 2,
        ]);

        $this->actingAs($instructor);

        // Move module 1 down
        Livewire::test(CurriculumBuilder::class, ['course' => $course])
            ->call('moveModule', $module1->id, 'down')
            ->assertDispatched('toast');

        $module1->refresh();
        $module2->refresh();

        $this->assertEquals(2, $module1->order);
        $this->assertEquals(1, $module2->order);
    }

    public function test_instructor_can_add_attachments_to_lesson(): void
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
            'title' => 'Curso de Finanças',
            'slug' => 'curso-de-financas',
            'status' => 'draft',
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Aula 1',
            'duration_minutes' => 15,
            'order' => 1,
        ]);

        $this->actingAs($instructor);

        Livewire::test(CurriculumBuilder::class, ['course' => $course])
            ->set('targetLessonId', $lesson->id)
            ->set('attachmentTitle', 'Planilha de Orçamento')
            ->set('attachmentUrl', 'https://storage.exemplo.com/planilha.xlsx')
            ->call('addAttachment')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('lesson_attachments', [
            'lesson_id' => $lesson->id,
            'company_id' => $company->id,
            'title' => 'Planilha de Orçamento',
            'file_path' => 'https://storage.exemplo.com/planilha.xlsx',
        ]);
    }
}
