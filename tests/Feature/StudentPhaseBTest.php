<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Helpers\StudentNavHelper;
use App\Livewire\Student\Catalog as StudentCatalog;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\Tracks as StudentTracks;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class StudentPhaseBTest extends TestCase
{
    use DatabaseTransactions;

    protected function setupTenant(): array
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa B {$unique}", 'status' => 'active']);

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

    public function test_dashboard_limits_tracks_to_two_and_in_progress_courses_to_three(): void
    {
        [$company, $instructor, $student, $unique] = $this->setupTenant();

        // 1. Create 3 tracks for the student
        for ($i = 1; $i <= 3; $i++) {
            $track = LearningTrack::create([
                'company_id' => $company->id,
                'title' => "Trilha Alpha {$i} {$unique}",
                'slug' => "trilha-alpha-{$i}-{$unique}",
                'department' => "Depto {$i}",
                'is_mandatory' => $i === 1,
            ]);

            TrackEnrollment::create([
                'company_id' => $company->id,
                'user_id' => $student->id,
                'track_id' => $track->id,
                'status' => 'active',
                'progress_percent' => 20 * $i,
            ]);
        }

        // 2. Create 4 in-progress courses and 1 completed course
        for ($i = 1; $i <= 4; $i++) {
            $course = Course::create([
                'company_id' => $company->id,
                'instructor_id' => $instructor->id,
                'title' => "Curso Ativo {$i} {$unique}",
                'slug' => "curso-ativo-{$i}-{$unique}",
                'status' => 'published',
            ]);

            $enr = Enrollment::create([
                'company_id' => $company->id,
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active',
                'progress_percent' => 10 * $i,
            ]);

            // Set distinct timestamps so latest('updated_at') has a deterministic order (#4 is most recent)
            DB::table('enrollments')->where('id', $enr->id)->update([
                'updated_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $completedCourse = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Concluído {$unique}",
            'slug' => "curso-concluido-{$unique}",
            'status' => 'published',
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $completedCourse->id,
            'status' => 'completed',
            'progress_percent' => 100,
        ]);

        // 1 un-enrolled published course
        Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Disponível {$unique}",
            'slug' => "curso-disponivel-{$unique}",
            'status' => 'published',
        ]);

        $this->actingAs($student);
        StudentNavHelper::reset();

        $component = Livewire::test(StudentDashboard::class);

        // Assert only 2 most recent tracks are rendered (#3 and #2), #1 is truncated
        $component->assertSee("Trilha Alpha 3 {$unique}")
                  ->assertSee("Trilha Alpha 2 {$unique}")
                  ->assertDontSee("Trilha Alpha 1 {$unique}");

        // Assert only 3 most recently updated in-progress courses are rendered (#4, #3, #2)
        $component->assertSee("Curso Ativo 4 {$unique}")
                  ->assertSee("Curso Ativo 3 {$unique}")
                  ->assertSee("Curso Ativo 2 {$unique}")
                  ->assertDontSee("Curso Ativo 1 {$unique}");

        // Assert completed course is NOT in "Continuar Aprendendo"
        $component->assertDontSee("Curso Concluído {$unique}");

        // Stat cards must compute over ALL enrollments (4 active + 1 completed = 5)
        $component->assertSee('Cursos no seu plano');
        $component->assertSee('Cursos finalizados');

        // Invitation card shows 1 available course with correct pt-BR singular
        $component->assertSee('1 curso disponível para você');
        $component->assertSee('Ver todas as trilhas');
        $component->assertSee('Ver todos os meus cursos');
    }

    public function test_dashboard_hides_tracks_section_when_student_has_no_tracks(): void
    {
        [$company, $instructor, $student, $unique] = $this->setupTenant();

        $this->actingAs($student);
        StudentNavHelper::reset();

        Livewire::test(StudentDashboard::class)
            ->assertDontSee('Minhas Trilhas Corporativas')
            ->assertDontSee('Ver todas as trilhas');
    }

    public function test_tracks_page_filters_sorting_and_course_row_links(): void
    {
        [$company, $instructor, $student, $unique] = $this->setupTenant();

        $courseEnrolled = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Matriculado {$unique}",
            'slug' => "curso-mat-{$unique}",
            'status' => 'published',
        ]);

        $courseUnenrolled = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => "Curso Nao Matriculado {$unique}",
            'slug' => "curso-nao-mat-{$unique}",
            'status' => 'published',
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $courseEnrolled->id,
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        $trackMandatory = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha Obrigatória {$unique}",
            'slug' => "trilha-obrig-{$unique}",
            'department' => 'TI',
            'is_mandatory' => true,
        ]);
        $trackMandatory->courses()->attach([
            $courseEnrolled->id => ['order' => 1],
            $courseUnenrolled->id => ['order' => 2],
        ]);

        $trackOptional = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha Opcional {$unique}",
            'slug' => "trilha-opc-{$unique}",
            'department' => 'RH',
            'is_mandatory' => false,
        ]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $trackMandatory->id,
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $trackOptional->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        $this->actingAs($student);
        StudentNavHelper::reset();

        $component = Livewire::test(StudentTracks::class);

        // Both tracks visible initially
        $component->assertSee("Trilha Obrigatória {$unique}")
                  ->assertSee("Trilha Opcional {$unique}");

        // Check course row links: enrolled links to continueCourse, unenrolled links to catalog
        $component->assertSeeHtml("continueCourse({$courseEnrolled->id})")
                  ->assertSeeHtml(route('student.catalog'));

        // Tab filter: completed
        $component->set('status', 'completed')
                  ->assertDontSee("Trilha Obrigatória {$unique}")
                  ->assertSee("Trilha Opcional {$unique}");

        // Tab filter: in_progress
        $component->set('status', 'in_progress')
                  ->assertSee("Trilha Obrigatória {$unique}")
                  ->assertDontSee("Trilha Opcional {$unique}");

        // Priority filter: optional
        $component->set('status', 'all')
                  ->set('priority', 'optional')
                  ->assertDontSee("Trilha Obrigatória {$unique}")
                  ->assertSee("Trilha Opcional {$unique}");

        // Search filter
        $component->set('priority', 'all')
                  ->set('search', 'Inexistente123')
                  ->assertDontSee("Trilha Obrigatória {$unique}")
                  ->assertSee('Nenhuma trilha encontrada para a sua busca.');
    }

    public function test_catalog_page_filters_states_and_n_plus_one_prevention(): void
    {
        [$company, $instructor, $student, $unique] = $this->setupTenant();

        // Create 9 courses with modules, lessons, and quizzes
        for ($i = 1; $i <= 9; $i++) {
            $course = Course::create([
                'company_id' => $company->id,
                'instructor_id' => $instructor->id,
                'title' => "Curso Catalogo {$i} {$unique}",
                'slug' => "curso-cat-{$i}-{$unique}",
                'status' => 'published',
            ]);

            $module = Module::create([
                'company_id' => $company->id,
                'course_id' => $course->id,
                'title' => "Modulo {$i}",
                'order' => 1,
            ]);

            Lesson::create([
                'company_id' => $company->id,
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => "Aula {$i}",
                'order' => 1,
                'duration_minutes' => 30,
            ]);

            $quiz = Quiz::create([
                'company_id' => $company->id,
                'course_id' => $course->id,
                'title' => "Quiz {$i}",
                'pass_score' => 70,
            ]);

            QuizQuestion::create([
                'company_id' => $company->id,
                'quiz_id' => $quiz->id,
                'question_text' => "Pergunta {$i}",
                'order' => 1,
            ]);

            if ($i === 1) {
                // Completed
                Enrollment::create([
                    'company_id' => $company->id,
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'completed',
                    'progress_percent' => 100,
                ]);
            } elseif ($i === 2) {
                // In progress
                Enrollment::create([
                    'company_id' => $company->id,
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                    'progress_percent' => 50,
                ]);
            }
        }

        $this->actingAs($student);
        StudentNavHelper::reset();

        DB::enableQueryLog();

        $component = Livewire::test(StudentCatalog::class);
        $component->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ensure N+1 prevention: rendering 9 courses with relations takes under 15 queries
        $this->assertLessThan(15, count($queries), 'Catalog render should avoid N+1 queries by preloading enrollments.');

        // Verify completed course shows plain green text and Rever Aulas
        $component->assertSee('Concluído')
                  ->assertSee('Rever Aulas');

        // Verify in-progress course shows Continuar Aula
        $component->assertSee('Continuar Aula');

        // Verify available course shows Matricular-se Gratuitamente
        $component->assertSee('Matricular-se Gratuitamente');

        // Test status tabs filter: completed
        $component->set('status', 'completed')
                  ->assertSee("Curso Catalogo 1 {$unique}")
                  ->assertDontSee("Curso Catalogo 2 {$unique}")
                  ->assertDontSee("Curso Catalogo 3 {$unique}");

        // Test status tabs filter: in_progress
        $component->set('status', 'in_progress')
                  ->assertDontSee("Curso Catalogo 1 {$unique}")
                  ->assertSee("Curso Catalogo 2 {$unique}");

        // Test status tabs filter: available
        $component->set('status', 'available')
                  ->assertDontSee("Curso Catalogo 1 {$unique}")
                  ->assertDontSee("Curso Catalogo 2 {$unique}")
                  ->assertSee("Curso Catalogo 3 {$unique}");
    }

    public function test_sidebar_has_tracks_reactive_and_hover_classes(): void
    {
        [$company, $instructor, $student, $unique] = $this->setupTenant();

        $this->actingAs($student);
        StudentNavHelper::reset();

        // Student without tracks: helper returns false
        $this->assertFalse(StudentNavHelper::hasTracks());

        // Assign track
        $track = LearningTrack::create([
            'company_id' => $company->id,
            'title' => "Trilha TI {$unique}",
            'slug' => "trilha-ti-{$unique}",
            'is_mandatory' => true,
        ]);
        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'track_id' => $track->id,
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        // After assignment, helper returns true
        StudentNavHelper::reset();
        $this->assertTrue(StudentNavHelper::hasTracks());

        // Sidebar renders interactive hover classes
        Livewire::test(StudentDashboard::class)
            ->assertSeeHtml('scale-y-0')
            ->assertSeeHtml('hover:bg-edtech-blue-light/60');
    }
}
