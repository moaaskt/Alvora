<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Student\LessonPlayer;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonAttachment;
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
            ->assertSet('progressPercent', 50)
            ->assertSee('Concluída');

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

    public function test_student_can_save_personal_notes_and_receives_event(): void
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
            ->call('saveNote')
            ->assertDispatched('note-saved');

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

        // 1. O componente Livewire do player deve exibir "Quizzes pendentes:" e "Fazer quiz", e NÃO o botão de download
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSee('Quizzes pendentes:')
            ->assertSee('Fazer quiz')
            ->assertDontSee('Baixar Certificado');

        // 2. O link aponta para a rota real student.quiz e alcança o QuizRunner com status 200
        $quizResponse = $this->get(route('student.quiz', $quiz));
        $quizResponse->assertStatus(200);
        $quizResponse->assertSee('Quiz Final Obrigatório');

        // 3. Tentativa direta de download do certificado redireciona para o player com flash warning
        $response = $this->get(route('student.certificate', $course->slug));
        $response->assertRedirect(route('student.player', $course->slug));
        $response->assertSessionHas('warning', 'Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');

        // 4. Ao carregar a página do player com a sessão de aviso, a notificação visual é renderizada
        $playerPage = $this->followingRedirects()->get(route('student.certificate', $course->slug));
        $playerPage->assertStatus(200);
        $playerPage->assertSee('Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');
    }

    public function test_student_with_multiple_pending_quizzes_resolves_first_one_and_no_quiz_handles_cleanly(): void
    {
        $company = Company::create(['name' => 'Empresa Multi', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Multi',
            'email' => 'inst_multi@multi.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Multi',
            'email' => 'student_multi@multi.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso com Dois Quizzes',
            'slug' => 'curso-com-dois-quizzes',
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
            'title' => 'Aula Única',
            'order' => 1,
            'duration_minutes' => 30,
        ]);

        // Dois quizzes
        $quiz1 = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Inicial',
            'passing_score' => 70,
        ]);

        $quiz2 = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Avançado',
            'passing_score' => 70,
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

        // Deve apontar para o primeiro quiz pendente ($quiz1)
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSee('Quizzes pendentes:')
            ->assertSee(route('student.quiz', $quiz1));

        // Se o aluno for aprovado no quiz1, o próximo deve ser o quiz2
        QuizAttempt::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz1->id,
            'user_id' => $student->id,
            'score' => 100,
            'passed' => true,
            'completed_at' => now(),
        ]);

        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSee('Quizzes pendentes:')
            ->assertSee(route('student.quiz', $quiz2));
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
            ->assertSee('Baixar Certificado')
            ->assertDontSee('Quizzes pendentes:');

        // 2. Download direto do certificado retorna 200 com arquivo PDF
        $response = $this->get(route('student.certificate', $course->slug));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_lesson_player_sidebar_module_counters_and_plurals(): void
    {
        $company = Company::create(['name' => 'Empresa Plural', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Teste',
            'email' => 'instructor_test@plural.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Plural',
            'email' => 'aluno_plural@plural.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Gramática & Métricas',
            'slug' => 'curso-de-gramatica-metricas',
            'status' => 'published',
        ]);

        // Módulo 1 com 1 aula (singular: 1 aula)
        $module1 = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Singular',
            'order' => 1,
        ]);

        $lesson1 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module1->id,
            'title' => 'Aula 1 Solitária',
            'order' => 1,
        ]);

        // Anexos: 1 anexo na aula 1
        LessonAttachment::create([
            'company_id' => $company->id,
            'lesson_id' => $lesson1->id,
            'title' => 'Guia Prático PDF',
            'file_path' => 'attachments/guia.pdf',
            'file_type' => 'pdf',
            'file_size_bytes' => 102400,
        ]);

        // Módulo 2 com 2 aulas (plural: 2 aulas)
        $module2 = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 2: Plural',
            'order' => 2,
        ]);

        $lesson2 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module2->id,
            'title' => 'Aula 2 Primeira',
            'order' => 1,
        ]);

        $lesson3 = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module2->id,
            'title' => 'Aula 3 Segunda',
            'order' => 2,
        ]);

        $this->actingAs($student);

        // Verifica renderização e contadores corretos:
        // Total de aulas: 3 aulas
        // Módulo 1: 0/1
        // Módulo 2: 0/2
        // Anexo: "1 anexo"
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson1])
            ->assertSee('0 / 3 aulas')
            ->assertSee('0/1')
            ->assertSee('0/2')
            ->assertSee('1 anexo')
            ->assertDontSee('1 anexos')
            ->assertDontSee('1 anexo(s)');

        // Completa a aula 1
        LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
            'completed_at' => now(),
        ]);

        // Agora Módulo 1 deve mostrar 1/1
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson1])
            ->assertSee('1 / 3 aulas')
            ->assertSee('1/1');
    }

    public function test_lesson_player_overview_shows_lesson_description_with_fallback_to_course(): void
    {
        $company = Company::create(['name' => 'Empresa Desc', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Desc',
            'email' => 'inst_desc@desc.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Desc',
            'email' => 'aluno_desc@desc.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Pedagogia Corporativa',
            'slug' => 'curso-de-pedagogia-corporativa',
            'description' => 'Descrição Geral do Curso Corporativo.',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        // Aula com descrição própria
        $lessonWithDesc = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula com Descrição Própria',
            'description' => 'Objetivo específico desta aula técnica.',
            'order' => 1,
        ]);

        // Aula sem descrição
        $lessonWithoutDesc = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula Sem Descrição',
            'description' => null,
            'order' => 2,
        ]);

        $this->actingAs($student);

        // Aula 1 deve exibir a descrição específica da aula
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessonWithDesc])
            ->set('activeTab', 'overview')
            ->assertSee('Objetivo específico desta aula técnica.')
            ->assertDontSee('Descrição Geral do Curso Corporativo.');

        // Aula 2 deve fazer fallback para a descrição do curso
        Livewire::test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessonWithoutDesc])
            ->set('activeTab', 'overview')
            ->assertSee('Descrição Geral do Curso Corporativo.');
    }

    public function test_lesson_player_layout_hides_hero_band_and_ai_tutor(): void
    {
        $company = Company::create(['name' => 'Empresa Layout', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Layout',
            'email' => 'inst_layout@layout.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Layout',
            'email' => 'aluno_layout@layout.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Design System',
            'slug' => 'curso-de-design-system',
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
        ]);

        $this->actingAs($student);

        // 1. No player de aula:
        // - Não deve ter o botão/gaveta do Tutor IA
        // - Não deve ter a faixa de gradiente hero (340px)
        $playerResponse = $this->get(route('student.player', ['course' => $course->slug, 'lesson' => $lesson->id]));
        $playerResponse->assertStatus(200);
        $playerResponse->assertDontSee('Tutor Alvora AI');
        $playerResponse->assertDontSee('HERO BACKGROUND BAND');

        // 2. No dashboard do aluno:
        // - Deve ter o botão do Tutor IA
        // - Deve ter a faixa de gradiente hero
        $dashboardResponse = $this->get(route('student.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Tutor Alvora AI');
        $dashboardResponse->assertSee('HERO BACKGROUND BAND');
    }
}
