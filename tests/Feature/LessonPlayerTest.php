<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Student\LessonPlayer;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LessonPlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_access_lesson_player_and_auto_enrolls(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Fundamentos de Segurança B2B',
            'slug' => 'fundamentos-de-seguranca-b2b',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Introdução',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: Ameaças Modernas',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'order' => 1,
        ]);

        $this->actingAs($student);

        // Acesso à rota do player
        $response = $this->get(route('student.player', ['course' => $course->slug, 'lesson' => $lesson->id]));
        $response->assertStatus(200);
        $response->assertSee('Fundamentos de Segurança B2B');
        $response->assertSee('Aula 1: Ameaças Modernas');

        // Confirma auto-matrícula criada
        $this->assertDatabaseHas('enrollments', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_student_can_toggle_lesson_completion_and_recalculates_course_progress(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Arquitetura de Microsserviços',
            'slug' => 'arquitetura-de-microsservicos',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Conceitos Básicos',
            'order' => 1,
        ]);

        $lesson1 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: O que é microsserviço',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 2: Comunicação síncrona vs assíncrona',
            'order' => 2,
        ]);

        $this->actingAs($student);

        // Marca aula 1 como concluída
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson1])
            ->call('toggleComplete')
            ->assertSet('isCompleted', true)
            ->assertSet('progressPercent', 50);

        $this->assertDatabaseHas('lesson_progress', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
        ]);

        // Matrícula atualizada para 50%
        $this->assertDatabaseHas('enrollments', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percent' => 50,
        ]);

        // Marca aula 2 como concluída -> atinge 100%
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson2])
            ->call('toggleComplete')
            ->assertSet('isCompleted', true)
            ->assertSet('progressPercent', 100);

        // Matrícula concluída
        $this->assertDatabaseHas('enrollments', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percent' => 100,
            'status' => 'completed',
        ]);
    }

    public function test_student_can_navigate_between_lessons(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestão Ágil de Projetos',
            'slug' => 'gestao-agil-de-projetos',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Scrum e Kanban',
            'order' => 1,
        ]);

        $lesson1 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: Rituais do Scrum',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 2: Métricas de Fluxo Kanban',
            'order' => 2,
        ]);

        $this->actingAs($student);

        // Começa na aula 1 e clica em próxima aula
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson1])
            ->call('nextLesson')
            ->assertSet('currentLesson.id', $lesson2->id)
            ->call('previousLesson')
            ->assertSet('currentLesson.id', $lesson1->id);
    }

    public function test_student_can_save_personal_notes(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Liderança Corporativa',
            'slug' => 'lideranca-corporativa',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Soft Skills',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: Escuta Ativa',
            'order' => 1,
        ]);

        $this->actingAs($student);

        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->set('noteContent', 'Anotação importante sobre feedbacks empáticos.')
            ->call('saveNote');

        $this->assertDatabaseHas('lesson_notes', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'content' => 'Anotação importante sobre feedbacks empáticos.',
        ]);
    }

    public function test_student_cannot_access_course_or_lessons_from_another_tenant(): void
    {
        $companyA = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Empresa Beta', 'status' => 'active']);

        $instructorB = User::create([
            'name' => 'Instrutor Beta',
            'email' => 'beta@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $studentA = User::create([
            'name' => 'Aluno Alfa',
            'email' => 'alfa@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $courseB = Course::create([
            'company_id' => $companyB->id,
            'instructor_id' => $instructorB->id,
            'title' => 'Estratégia Secreta Beta',
            'slug' => 'estrategia-secreta-beta',
            'status' => 'published',
        ]);

        $this->actingAs($studentA);

        // Tentativa de acesso via slug do curso de outra empresa retorna 404 pelo TenantScope
        $response = $this->get(route('student.player', ['course' => $courseB->slug]));
        $response->assertStatus(404);
    }

    public function test_student_with_100_percent_progress_but_unpassed_quizzes_sees_warning_on_download_attempt(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Alfa',
            'email' => 'instructor@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Alfa',
            'email' => 'aluno@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso com Quiz Obrigatório',
            'slug' => 'curso-com-quiz-obrigatorio',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'order' => 1,
            'duration_minutes' => 60,
        ]);

        // Cria quiz obrigatório não realizado
        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Final Obrigatório',
            'passing_score' => 70,
        ]);

        // Matrícula com 100% de progresso de aulas
        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student);

        // 1. O componente Livewire do player deve exibir "Quizzes Pendentes" e NÃO o botão de download
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSee('Quizzes Pendentes')
            ->assertDontSee('Baixar Certificado 🎓');

        // 2. Tentativa direta de download do certificado redireciona para o player com flash warning
        $response = $this->get(route('student.certificate', $course->slug));
        $response->assertRedirect(route('student.player', $course->slug));
        $response->assertSessionHas('warning', 'Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');

        // 3. Ao carregar a página do player com a sessão de aviso, a notificação visual é renderizada
        $playerPage = $this->followingRedirects()->get(route('student.certificate', $course->slug));
        $playerPage->assertStatus(200);
        $playerPage->assertSee('Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');
    }

    public function test_student_with_full_eligibility_sees_active_download_button(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Alfa',
            'email' => 'instructor2@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Alfa',
            'email' => 'aluno2@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso Completo e Aprovado',
            'slug' => 'curso-completo-e-aprovado',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'order' => 1,
            'duration_minutes' => 60,
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Final Obrigatório',
            'passing_score' => 70,
        ]);

        // Registra aprovação no quiz
        QuizAttempt::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 95,
            'passed' => true,
            'completed_at' => now(),
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student);

        // 1. O componente Livewire exibe o botão ativo de download de certificado
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSee('Baixar Certificado 🎓')
            ->assertDontSee('Quizzes Pendentes');

        // 2. Download direto do certificado retorna 200 com arquivo PDF
        $response = $this->get(route('student.certificate', $course->slug));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
