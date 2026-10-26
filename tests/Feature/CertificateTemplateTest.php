<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use App\Services\CertificateSchemaValidator;
use App\Services\CertificateService;
use App\Services\CertificateTemplateResolver;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificateTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected CertificateSchemaValidator $validator;
    protected CertificateTemplateResolver $resolver;
    protected CertificateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CertificateSchemaValidator();
        $this->resolver = new CertificateTemplateResolver($this->validator);
        $this->service = new CertificateService();
    }

    /**
     * Helper to prepare eligible student, instructor, company and course.
     *
     * @return array{company: Company, student: User, instructor: User, course: Course}
     */
    protected function setupEligibleCourseAndStudent(string $companyName = 'TechCorp'): array
    {
        $company = Company::create(['name' => $companyName, 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Prof. Carlos',
            'email' => 'carlos.' . uniqid() . '@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Matheus',
            'email' => 'matheus.' . uniqid() . '@techcorp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Arquitetura Corporativa de Software',
            'slug' => 'arquitetura-corporativa-' . uniqid(),
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Padrões',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Lição 1: Monolito vs Microsserviços',
            'duration_minutes' => 120,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 100,
        ]);

        LessonProgress::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        return [
            'company' => $company,
            'student' => $student,
            'instructor' => $instructor,
            'course' => $course,
        ];
    }

    /**
     * Helper to count pages of generated PDF output.
     */
    protected function countPdfPages(string $pdfOutput): int
    {
        preg_match_all("/\/Type\s*\/Page[^s]/", $pdfOutput, $matches);
        $count = count($matches[0]);
        if ($count > 0) {
            return $count;
        }

        if (preg_match('/\/Count\s+(\d+)/', $pdfOutput, $countMatches)) {
            return (int) $countMatches[1];
        }

        return 1;
    }

    // -------------------------------------------------------------------------
    // 1. Schema Validator Tests
    // -------------------------------------------------------------------------

    public function test_schema_validator_accepts_valid_default_schema(): void
    {
        $defaultSchema = $this->resolver->getDefaultSchema();
        $validated = $this->validator->validate($defaultSchema);

        $this->assertIsArray($validated);
        $this->assertEquals('Template Padrão Institucional ALVORA', $validated['name']);
        $this->assertNotEmpty($validated['elements']);
    }

    public function test_schema_validator_rejects_unknown_root_properties(): void
    {
        $schema = $this->resolver->getDefaultSchema();
        $schema['unexpected_backdoor'] = 'malicious_payload';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Propriedade de raiz desconhecida ou não autorizada no schema: 'unexpected_backdoor'");

        $this->validator->validate($schema);
    }

    public function test_schema_validator_rejects_unauthorized_dynamic_placeholders(): void
    {
        $schema = $this->resolver->getDefaultSchema();
        $schema['elements'][0]['content'] = 'Tentativa de injeção: {{user_password_hash}}';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Marcador dinâmico não autorizado no elemento 'brand_logo': '{{user_password_hash}}'");

        $this->validator->validate($schema);
    }

    public function test_schema_validator_rejects_raw_html_in_content(): void
    {
        $schema = $this->resolver->getDefaultSchema();
        $schema['elements'][0]['content'] = 'Certificado <script>alert("xss")</script>';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("HTML arbitrário ou tags embutidas não são permitidos no conteúdo do elemento 'brand_logo'");

        $this->validator->validate($schema);
    }

    public function test_schema_validator_rejects_fonts_outside_whitelist(): void
    {
        $schema = $this->resolver->getDefaultSchema();
        $schema['elements'][0]['style']['font_family'] = 'Comic Sans MS';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Fonte não autorizada no elemento 'brand_logo': 'Comic Sans MS'");

        $this->validator->validate($schema);
    }

    public function test_schema_validator_rejects_coordinates_out_of_bounds(): void
    {
        $schema = $this->resolver->getDefaultSchema();
        // Width + X > 1122
        $schema['elements'][0]['x'] = 1000;
        $schema['elements'][0]['width'] = 200;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Largura transborda os limites da folha A4 no elemento 'brand_logo'");

        $this->validator->validate($schema);
    }

    // -------------------------------------------------------------------------
    // 2. Immutability & Lifecycle Tests
    // -------------------------------------------------------------------------

    public function test_template_version_schema_json_is_strictly_immutable_after_publication(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $template = CertificateTemplate::create([
            'company_id' => $company->id,
            'name' => 'Template Alfa Custom',
            'status' => 'active',
        ]);

        $schema = $this->resolver->getDefaultSchema();

        $version = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'active',
        ]);

        // Tentar alterar o schema_json após ativação deve falhar
        $alteredSchema = $schema;
        $alteredSchema['background']['color'] = '#ff0000';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Versões de templates publicadas ou ativas são imutáveis');

        $version->update(['schema_json' => $alteredSchema]);
    }

    public function test_cannot_delete_template_version_referenced_by_issued_certificates(): void
    {
        $ctx = $this->setupEligibleCourseAndStudent('Banco Alpha');
        $certificate = $this->service->getOrCreateCertificate($ctx['student'], $ctx['course']);

        $versionId = $certificate->template_version_id;
        $this->assertNotNull($versionId, 'O certificado recém-emitido deve possuir uma versão de template associada.');

        $version = CertificateTemplateVersion::findOrFail($versionId);

        // Tentar excluir a versão deve ser impedido pelo hook do model
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma versão de template vinculada a certificados emitidos');

        $version->delete();
    }

    public function test_cannot_delete_template_whose_versions_are_referenced_by_certificates(): void
    {
        $ctx = $this->setupEligibleCourseAndStudent('Banco Beta');
        $certificate = $this->service->getOrCreateCertificate($ctx['student'], $ctx['course']);

        $version = CertificateTemplateVersion::findOrFail($certificate->template_version_id);
        $template = CertificateTemplate::withoutGlobalScopes()->findOrFail($version->template_id);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir um template que possui versões vinculadas a certificados emitidos.');

        $template->delete();
    }

    // -------------------------------------------------------------------------
    // 3. Multi-Tenancy & Authorization Tests
    // -------------------------------------------------------------------------

    public function test_hierarchical_template_resolution_company_vs_global_default(): void
    {
        $companyA = Company::create(['name' => 'Empresa Sem Customização', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Empresa Com Customização', 'status' => 'active']);

        // Empresa A: não possui template -> resolve o global default ALVORA
        $resolvedA = $this->resolver->resolveActiveTemplate($companyA->id);
        $this->assertNotNull($resolvedA);
        $this->assertNull($resolvedA->template->company_id, 'Deve resolver o template global ALVORA com company_id null.');

        // Empresa B: cria seu próprio template corporativo ativo
        $templateB = CertificateTemplate::create([
            'company_id' => $companyB->id,
            'name' => 'Template Exclusivo Empresa B',
            'status' => 'active',
        ]);

        $customSchema = $this->resolver->getDefaultSchema();
        $customSchema['name'] = 'Custom Empresa B';

        $versionB = CertificateTemplateVersion::create([
            'template_id' => $templateB->id,
            'version' => 1,
            'schema_json' => $customSchema,
            'status' => 'active',
        ]);

        $resolvedB = $this->resolver->resolveActiveTemplate($companyB->id);
        $this->assertNotNull($resolvedB);
        $this->assertEquals($versionB->id, $resolvedB->id);
        $this->assertEquals($companyB->id, $resolvedB->template->company_id);

        // Se desativar o template da Empresa B, volta a resolver o global default
        $templateB->update(['status' => 'archived']);
        $resolvedBAfterDeactivation = $this->resolver->resolveActiveTemplate($companyB->id);
        $this->assertNull($resolvedBAfterDeactivation->template->company_id, 'Após desativação, volta para o global default.');
    }

    public function test_tenants_cannot_mutate_or_delete_global_templates_via_tenant_scope(): void
    {
        // Garante que o template global default existe
        $defaultVersion = $this->resolver->ensureDefaultTemplate();
        $globalTemplateId = $defaultVersion->template_id;

        $company = Company::create(['name' => 'Empresa Curiosa', 'status' => 'active']);
        $companyAdmin = User::create([
            'name' => 'Admin Curioso',
            'email' => 'admin.' . uniqid() . '@curiosa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        // Atua como admin da empresa cliente
        $this->actingAs($companyAdmin);

        // O TenantScope deve impedir que a consulta encontre o template global
        $found = CertificateTemplate::find($globalTemplateId);
        $this->assertNull($found, 'O TenantScope deve ocultar templates com company_id = null das consultas corporativas.');

        // Tentar atualizar ou excluir deve afetar 0 registros
        $affected = CertificateTemplate::where('id', $globalTemplateId)->update(['name' => 'Hacked Name']);
        $this->assertEquals(0, $affected, 'Nenhuma linha global pode ser atualizada sob TenantScope corporativo.');

        $deleted = CertificateTemplate::where('id', $globalTemplateId)->delete();
        $this->assertEquals(0, $deleted, 'Nenhuma linha global pode ser excluída sob TenantScope corporativo.');

        // Verifica fora do scope que o template global continua íntegro
        $persisted = CertificateTemplate::withoutGlobalScopes()->find($globalTemplateId);
        $this->assertNotNull($persisted);
        $this->assertEquals('Template Padrão Institucional ALVORA', $persisted->name);
    }

    // -------------------------------------------------------------------------
    // 4. PDF Rendering, Dual Dispatch & Concurrency Tests
    // -------------------------------------------------------------------------

    public function test_versioned_renderer_outputs_strictly_single_page_pdf(): void
    {
        $ctx = $this->setupEligibleCourseAndStudent('Faculdade Delta');
        $certificate = $this->service->getOrCreateCertificate($ctx['student'], $ctx['course']);

        $pdf = $this->service->generatePdf($certificate);
        $pdfOutput = $pdf->output();

        $this->assertNotEmpty($pdfOutput);
        $pageCount = $this->countPdfPages($pdfOutput);
        $this->assertEquals(1, $pageCount, 'O certificado versionado deve gerar estritamente 1 página no Dompdf.');
    }

    public function test_dual_dispatch_legacy_vs_versioned(): void
    {
        $ctx = $this->setupEligibleCourseAndStudent('Empresa Legado');

        // 1. Certificado legado com template_version_id = null
        $legacyCertificate = Certificate::create([
            'company_id' => $ctx['company']->id,
            'user_id' => $ctx['student']->id,
            'course_id' => $ctx['course']->id,
            'template_version_id' => null,
            'total_hours_minutes' => 120,
            'issued_at' => now(),
            'snapshot' => [
                'mode' => 'original',
                'student_name' => 'Aluno Antigo',
                'course_title' => 'Curso Legado',
                'company_name' => 'Empresa Legado',
                'workload_minutes' => 120,
            ],
        ]);

        $legacyPdf = $this->service->generatePdf($legacyCertificate);
        $this->assertNotEmpty($legacyPdf->output());
        $this->assertEquals(1, $this->countPdfPages($legacyPdf->output()));

        // 2. Certificado novo com template_version_id vinculado (aluno distinto para preservar unicidade)
        $studentNovo = User::create([
            'name' => 'Aluno Novo',
            'email' => 'aluno.novo.' . uniqid() . '@legado.com',
            'password' => bcrypt('password'),
            'company_id' => $ctx['company']->id,
            'role' => UserRole::STUDENT,
        ]);

        $version = $this->resolver->ensureDefaultTemplate();
        $versionedCertificate = Certificate::create([
            'company_id' => $ctx['company']->id,
            'user_id' => $studentNovo->id,
            'course_id' => $ctx['course']->id,
            'template_version_id' => $version->id,
            'total_hours_minutes' => 120,
            'issued_at' => now(),
            'snapshot' => [
                'mode' => 'original',
                'student_name' => 'Aluno Novo',
                'course_title' => 'Curso Novo',
                'company_name' => 'Empresa Nova',
                'workload_minutes' => 120,
            ],
        ]);

        $versionedPdf = $this->service->generatePdf($versionedCertificate);
        $this->assertNotEmpty($versionedPdf->output());
        $this->assertEquals(1, $this->countPdfPages($versionedPdf->output()));
    }

    public function test_publishing_v2_template_does_not_mutate_previously_issued_v1_certificates(): void
    {
        $ctx1 = $this->setupEligibleCourseAndStudent('Consultoria Sigma');

        // Emite certificado C1 com versão v1 do template default
        $cert1 = $this->service->getOrCreateCertificate($ctx1['student'], $ctx1['course']);
        $v1Id = $cert1->template_version_id;
        $this->assertNotNull($v1Id);

        // Cria versão v2 com novo layout no template default ALVORA
        $defaultTemplate = CertificateTemplate::withoutGlobalScopes()->whereNull('company_id')->where('is_default', true)->first();

        $v2Schema = $this->resolver->getDefaultSchema();
        $v2Schema['version'] = '2.0.0';
        $v2Schema['background']['color'] = '#1e293b'; // novo fundo slate

        $v2 = CertificateTemplateVersion::create([
            'template_id' => $defaultTemplate->id,
            'version' => 2,
            'schema_json' => $v2Schema,
            'status' => 'active',
        ]);

        // Configura novo aluno elegível para emitir após a publicação da v2
        $student2 = User::create([
            'name' => 'Aluno Rodrigo',
            'email' => 'rodrigo.' . uniqid() . '@sigma.com',
            'password' => bcrypt('password'),
            'company_id' => $ctx1['company']->id,
            'role' => UserRole::STUDENT,
        ]);

        Enrollment::create([
            'company_id' => $ctx1['company']->id,
            'user_id' => $student2->id,
            'course_id' => $ctx1['course']->id,
            'status' => 'active',
            'progress_percent' => 100,
        ]);

        $cert2 = $this->service->getOrCreateCertificate($student2, $ctx1['course']);

        // Verificações essenciais:
        // C1 mantém estritamente v1_id
        $cert1->refresh();
        $this->assertEquals($v1Id, $cert1->template_version_id, 'Certificado antigo C1 deve continuar estritamente vinculado à versão v1.');

        // C2 recebe v2_id
        $this->assertEquals($v2->id, $cert2->template_version_id, 'Novo certificado C2 deve ser emitido com a versão v2 ativa.');

        // A renderização de C1 utiliza os atributos de v1 (#0b0f19), e C2 de v2 (#1e293b)
        $this->assertEquals('#0b0f19', $cert1->templateVersion->schema_json['background']['color']);
        $this->assertEquals('#1e293b', $cert2->templateVersion->schema_json['background']['color']);
    }

    public function test_new_issuance_associates_active_template_version_and_preserves_idempotency(): void
    {
        $ctx = $this->setupEligibleCourseAndStudent('Inovação S/A');

        $certA = $this->service->getOrCreateCertificate($ctx['student'], $ctx['course']);
        $this->assertNotNull($certA->template_version_id);
        $this->assertInstanceOf(CertificateTemplateVersion::class, $certA->templateVersion);

        // Segunda chamada para os mesmos parâmetros retorna o mesmo certificado existente
        $certB = $this->service->getOrCreateCertificate($ctx['student'], $ctx['course']);
        $this->assertEquals($certA->id, $certB->id);
        $this->assertEquals($certA->template_version_id, $certB->template_version_id);
    }
}
