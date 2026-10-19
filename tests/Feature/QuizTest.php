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
            ->assertRedirect(route('instructor.courses.curriculum', $course));

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
}
