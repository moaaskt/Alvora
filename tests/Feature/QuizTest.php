<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Instructor\QuizEditor;
use App\Livewire\Student\QuizRunner;
use App\Models\Company;
use App\Models\Course;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use DatabaseTransactions;

    public function test_instructor_can_create_quiz_with_questions_and_options(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'DevOps e CI/CD Corporativo',
            'slug' => 'devops-e-ci-cd-corporativo',
            'status' => 'published',
        ]);

        $this->actingAs($instructor);

        Livewire::test(QuizEditor::class, ['course' => $course])
            ->set('title', 'Avaliação de Fixação CI/CD')
            ->set('passing_score', 80)
            ->set('questions', [
                [
                    'id' => null,
                    'question_text' => 'O que significa CI em DevOps?',
                    'explanation' => 'CI significa Continuous Integration (Integração Contínua).',
                    'options' => [
                        ['id' => null, 'option_text' => 'Continuous Integration', 'is_correct' => true],
                        ['id' => null, 'option_text' => 'Continuous Internet', 'is_correct' => false],
                    ],
                ],
            ])
            ->call('save')
            ->assertRedirect(route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']));

        $this->assertDatabaseHas('quizzes', [
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Avaliação de Fixação CI/CD',
            'passing_score' => 80,
        ]);

        $this->assertDatabaseHas('quiz_questions', [
            'company_id' => $company->id,
            'question_text' => 'O que significa CI em DevOps?',
        ]);

        $this->assertDatabaseHas('quiz_options', [
            'option_text' => 'Continuous Integration',
            'is_correct' => 1,
        ]);
    }

    public function test_student_can_take_quiz_and_pass_with_minimum_score(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Segurança de Aplicações',
            'slug' => 'seguranca-de-aplicacoes',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz OWASP Top 10',
            'passing_score' => 70,
        ]);

        $question = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'O que previne ataques de SQL Injection?',
            'order' => 1,
        ]);

        $correctOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Prepared Statements (Consultas Parametrizadas)',
            'is_correct' => true,
        ]);

        $wrongOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Concatenação direta de strings no SQL',
            'is_correct' => false,
        ]);

        $this->actingAs($student);

        // Aluno responde com a opção correta
        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->call('selectOption', $question->id, $correctOption->id)
            ->call('submit')
            ->assertSet('score', 100)
            ->assertSet('passed', true)
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('quiz_attempts', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'passed' => 1,
        ]);
    }

    public function test_student_fails_quiz_when_below_passing_score(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Segurança de Aplicações',
            'slug' => 'seguranca-de-aplicacoes-2',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Risco Cibernético',
            'passing_score' => 80,
        ]);

        $question = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'O que é autenticação multifator (MFA)?',
            'order' => 1,
        ]);

        $correctOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Uso de dois ou mais fatores distintos de autenticação',
            'is_correct' => true,
        ]);

        $wrongOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Compartilhar senhas por e-mail',
            'is_correct' => false,
        ]);

        $this->actingAs($student);

        // Aluno seleciona a opção errada
        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->call('selectOption', $question->id, $wrongOption->id)
            ->call('submit')
            ->assertSet('score', 0)
            ->assertSet('passed', false)
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('quiz_attempts', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'passed' => 0,
        ]);
    }

    public function test_student_cannot_access_quiz_from_another_tenant(): void
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
            'title' => 'Gestão Confidencial Beta',
            'slug' => 'gestao-confidencial-beta',
            'status' => 'published',
        ]);

        $quizB = Quiz::create([
            'company_id' => $companyB->id,
            'course_id' => $courseB->id,
            'title' => 'Quiz Exclusivo Beta',
            'passing_score' => 70,
        ]);

        $this->actingAs($studentA);

        // Acesso ao quiz de outra empresa deve falhar com 404 devido ao TenantScope
        $response = $this->get(route('student.quiz', $quizB));
        $response->assertStatus(404);
    }

    public function test_instructor_can_access_quiz_edit_screen_without_errors(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'DevOps e CI/CD Corporativo',
            'slug' => 'devops-e-ci-cd-corporativo-edit',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Avaliação Final DevOps',
            'passing_score' => 75,
        ]);

        $this->actingAs($instructor);

        // Testa rota /quizzes/{quiz}/editar (instructor.quizzes.edit) sem erros 500
        $responseA = $this->get(route('instructor.quizzes.edit', $quiz));
        $responseA->assertStatus(200);
        $responseA->assertSee('Editar Avaliação / Quiz');
        $responseA->assertSee('DevOps e CI/CD Corporativo');

        // Testa rota alternativa /cursos/{course}/quizzes/{quiz}/editar (instructor.courses.quizzes.edit)
        $responseB = $this->get(route('instructor.courses.quizzes.edit', ['course' => $course, 'quiz' => $quiz]));
        $responseB->assertStatus(200);
        $responseB->assertSee('Editar Avaliação / Quiz');
        $responseB->assertSee('DevOps e CI/CD Corporativo');

        // Testa montagem e estado inicial do componente Livewire
        Livewire::test(QuizEditor::class, ['quiz' => $quiz])
            ->assertSet('title', 'Avaliação Final DevOps')
            ->assertSet('passing_score', 75);
    }

    public function test_quiz_runner_renders_new_visual_structure_and_back_link_points_to_player(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Liderança e Estratégia',
            'slug' => 'lideranca-e-estrategia',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Avaliação Final de Liderança',
            'passing_score' => 70,
        ]);

        $question = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'Qual é o pilar fundamental?',
            'order' => 1,
        ]);

        QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Opção A',
            'is_correct' => true,
        ]);

        $this->actingAs($student);

        $expectedPlayerUrl = route('student.player', ['course' => $course->slug]);

        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->assertSee('Voltar ao Curso')
            ->assertSee($expectedPlayerUrl)
            ->assertSee('Avaliação Final de Liderança')
            ->assertSee('Nota mínima para aprovação: 70%')
            ->assertSee('Questão 1 de 1')
            ->assertSee('Respondidas:')
            ->assertSee('questões')
            ->assertSee('Finalizar e Ver Resultado')
            ->assertDontSee('Aprovado')
            ->assertDontSee('Não aprovado');
    }

    public function test_quiz_runner_result_banner_and_sticky_bar_behavior(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestão de Mudanças',
            'slug' => 'gestao-de-mudancas',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz de Mudanças',
            'passing_score' => 70,
        ]);

        $question = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'O que impulsiona a mudança?',
            'order' => 1,
        ]);

        $correctOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Comunicação clara e liderança ativa',
            'is_correct' => true,
        ]);

        $wrongOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Imposição unilateral sem alinhamento',
            'is_correct' => false,
        ]);

        $this->actingAs($student);

        // Cenário 1: Reprovação (seleciona errada)
        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->call('selectOption', $question->id, $wrongOption->id)
            ->call('submit')
            ->assertSee('Não aprovado')
            ->assertSee('Você obteve 0% de aproveitamento.')
            ->assertSee('Tentar Novamente')
            ->assertDontSee('Aprovado')
            ->assertDontSee('Finalizar e Ver Resultado'); // Sticky bar oculta após envio

        // Cenário 2: Aprovação (retry e seleciona correta)
        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->call('retry')
            ->assertDontSee('Não aprovado')
            ->assertDontSee('Aprovado')
            ->assertSee('Finalizar e Ver Resultado') // Sticky bar reaparece
            ->call('selectOption', $question->id, $correctOption->id)
            ->call('submit')
            ->assertSee('Aprovado')
            ->assertSee('Você obteve 100% de aproveitamento.')
            ->assertSee('Voltar ao Curso')
            ->assertDontSee('Não aprovado')
            ->assertDontSee('Tentar Novamente')
            ->assertDontSee('Finalizar e Ver Resultado');
    }

    public function test_quiz_runner_explanation_box_presence_after_submission(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Comunicação Corporativa',
            'slug' => 'comunicacao-corporativa',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz Comunicação',
            'passing_score' => 70,
        ]);

        $questionWithExp = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'Questão com explicação detalhada',
            'explanation' => 'Esta é a justificativa pedagógica fundamental.',
            'order' => 1,
        ]);

        $optA = QuizOption::create([
            'quiz_question_id' => $questionWithExp->id,
            'option_text' => 'Alternativa 1',
            'is_correct' => true,
        ]);

        $questionWithoutExp = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'Questão sem explicação',
            'explanation' => null,
            'order' => 2,
        ]);

        $optB = QuizOption::create([
            'quiz_question_id' => $questionWithoutExp->id,
            'option_text' => 'Alternativa 2',
            'is_correct' => true,
        ]);

        $this->actingAs($student);

        // Antes do envio, a justificativa não deve aparecer
        $test = Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->assertDontSee('Justificativa')
            ->assertDontSee('Esta é a justificativa pedagógica fundamental.');

        // Após envio, exibe a justificativa apenas da questão que a possui
        $test->call('selectOption', $questionWithExp->id, $optA->id)
            ->call('selectOption', $questionWithoutExp->id, $optB->id)
            ->call('submit')
            ->assertSee('Justificativa')
            ->assertSee('Esta é a justificativa pedagógica fundamental.');
    }

    public function test_quiz_runner_option_styles_present_after_submission(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Roberto',
            'email' => 'roberto@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Marcos',
            'email' => 'marcos@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestão Visual de Estilos',
            'slug' => 'gestao-visual-de-estilos',
            'status' => 'published',
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Quiz de Estilos',
            'passing_score' => 70,
        ]);

        $question = QuizQuestion::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'question_text' => 'Verificação de estilos de gabarito',
            'order' => 1,
        ]);

        $correctOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Opção Verdadeira',
            'is_correct' => true,
        ]);

        $wrongSelectedOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Opção Errada Selecionada',
            'is_correct' => false,
        ]);

        $wrongUnselectedOption = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Opção Errada Não Selecionada',
            'is_correct' => false,
        ]);

        $this->actingAs($student);

        Livewire::test(QuizRunner::class, ['quiz' => $quiz])
            ->call('selectOption', $question->id, $wrongSelectedOption->id)
            ->call('submit')
            // Verifica estilo da correta: bg-green-50 e border-green-200
            ->assertSeeHtml('bg-green-50 border border-green-200 text-green-700')
            // Verifica estilo da errada selecionada: bg-red-50 e border-red-200
            ->assertSeeHtml('bg-red-50 border border-red-200 text-red-600')
            // Verifica estilo neutro não selecionado: opacity-60
            ->assertSeeHtml('opacity-60');
    }
}
