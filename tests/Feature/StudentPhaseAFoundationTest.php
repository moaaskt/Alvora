<?php

namespace Tests\Feature;

use App\Actions\Student\EnrollStudentAction;
use App\Enums\UserRole;
use App\Events\CourseProgressUpdated;
use App\Helpers\DurationHelper;
use App\Helpers\StudentNavHelper;
use App\Livewire\Student\Catalog as StudentCatalog;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\Tracks as StudentTracks;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class StudentPhaseAFoundationTest extends TestCase
{
    use DatabaseTransactions;

    protected function createCompanyWithUsers(): array
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa {$unique}", 'status' => 'active']);

        $instructor = User::create([
            'name' => "Instrutor {$unique}",
            'email' => "inst_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => "Aluno {$unique}",
            'email' => "aluno_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        return [$company, $instructor, $student, $unique];
    }

    public function test_duration_helper_formats_edge_cases(): void
    {
        $this->assertSame('', DurationHelper::format(0));
        $this->assertSame('45min', DurationHelper::format(45));
        $this->assertSame('59min', DurationHelper::format(59));
        $this->assertSame('1h', DurationHelper::format(60));
        $this->assertSame('1h 1min', DurationHelper::format(61));
        $this->assertSame('1h 15min', DurationHelper::format(75));
        $this->assertSame('2h', DurationHelper::format(120));
    }

    public function test_event_is_dispatched_when_course_progress_is_recalculated(): void
    {
        Event::fake([CourseProgressUpdated::class]);

        [$company, $instructor, $student, $unique] = $this->createCompanyWithUsers();

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso IA {$unique}",
            'slug' => "curso-ia-{$unique}",
            'status' => 'published',
        ]);

        $enrollment = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        $enrollment->recalculateProgress();

        Event::assertDispatched(CourseProgressUpdated::class, function ($event) use ($enrollment) {
            return $event->enrollment->id === $enrollment->id;
        });
    }

    public function test_listener_recalculates_track_progress_on_lesson_completion_and_undo(): void
    {
        [$company, $instructor, $student, $unique] = $this->createCompanyWithUsers();

        $course1 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso 1 {$unique}",
            'slug' => "curso-1-{$unique}",
            'status' => 'published',
        ]);

        $course2 = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso 2 {$unique}",
            'slug' => "curso-2-{$unique}",
            'status' => 'published',
        ]);

        $module1 = Module::create([
            'company_id' => $company->id,
            'course_id' => $course1->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson1 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course1->id,
            'module_id' => $module1->id,
            'title' => 'Aula 1',
            'order' => 1,
        ]);

        $module2 = Module::create([
            'company_id' => $company->id,
            'course_id' => $course2->id,
            'title' => 'Módulo 2',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course2->id,
            'module_id' => $module2->id,
            'title' => 'Aula 2',
            'order' => 1,
        ]);

        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha Integrada {$unique}",
            'slug' => "trilha-integrada-{$unique}",
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
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        $enrollment2 = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course2->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        $trackEnrollment = TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        // Aluno conclui aula 1 do curso 1
        $progress1 = LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
            'completed_at' => now(),
        ]);

        // Recalcula curso 1 (100%). Curso 2 está em 0%. Média da trilha = 50%.
        $enrollment1->recalculateProgress();

        $this->assertEquals(50, $trackEnrollment->fresh()->progress_percent);
        $this->assertEquals('active', $trackEnrollment->fresh()->status);

        // Desmarca a aula (undo)
        $progress1->update(['completed_at' => null]);
        $enrollment1->recalculateProgress();

        $this->assertEquals(0, $trackEnrollment->fresh()->progress_percent);
        $this->assertEquals('active', $trackEnrollment->fresh()->status);
        $this->assertNull($trackEnrollment->fresh()->completed_at);

        // Marca aula 1 e também conclui aula 2 para testar 100% -> 'completed'
        $progress1->update(['completed_at' => now()]);
        $enrollment1->recalculateProgress();

        LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson2->id,
            'completed_at' => now(),
        ]);
        $enrollment2->recalculateProgress();

        $freshTrack = $trackEnrollment->fresh();
        $this->assertEquals(100, $freshTrack->progress_percent);
        $this->assertEquals('completed', $freshTrack->status);
        $this->assertNotNull($freshTrack->completed_at);
    }

    public function test_track_progress_returns_zero_safely_when_track_has_zero_published_courses(): void
    {
        [$company, $instructor, $student, $unique] = $this->createCompanyWithUsers();

        $draftCourse = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Rascunho {$unique}",
            'slug' => "rascunho-{$unique}",
            'status' => 'draft',
        ]);

        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha Vazia {$unique}",
            'slug' => "trilha-vazia-{$unique}",
            'is_mandatory' => true,
        ]);

        $track->courses()->attach([$draftCourse->id => ['order' => 1]]);

        $this->assertCount(0, $track->publishedCourses);
        $this->assertSame(0, $track->calculateUserProgress($student->id));
    }

    public function test_no_student_screen_render_issues_an_update_query(): void
    {
        [$company, $instructor, $student, $unique] = $this->createCompanyWithUsers();

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Teste {$unique}",
            'slug' => "curso-teste-{$unique}",
            'status' => 'published',
        ]);

        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha Teste {$unique}",
            'slug' => "trilha-teste-{$unique}",
            'is_mandatory' => true,
        ]);
        $track->courses()->attach([$course->id => ['order' => 1]]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        $this->actingAs($student);
        StudentNavHelper::reset();

        DB::enableQueryLog();

        Livewire::test(StudentDashboard::class)->assertStatus(200);
        Livewire::test(StudentTracks::class)->assertStatus(200);
        Livewire::test(StudentCatalog::class)->assertStatus(200);

        $queries = DB::getQueryLog();
        $updateQueries = array_filter($queries, function ($q) {
            return stripos(trim($q['query']), 'update') === 0;
        });

        $this->assertCount(0, $updateQueries, 'No UPDATE queries should be issued during student screen rendering.');
        DB::disableQueryLog();
    }

    public function test_artisan_backfill_command_with_dry_run_and_tenant_isolation(): void
    {
        [$companyA, $instructorA, $studentA, $uniqA] = $this->createCompanyWithUsers();
        [$companyB, $instructorB, $studentB, $uniqB] = $this->createCompanyWithUsers();

        $courseA = Course::create([
            'company_id' => $companyA->id,
            'instructor_id' => $instructorA->id,
            'title' => "Curso A {$uniqA}",
            'slug' => "curso-a-{$uniqA}",
            'status' => 'published',
        ]);

        $trackA = LearningTrack::create([
            'company_id' => $companyA->id,
            'title' => "Trilha A {$uniqA}",
            'slug' => "trilha-a-{$uniqA}",
            'is_mandatory' => true,
        ]);
        $trackA->courses()->attach([$courseA->id => ['order' => 1]]);

        Enrollment::create([
            'company_id' => $companyA->id,
            'user_id' => $studentA->id,
            'course_id' => $courseA->id,
            'status' => 'completed',
            'progress_percent' => 100,
        ]);

        $teA = TrackEnrollment::create([
            'company_id' => $companyA->id,
            'user_id' => $studentA->id,
            'track_id' => $trackA->id,
            'status' => 'active',
            'progress_percent' => 0, // stale value
        ]);

        // Dry run test: does not persist
        $this->artisan('tracks:backfill-progress', ['--tenant' => $companyA->id, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals(0, $teA->fresh()->progress_percent);

        // Real run test: persists 100% and completed
        $this->artisan('tracks:backfill-progress', ['--tenant' => $companyA->id])
            ->assertSuccessful();

        $fresh = $teA->fresh();
        $this->assertEquals(100, $fresh->progress_percent);
        $this->assertEquals('completed', $fresh->status);
    }

    public function test_new_student_routes_auth_and_access(): void
    {
        [$company, $instructor, $student, $unique] = $this->createCompanyWithUsers();

        // Guest access redirects to login
        $this->get(route('student.tracks'))->assertRedirect(route('login'));
        $this->get(route('student.catalog'))->assertRedirect(route('login'));

        // Authenticated student access
        $this->actingAs($student);
        $this->get(route('student.tracks'))->assertStatus(200);
        $this->get(route('student.catalog'))->assertStatus(200);
    }
}
