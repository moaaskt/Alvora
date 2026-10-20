<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\TrackManager;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LearningTrackTest extends TestCase
{
    use DatabaseTransactions;

    public function test_company_admin_can_create_learning_track_with_ordered_courses(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Tech Corp {$unique}", 'status' => 'active']);

        $admin = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$unique}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $instructor = User::create([
            'name' => 'Instrutor Dev',
            'email' => "dev_{$unique}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course1 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Introdução à Segurança',
            'slug' => "intro-seguranca-{$unique}",
            'status' => 'published',
        ]);

        $course2 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Boas Práticas de Cloud',
            'slug' => "boas-praticas-cloud-{$unique}",
            'status' => 'published',
        ]);

        $student = User::create([
            'name' => 'Colaborador João',
            'email' => "joao_{$unique}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($admin);

        Livewire::test(TrackManager::class)
            ->set('title', 'Trilha Onboarding TI')
            ->set('description', 'Trilha fundamental para novos engenheiros')
            ->set('department', 'Tecnologia')
            ->set('is_mandatory', true)
            ->set('selectedCourses', [$course1->id, $course2->id])
            ->set('selectedUsers', [$student->id])
            ->call('saveTrack')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('learning_tracks', [
            'company_id' => $company->id,
            'title' => 'Trilha Onboarding TI',
            'department' => 'Tecnologia',
            'is_mandatory' => 1,
        ]);

        $track = LearningTrack::where('company_id', $company->id)->where('title', 'Trilha Onboarding TI')->first();
        $this->assertNotNull($track);

        $this->assertDatabaseHas('track_courses', [
            'track_id' => $track->id,
            'course_id' => $course1->id,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('track_courses', [
            'track_id' => $track->id,
            'course_id' => $course2->id,
            'order' => 2,
        ]);

        $this->assertDatabaseHas('track_enrollments', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
        ]);
    }

    public function test_consolidated_track_progress_calculation_for_student(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Alpha Inc {$unique}", 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Geral',
            'email' => "inst_{$unique}@alpha.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Mariana Silva',
            'email' => "mariana_{$unique}@alpha.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course1 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Módulo 1: Fundamentos',
            'slug' => "modulo-1-fundamentos-{$unique}",
            'status' => 'published',
        ]);

        $course2 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Módulo 2: Avançado',
            'slug' => "modulo-2-avancado-{$unique}",
            'status' => 'published',
        ]);

        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => 'Formação de Liderança',
            'slug' => "formacao-de-lideranca-{$unique}",
            'is_mandatory' => true,
        ]);

        $track->courses()->attach([
            $course1->id => ['order' => 1],
            $course2->id => ['order' => 2],
        ]);

        $enrollment1 = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course1->id,
            'progress_percent' => 100,
            'status' => 'completed',
        ]);

        $enrollment2 = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course2->id,
            'progress_percent' => 50,
            'status' => 'active',
        ]);

        $trackEnrollment = TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        // (100 + 50) / 2 = 75%
        $progress = $track->calculateUserProgress($student->id);
        $this->assertEquals(75, $progress);

        $trackEnrollment->updateConsolidatedProgress();
        $this->assertEquals(75, $trackEnrollment->fresh()->progress_percent);
        $this->assertEquals('active', $trackEnrollment->fresh()->status);

        // Aluno conclui o curso 2
        $enrollment2->update([
            'progress_percent' => 100,
            'status' => 'completed',
        ]);

        // (100 + 100) / 2 = 100%
        $trackEnrollment->updateConsolidatedProgress();
        $this->assertEquals(100, $trackEnrollment->fresh()->progress_percent);
        $this->assertEquals('completed', $trackEnrollment->fresh()->status);
        $this->assertNotNull($trackEnrollment->fresh()->completed_at);
    }

    public function test_multi_tenant_isolation_tracks_cannot_be_viewed_by_another_company(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Empresa Alfa {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Empresa Beta {$unique}", 'status' => 'active']);

        $adminA = User::create([
            'name' => 'Admin Alfa',
            'email' => "admin_a_{$unique}@alfa.com",
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $adminB = User::create([
            'name' => 'Admin Beta',
            'email' => "admin_b_{$unique}@beta.com",
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $trackAlfa = LearningTrack::create([
            'company_id' => $companyA->id,
            'title' => 'Trilha Secreta Alfa',
            'slug' => "trilha-secreta-alfa-{$unique}",
            'is_mandatory' => true,
        ]);

        // Admin B no TrackManager não deve ver a trilha da Empresa A
        $this->actingAs($adminB);

        Livewire::test(TrackManager::class)
            ->assertDontSee('Trilha Secreta Alfa');
    }

    public function test_student_dashboard_displays_enrolled_corporate_tracks(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Fintech Prime {$unique}", 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Compliance',
            'email' => "inst_{$unique}@fintech.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Carlos Andrade',
            'email' => "carlos_{$unique}@fintech.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Prevenção à Lavagem de Dinheiro',
            'slug' => "prevencao-lavagem-dinheiro-{$unique}",
            'status' => 'published',
        ]);

        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => 'Trilha Compliance Bancário',
            'slug' => "trilha-compliance-bancario-{$unique}",
            'department' => 'Compliance',
            'is_mandatory' => true,
        ]);

        $track->courses()->attach([
            $course->id => ['order' => 1],
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percent' => 50,
            'status' => 'active',
        ]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        $this->actingAs($student);

        Livewire::test(StudentDashboard::class)
            ->assertSee('Minhas Trilhas Corporativas')
            ->assertSee('Trilha Compliance Bancário')
            ->assertSee('Compliance')
            ->assertSee('Obrigatória')
            ->assertSee('50%');
    }
}
