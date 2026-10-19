<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_cannot_generate_certificate_if_progress_below_100(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Segurança Cibernética para Bancos',
            'slug' => 'seguranca-cibernetica-para-bancos',
            'status' => 'published',
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 50, // Incompleto!
        ]);

        $this->actingAs($student);

        $response = $this->get(route('student.certificate', $course->slug));

        // Redireciona de volta com aviso
        $response->assertRedirect(route('student.player', $course->slug));
        $response->assertSessionHas('warning');

        $this->assertDatabaseMissing('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_eligible_student_can_download_certificate_pdf(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestão de Crise e Continuidade',
            'slug' => 'gestao-de-crise-e-continuidade',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Plano de Resposta',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: Introdução aos Incidentes',
            'duration_minutes' => 60,
            'order' => 1,
        ]);

        // Matrícula 100% concluída
        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        // Quiz obrigatório aprovado
        $quiz = Quiz::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Avaliação Final',
            'passing_score' => 70,
        ]);

        QuizAttempt::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($student);

        $response = $this->get(route('student.certificate', $course->slug));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'pdf'));

        $this->assertDatabaseHas('certificates', [
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'total_hours_minutes' => 60,
        ]);
    }

    public function test_public_user_can_verify_authentic_certificate_by_code(): void
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
            'name' => 'Aluno Lucas',
            'email' => 'lucas@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Compliance e LGPD Corporativa',
            'slug' => 'compliance-e-lgpd-corporativa',
            'status' => 'published',
        ]);

        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-TEST99',
            'total_hours_minutes' => 120,
            'issued_at' => now(),
        ]);

        // Acesso público sem autenticação
        $response = $this->get(route('certificates.verify', 'ALV-2026-TEST99'));

        $response->assertStatus(200);
        $response->assertSee('Certificado Válido e Registrado');
        $response->assertSee('Aluno Lucas');
        $response->assertSee('Compliance e LGPD Corporativa');
        $response->assertSee('ALV-2026-TEST99');
    }

    public function test_public_user_gets_invalid_notice_for_non_existent_code(): void
    {
        $response = $this->get(route('certificates.verify', 'ALV-0000-INVALID'));

        $response->assertStatus(200);
        $response->assertSee('Certificado Inválido ou Inexistente');
    }

    public function test_student_cannot_download_certificate_of_another_tenant(): void
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
            'title' => 'Curso Exclusivo Empresa Beta',
            'slug' => 'curso-exclusivo-empresa-beta',
            'status' => 'published',
        ]);

        $this->actingAs($studentA);

        // Acesso a curso de outro tenant retorna 404 pelo TenantScope
        $response = $this->get(route('student.certificate', $courseB->slug));
        $response->assertStatus(404);
    }
}
