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
}
