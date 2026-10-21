<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\CalculateEvasionRiskJob;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\RiskScore;
use App\Models\User;
use App\Services\EvasionRiskCalculatorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EvasionRiskCalculationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_active_student_with_good_grades_has_low_risk_score(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Ativa {$unique}", 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Engajado',
            'email' => "engajado_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $student->id,
            'title' => 'Curso de Alta Performance',
            'slug' => "curso-performance-{$unique}",
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
            'duration_minutes' => 15,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(), // Concluiu agora
        ]);

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Aula 1',
            'passing_score' => 70,
        ]);

        QuizAttempt::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 100, // Nota máxima
            'passed' => true,
        ]);

        $calculator = new EvasionRiskCalculatorService();
        $riskScore = $calculator->calculateForUser($student);

        $this->assertInstanceOf(RiskScore::class, $riskScore);
        $this->assertEquals($student->id, $riskScore->user_id);
        $this->assertEquals($company->id, $riskScore->company_id);
        $this->assertLessThan(40, $riskScore->score);
        $this->assertEquals('low', $riskScore->level);
        $this->assertTrue($riskScore->isLowRisk());
        $this->assertArrayHasKey('inactivity', $riskScore->breakdown);
        $this->assertArrayHasKey('stagnation', $riskScore->breakdown);
        $this->assertArrayHasKey('quiz', $riskScore->breakdown);
        $this->assertArrayHasKey('peer_deviation', $riskScore->breakdown);
    }

    public function test_inactive_student_with_poor_grades_has_high_risk_score(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Inativa {$unique}", 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Inativo',
            'email' => "inativo_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        // Força inatividade de 20 dias
        $student->updated_at = now()->subDays(20);
        $student->saveQuietly();

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $student->id,
            'title' => 'Curso de Liderança',
            'slug' => "curso-lideranca-{$unique}",
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
            'duration_minutes' => 20,
        ]);

        // Matrícula há 20 dias sem nenhuma aula concluída
        $enrollment = Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()->subDays(20),
        ]);
        $enrollment->created_at = now()->subDays(20);
        $enrollment->saveQuietly();

        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Inicial',
            'passing_score' => 70,
        ]);

        QuizAttempt::create([
            'company_id' => $company->id,
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 10, // Nota muito baixa
            'passed' => false,
        ]);

        $calculator = new EvasionRiskCalculatorService();
        $riskScore = $calculator->calculateForUser($student);

        $this->assertGreaterThanOrEqual(70, $riskScore->score);
        $this->assertEquals('high', $riskScore->level);
        $this->assertTrue($riskScore->isHighRisk());
    }

    public function test_risk_scores_respect_tenant_isolation(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Tenant A {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Tenant B {$unique}", 'status' => 'active']);

        $studentA = User::create([
            'name' => 'Aluno A',
            'email' => "a_{$unique}@tenanta.com",
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $studentB = User::create([
            'name' => 'Aluno B',
            'email' => "b_{$unique}@tenantb.com",
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::STUDENT,
        ]);

        $calculator = new EvasionRiskCalculatorService();
        $calculator->calculateForUser($studentA);
        $calculator->calculateForUser($studentB);

        // Consultando como Tenant A
        $this->actingAs($studentA);
        $scoresA = RiskScore::all();

        $this->assertTrue($scoresA->every(fn($score) => $score->company_id === $companyA->id));
        $this->assertFalse($scoresA->contains('company_id', $companyB->id));
    }

    public function test_calculate_evasion_risk_job_executes_successfully(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Tenant Job {$unique}", 'status' => 'active']);

        $student = User::create([
            'name' => 'Aluno Job',
            'email' => "job_{$unique}@tenant.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $job = new CalculateEvasionRiskJob(userId: $student->id);
        app()->call([$job, 'handle']);

        $this->assertDatabaseHas('risk_scores', [
            'user_id' => $student->id,
            'company_id' => $company->id,
        ]);

        $this->assertNotNull($student->latestRiskScore);
        $this->assertEquals($student->id, $student->latestRiskScore->user_id);
    }
}
