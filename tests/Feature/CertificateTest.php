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
use Illuminate\Support\Facades\DB;
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

    public function test_certificate_pdf_renders_strictly_single_page(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa Testes', 'status' => 'active']);

        $user = User::create([
            'name' => 'Maria Aparecida de Souza Gonçalves da Silva Pereira',
            'email' => 'maria.longname@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $instructor = User::create([
            'name' => 'Instrutor Especialista',
            'email' => 'instructor@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Especialização Avançada em Compliance Corporativo, Governança e LGPD Aplicada',
            'slug' => 'especializacao-avancada-compliance-lgpd',
            'status' => 'published',
        ]);

        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-ONEPAGE',
            'total_hours_minutes' => 120,
            'issued_at' => now(),
        ]);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);
        $pdfOutput = $service->generatePdf($certificate)->output();

        // No formato de documento PDF gerado pelo Dompdf/CPDF, cada página é identificada pelo dicionário /Type /Page (sem 's')
        $pageCount = preg_match_all('#/Type\s*/Page[^s]#', $pdfOutput);

        $this->assertEquals(1, $pageCount, "O PDF do certificado deve possuir rigorosamente 1 página, porém foram geradas {$pageCount} páginas.");
    }

    public function test_authenticated_user_from_different_tenant_can_verify_certificate_without_500(): void
    {
        $companyA = Company::create(['name' => 'Empresa Emissora A', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Empresa Externa B', 'status' => 'active']);

        $instructorA = User::create([
            'name' => 'Instrutor A',
            'email' => 'instructor.a@empresa-a.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $studentA = User::create([
            'name' => 'Aluno da Empresa A',
            'email' => 'aluno.a@empresa-a.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $userFromCompanyB = User::create([
            'name' => 'Gestor de RH Empresa B',
            'email' => 'rh.b@empresa-b.com',
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $courseA = Course::create([
            'company_id' => $companyA->id,
            'instructor_id' => $instructorA->id,
            'title' => 'Curso de Liderança Corporativa A',
            'slug' => 'curso-de-lideranca-corporativa-a',
            'status' => 'published',
        ]);

        $certificate = Certificate::create([
            'company_id' => $companyA->id,
            'user_id' => $studentA->id,
            'course_id' => $courseA->id,
            'validation_code' => 'ALV-2026-CROSS01',
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        // Simula o Gestor da Empresa B logado acessando a página pública de autenticidade
        $this->actingAs($userFromCompanyB);

        $response = $this->get(route('certificates.verify', 'ALV-2026-CROSS01'));

        $response->assertStatus(200);
        $response->assertSee('Certificado Válido e Registrado');
        $response->assertSee('Aluno da Empresa A');
        $response->assertSee('Curso de Liderança Corporativa A');
        $response->assertSee('Empresa Emissora A');
        $response->assertSee('ALV-2026-CROSS01');
    }

    public function test_public_verification_does_not_leak_cross_tenant_access_to_private_routes(): void
    {
        $companyA = Company::create(['name' => 'Empresa Alfa Privada', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Empresa Beta Investigadora', 'status' => 'active']);

        $instructorA = User::create([
            'name' => 'Instrutor A',
            'email' => 'inst.priv@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $studentB = User::create([
            'name' => 'Aluno Invasor da Empresa B',
            'email' => 'aluno.b@beta.com',
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::STUDENT,
        ]);

        $courseA = Course::create([
            'company_id' => $companyA->id,
            'instructor_id' => $instructorA->id,
            'title' => 'Curso Sigiloso da Empresa Alfa',
            'slug' => 'curso-sigiloso-da-empresa-alfa',
            'status' => 'published',
        ]);

        // Usuário logado na Empresa B tenta baixar certificado do curso da Empresa A
        $this->actingAs($studentB);

        $response = $this->get(route('student.certificate', $courseA->slug));

        // Rota privada deve barrar com 404 estrito pelo TenantScope
        $response->assertStatus(404);
    }

    public function test_concurrent_emission_collision_during_insert_recovers_idempotently_via_catch_block(): void
    {
        $company = Company::create(['name' => 'Empresa Concorrencia Real', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Concorrente',
            'email' => 'inst.conc@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Veloz',
            'email' => 'aluno.veloz@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestao de Crise em Tempo Real',
            'slug' => 'gestao-de-crise-em-tempo-real',
            'status' => 'published',
        ]);

        // Matricula 100% concluida
        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        // Garante que ANTES da chamada NAO existe certificado algum
        $this->assertNull(Certificate::where('user_id', $student->id)->where('course_id', $course->id)->first());

        // Para exercitar deterministicamente o bloco catch durante o Certificate::create(),
        // registramos um listener que insere o registro vencedor exatamente no momento do creating,
        // simulando outra transacao paralela que completou o INSERT entre o SELECT inicial e o INSERT desta thread.
        $insertedWinner = null;
        $hookTriggered = false;

        $listener = function () use ($student, $course, $company, &$insertedWinner, &$hookTriggered) {
            if (!$hookTriggered) {
                $hookTriggered = true;
                // Insere registro diretamente no banco sem re-disparar o mesmo hook
                $insertedWinner = Certificate::withoutEvents(function () use ($student, $course, $company) {
                    return Certificate::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'company_id' => $company->id,
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'validation_code' => 'ALV-2026-RACEWINNER',
                        'total_hours_minutes' => 60,
                        'issued_at' => now(),
                    ]);
                });
            }
        };

        Certificate::getEventDispatcher()->listen('eloquent.creating: ' . Certificate::class, $listener);

        try {
            /** @var \App\Services\CertificateService $service */
            $service = app(\App\Services\CertificateService::class);

            // Esta chamada passa pelo first() inicial (que retorna null), pelo isEligible() e tenta Certificate::create().
            // No create(), o listener insere o registro vencedor. O INSERT do Eloquent colide na constraint
            // unica REAL do banco de dados (SQLite 19 / MySQL 1062) e cai estritamente no bloco catch de recuperacao!
            $certificate = $service->getOrCreateCertificate($student, $course);

            $this->assertTrue($hookTriggered, 'O hook de colisao deveria ter sido acionado durante o create.');
            $this->assertNotNull($insertedWinner, 'O registro concorrente deveria ter sido persistido.');
            $this->assertEquals($insertedWinner->id, $certificate->id, 'Deve retornar o registro vencedor recuperado no catch.');
            $this->assertEquals('ALV-2026-RACEWINNER', $certificate->validation_code);
            $this->assertEquals(1, Certificate::where('user_id', $student->id)->where('course_id', $course->id)->count());
        } finally {
            // Limpa o listener para nao interferir em outros testes
            Certificate::flushEventListeners();
            Certificate::boot();
        }
    }

    public function test_service_does_not_mask_non_unique_database_exceptions(): void
    {
        $company = Company::create(['name' => 'Empresa Erro Banco', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor DB',
            'email' => 'inst.db@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno DB',
            'email' => 'aluno.db@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Teste de Falha DB',
            'slug' => 'curso-teste-falha-db',
            'status' => 'published',
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        // Simula falha de banco de dados nao relacionada a chave unica (ex: tabela inexistente)
        $listener = function () {
            DB::statement('SELECT * FROM non_existent_table_to_verify_unmasked_exception');
        };

        Certificate::getEventDispatcher()->listen('eloquent.creating: ' . Certificate::class, $listener);

        try {
            $service = app(\App\Services\CertificateService::class);

            $this->expectException(\Illuminate\Database\QueryException::class);
            $service->getOrCreateCertificate($student, $course);
        } finally {
            Certificate::flushEventListeners();
            Certificate::boot();
        }
    }

    public function test_public_verification_endpoint_enforces_rate_limiting(): void
    {
        $code = 'ALV-2026-RATE01';

        // Executa 60 requisicoes dentro do limite permitido
        for ($i = 0; $i < 60; $i++) {
            $response = $this->get(route('certificates.verify', $code));
            $this->assertNotEquals(429, $response->getStatusCode(), "Requisicao {$i} nao deveria receber 429.");
        }

        // A 61ª requisicao no mesmo minuto deve ser bloqueada por rate limiting (HTTP 429)
        $response = $this->get(route('certificates.verify', $code));
        $response->assertStatus(429);
    }
}
