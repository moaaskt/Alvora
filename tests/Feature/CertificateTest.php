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
        $insertedWinnerId = null;
        $hookTriggered = false;

        $listener = function () use ($student, $course, $company, &$insertedWinnerId, &$hookTriggered) {
            if (!$hookTriggered) {
                $hookTriggered = true;
                // Executa uma operacao SQL independente diretamente no banco,
                // inserindo o registro vencedor milissegundos antes da instrucao INSERT do Eloquent
                $uuid = (string) \Illuminate\Support\Str::uuid();
                DB::table('certificates')->insert([
                    'uuid' => $uuid,
                    'company_id' => $company->id,
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'validation_code' => 'ALV-2026-RACEWINNER',
                    'total_hours_minutes' => 60,
                    'issued_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertedWinnerId = DB::table('certificates')
                    ->where('user_id', $student->id)
                    ->where('course_id', $course->id)
                    ->value('id');
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
            $this->assertNotNull($insertedWinnerId, 'O registro concorrente deveria ter sido persistido.');
            $this->assertEquals($insertedWinnerId, $certificate->id, 'Deve retornar o registro vencedor recuperado no catch.');
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

    public function test_new_certificate_emission_captures_complete_original_snapshot(): void
    {
        $company = Company::create(['name' => 'TechCorp Treinamentos', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Prof. Carlos',
            'email' => 'carlos@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Juliana Mendes',
            'email' => 'juliana@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Engenharia de Prompt Corporativa',
            'slug' => 'engenharia-de-prompt-corporativa',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula Fundamentos',
            'duration_minutes' => 90,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);
        $certificate = $service->getOrCreateCertificate($student, $course);

        $this->assertNotNull($certificate->snapshot);
        $this->assertTrue($certificate->hasOriginalSnapshot());
        $this->assertFalse($certificate->isLegacyWithoutSnapshot());
        $this->assertFalse($certificate->isReconstructed());

        $this->assertEquals('original', $certificate->snapshot['mode']);
        $this->assertEquals('Juliana Mendes', $certificate->snapshot['student_name']);
        $this->assertEquals('Engenharia de Prompt Corporativa', $certificate->snapshot['course_title']);
        $this->assertEquals('TechCorp Treinamentos', $certificate->snapshot['company_name']);
        $this->assertEquals(90, $certificate->snapshot['workload_minutes']);
        $this->assertNotEmpty($certificate->snapshot['captured_at']);

        // Accessors devem retornar exatamente os valores do snapshot
        $this->assertEquals('Juliana Mendes', $certificate->student_name);
        $this->assertEquals('Engenharia de Prompt Corporativa', $certificate->course_title);
        $this->assertEquals('TechCorp Treinamentos', $certificate->company_name);
        $this->assertEquals('1h 30min', $certificate->formattedWorkload());
    }

    public function test_subsequent_changes_to_user_course_and_company_do_not_mutate_snapshot_or_effective_attributes(): void
    {
        $company = Company::create(['name' => 'Empresa Original S/A', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Original',
            'email' => 'original.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Nome Antigo',
            'email' => 'aluno.antigo@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Liderança 2026',
            'slug' => 'curso-de-lideranca-2026',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'duration_minutes' => 60,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);
        $certificate = $service->getOrCreateCertificate($student, $course);

        // Modifica registros vigentes posteriores
        $student->update(['name' => 'Aluno Nome Completamente Novo']);
        $course->update(['title' => 'Curso Renomeado para Versao 2.0']);
        $company->update(['name' => 'Empresa Fusao Holding Nova']);

        // Recarrega o certificado do banco
        $certificate = $certificate->fresh();

        // O snapshot e os atributos efetivos NÃO devem ser alterados
        $this->assertEquals('Aluno Nome Antigo', $certificate->snapshot['student_name']);
        $this->assertEquals('Curso de Liderança 2026', $certificate->snapshot['course_title']);
        $this->assertEquals('Empresa Original S/A', $certificate->snapshot['company_name']);

        $this->assertEquals('Aluno Nome Antigo', $certificate->student_name);
        $this->assertEquals('Curso de Liderança 2026', $certificate->course_title);
        $this->assertEquals('Empresa Original S/A', $certificate->company_name);
    }

    public function test_pdf_and_public_verification_render_historical_snapshot_values(): void
    {
        $company = Company::create(['name' => 'Inovacao Corp', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Prof. Paulo',
            'email' => 'paulo@inovacao.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Beatriz Lima',
            'email' => 'beatriz@inovacao.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Gestão de Projetos Ágeis',
            'slug' => 'gestao-de-projetos-ageis',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Scrum Master',
            'duration_minutes' => 120,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);
        $certificate = $service->getOrCreateCertificate($student, $course);

        // Modifica os registros cadastrais atuais
        $student->update(['name' => 'Beatriz Lima Silva Casada']);
        $course->update(['title' => 'Design Sprint e Scrum']);
        $company->update(['name' => 'Inovacao Global Tech']);

        // 1. Validação no PDF
        $pdf = $service->generatePdf($certificate);
        $renderedHtml = view('certificates.template', ['certificate' => $certificate])->render();

        $this->assertStringContainsString('Beatriz Lima', $renderedHtml);
        $this->assertStringNotContainsString('Beatriz Lima Silva Casada', $renderedHtml);

        $this->assertStringContainsString('Gestão de Projetos Ágeis', $renderedHtml);
        $this->assertStringNotContainsString('Design Sprint e Scrum', $renderedHtml);

        $this->assertStringContainsString('Inovacao Corp', $renderedHtml);
        $this->assertStringNotContainsString('Inovacao Global Tech', $renderedHtml);

        // 2. Validação na página pública
        $response = $this->get(route('certificates.verify', $certificate->validation_code));
        $response->assertStatus(200);

        $response->assertSee('Beatriz Lima');
        $response->assertDontSee('Beatriz Lima Silva Casada');

        $response->assertSee('Gestão de Projetos Ágeis');
        $response->assertDontSee('Design Sprint e Scrum');

        $response->assertSee('Inovacao Corp');
        $response->assertDontSee('Inovacao Global Tech');

        $response->assertSee('Registro Histórico Original: Dados imutáveis preservados desde a emissão.');
    }

    public function test_legacy_certificates_without_snapshot_fallback_to_current_data_with_honest_notice(): void
    {
        $company = Company::create(['name' => 'Empresa Legado Ltda', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Legado',
            'email' => 'legado.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Colaborador Legado',
            'email' => 'legado.colab@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso Emitido no Sistema Antigo',
            'slug' => 'curso-emitido-no-sistema-antigo',
            'status' => 'published',
        ]);

        // Cria certificado simulando emissão legada antes da Fase 1 (snapshot null)
        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2025-LEGACY99',
            'total_hours_minutes' => 60,
            'snapshot' => null,
            'issued_at' => now()->subYear(),
        ]);

        $this->assertTrue($certificate->isLegacyWithoutSnapshot());
        $this->assertFalse($certificate->hasOriginalSnapshot());
        $this->assertFalse($certificate->isReconstructed());

        // Deve retornar por fallback os valores cadastrais vigentes
        $this->assertEquals('Colaborador Legado', $certificate->student_name);
        $this->assertEquals('Curso Emitido no Sistema Antigo', $certificate->course_title);
        $this->assertEquals('Empresa Legado Ltda', $certificate->company_name);

        // Verificação pública deve exibir aviso honesto
        $response = $this->get(route('certificates.verify', 'ALV-2025-LEGACY99'));
        $response->assertStatus(200);
        $response->assertSee('Registro Histórico Legado: Documento emitido no formato anterior (dados cadastrais vigentes).');
        $response->assertDontSee('Registro Histórico Original: Dados imutáveis preservados desde a emissão.');

        // Se o aluno mudar de nome, no legado reflete a mudança atual com transparência
        $student->update(['name' => 'Colaborador Legado Atualizado']);
        $certificate = $certificate->fresh();
        $this->assertEquals('Colaborador Legado Atualizado', $certificate->student_name);
    }

    public function test_reconstructed_certificates_render_reconstructed_notice(): void
    {
        $company = Company::create(['name' => 'Empresa Recon S/A', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Recon',
            'email' => 'recon.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Recon',
            'email' => 'recon.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso para Reconstrucao',
            'slug' => 'curso-para-reconstrucao',
            'status' => 'published',
        ]);

        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-RECON01',
            'total_hours_minutes' => 90,
            'snapshot' => [
                'mode' => 'reconstructed',
                'student_name' => 'Aluno Nome Histórico Reconstruído',
                'course_title' => 'Curso Histórico Reconstruído',
                'company_name' => 'Empresa Histórica Reconstruída',
                'workload_minutes' => 90,
                'reconstructed_at' => now()->toIso8601String(),
                'reason' => 'Recomposição administrativa de registro pré-migração',
            ],
            'issued_at' => now()->subMonths(6),
        ]);

        $this->assertTrue($certificate->isReconstructed());
        $this->assertFalse($certificate->hasOriginalSnapshot());
        $this->assertFalse($certificate->isLegacyWithoutSnapshot());

        $this->assertEquals('Aluno Nome Histórico Reconstruído', $certificate->student_name);
        $this->assertEquals('Curso Histórico Reconstruído', $certificate->course_title);
        $this->assertEquals('Empresa Histórica Reconstruída', $certificate->company_name);

        $response = $this->get(route('certificates.verify', 'ALV-2026-RECON01'));
        $response->assertStatus(200);
        $response->assertSee('Registro Histórico Reconstruído: Snapshot recomposto retroativamente.');
    }

    public function test_course_and_user_with_issued_certificates_cannot_be_deleted(): void
    {
        $company = Company::create(['name' => 'Empresa Integridade', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Integro',
            'email' => 'integro.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Protegido',
            'email' => 'protegido.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso com Certificado Ativo',
            'slug' => 'curso-com-certificado-ativo',
            'status' => 'published',
        ]);

        Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-NODELETE',
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        // 1. Tentar excluir o curso deve disparar DomainException e ser impedido
        try {
            $course->delete();
            $this->fail('Deveria ter lançado DomainException ao tentar excluir curso com certificados.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('possui certificados emitidos', $e->getMessage());
        }

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('certificates', ['validation_code' => 'ALV-2026-NODELETE']);

        // 2. Tentar excluir o aluno deve disparar DomainException e ser impedido
        try {
            $student->delete();
            $this->fail('Deveria ter lançado DomainException ao tentar excluir aluno com certificados.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('possui certificados emitidos', $e->getMessage());
        }

        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $this->assertDatabaseHas('certificates', ['validation_code' => 'ALV-2026-NODELETE']);
    }

    public function test_course_list_component_prevents_deletion_of_course_with_certificates(): void
    {
        $company = Company::create(['name' => 'Empresa Componente', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Componente',
            'email' => 'comp.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Componente',
            'email' => 'comp.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso Componente Protegido',
            'slug' => 'curso-componente-protegido',
            'status' => 'published',
        ]);

        Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-COMPDEL',
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        $this->actingAs($instructor);

        \Livewire\Livewire::test(\App\Livewire\Instructor\CourseList::class)
            ->call('deleteCourse', $course->id)
            ->assertDispatched('toast', message: "Não é possível excluir o curso '{$course->title}' pois ele possui certificados emitidos.", type: 'error');

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('certificates', ['validation_code' => 'ALV-2026-COMPDEL']);
    }

    public function test_json_snapshot_persistence_and_integrity_in_database(): void
    {
        $company = Company::create(['name' => 'Empresa JSON Test', 'status' => 'active']);

        $user = User::create([
            'name' => 'Aluno JSON',
            'email' => 'json@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $user->id,
            'title' => 'Curso JSON Integrity',
            'slug' => 'curso-json-integrity',
            'status' => 'published',
        ]);

        $snapshotData = [
            'mode' => 'original',
            'student_name' => 'Aluno JSON Teste',
            'course_title' => 'Curso JSON Integrity',
            'company_name' => 'Empresa JSON Test',
            'workload_minutes' => 180,
            'captured_at' => '2026-10-25T19:30:00+00:00',
        ];

        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-JSON01',
            'total_hours_minutes' => 180,
            'snapshot' => $snapshotData,
            'issued_at' => now(),
        ]);

        // Lê diretamente via DB query bruta para comprovar persistência no formato JSON do banco
        $rawRow = DB::table('certificates')->where('id', $certificate->id)->first();
        $this->assertNotNull($rawRow->snapshot);

        $decoded = json_decode($rawRow->snapshot, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('original', $decoded['mode']);
        $this->assertEquals('Aluno JSON Teste', $decoded['student_name']);
        $this->assertEquals(180, $decoded['workload_minutes']);
    }

    public function test_company_with_issued_certificates_cannot_be_deleted(): void
    {
        $company = Company::create(['name' => 'Empresa Protegida Contra Exclusao', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Protegido',
            'email' => 'comp.prot.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Protegido Company',
            'email' => 'comp.prot.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Blindagem',
            'slug' => 'curso-de-blindagem',
            'status' => 'published',
        ]);

        Certificate::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'validation_code' => 'ALV-2026-COMPBLOCK',
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        try {
            $company->delete();
            $this->fail('Deveria ter lançado DomainException ao tentar excluir empresa com certificados.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('possui certificados emitidos', $e->getMessage());
        }

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
        $this->assertDatabaseHas('certificates', ['validation_code' => 'ALV-2026-COMPBLOCK']);
    }

    public function test_absence_of_company_does_not_generate_fictional_name_in_snapshot_or_views(): void
    {
        $company = Company::create(['name' => 'Empresa Base', 'status' => 'active']);

        $certificate = Certificate::create([
            'company_id' => $company->id,
            'user_id' => User::create([
                'name' => 'Aluno Sem Empresa',
                'email' => 'sem.empresa.aluno@alvora.com',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'role' => UserRole::STUDENT,
            ])->id,
            'course_id' => Course::create([
                'company_id' => $company->id,
                'instructor_id' => User::create([
                    'name' => 'Instrutor Base',
                    'email' => 'inst.base@alvora.com',
                    'password' => bcrypt('password'),
                    'company_id' => $company->id,
                    'role' => UserRole::INSTRUCTOR,
                ])->id,
                'title' => 'Curso Aberto ALVORA',
                'slug' => 'curso-aberto-alvora',
                'status' => 'published',
            ])->id,
            'validation_code' => 'ALV-2026-NOCOMPANY',
            'total_hours_minutes' => 45,
            'snapshot' => [
                'mode' => 'original',
                'student_name' => 'Aluno Sem Empresa',
                'course_title' => 'Curso Aberto ALVORA',
                'company_name' => null, // ausência explícita comprovável
                'workload_minutes' => 45,
                'captured_at' => now()->toIso8601String(),
            ],
            'issued_at' => now(),
        ]);

        $this->assertNull($certificate->snapshot['company_name']);
        $this->assertNull($certificate->company_name);

        // 1. Renderiza o PDF
        $renderedPdfHtml = view('certificates.template', ['certificate' => $certificate])->render();
        $this->assertStringNotContainsString('Empresa Cliente', $renderedPdfHtml);
        $this->assertStringNotContainsString('&bull; </div>', $renderedPdfHtml);
        $this->assertStringContainsString('Registro Corporativo', $renderedPdfHtml);

        // 2. Renderiza a validação pública
        $response = $this->get(route('certificates.verify', 'ALV-2026-NOCOMPANY'));
        $response->assertStatus(200);
        $response->assertDontSee('Empresa Cliente');
        $response->assertSee('Não informada');
    }

    public function test_issuance_and_snapshot_capture_share_strictly_consistent_timestamp(): void
    {
        $company = Company::create(['name' => 'Empresa Timestamp Test', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Time',
            'email' => 'time.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Time',
            'email' => 'time.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso Timestamp Check',
            'slug' => 'curso-timestamp-check',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'duration_minutes' => 60,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);
        $certificate = $service->getOrCreateCertificate($student, $course);

        // Verifica consistência temporal absoluta: o captured_at do snapshot reflete exatamente o mesmo instante do issued_at
        $this->assertEquals(
            $certificate->issued_at->toIso8601String(),
            $certificate->snapshot['captured_at'],
            'O timestamp captured_at deve ser idêntico ao issued_at para evitar divergências artificiais.'
        );
    }

    public function test_service_refreshes_stale_models_before_snapshot_capture(): void
    {
        $company = Company::create(['name' => 'Empresa Stale Test', 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Stale',
            'email' => 'stale.inst@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Nome Antigo Na Memória',
            'email' => 'stale.aluno@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Título Antigo Na Memória',
            'slug' => 'titulo-antigo-na-memoria',
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'duration_minutes' => 60,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        // Atualiza os registros diretamente no banco sem atualizar os objetos em memória $student e $course
        User::where('id', $student->id)->update(['name' => 'Nome Fresco Atualizado no Banco']);
        Course::where('id', $course->id)->update(['title' => 'Título Fresco Atualizado no Banco']);

        /** @var \App\Services\CertificateService $service */
        $service = app(\App\Services\CertificateService::class);

        // Passa as instâncias em memória desatualizadas ("stale") para o serviço
        $certificate = $service->getOrCreateCertificate($student, $course);

        // O snapshot deve capturar os valores frescos persistidos no banco
        $this->assertEquals('Nome Fresco Atualizado no Banco', $certificate->snapshot['student_name']);
        $this->assertEquals('Título Fresco Atualizado no Banco', $certificate->snapshot['course_title']);
    }
}
