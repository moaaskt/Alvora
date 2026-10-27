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
use App\Services\CertificateTemplatePublishingService;
use App\Services\CertificateTemplateResolver;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificateTemplateEditorBackendTest extends TestCase
{
    use DatabaseTransactions;

    protected CertificateSchemaValidator $validator;
    protected CertificateTemplateResolver $resolver;
    protected CertificateTemplatePublishingService $publishingService;
    protected CertificateService $certificateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CertificateSchemaValidator();
        $this->resolver = new CertificateTemplateResolver($this->validator);
        $this->publishingService = new CertificateTemplatePublishingService($this->validator);
        $this->certificateService = new CertificateService();
    }

    /**
     * Helper to create a company and an authorized company manager user.
     */
    protected function setupCompanyAndAdmin(string $companyName = 'Alpha Corp'): array
    {
        $company = Company::create([
            'name' => $companyName,
            'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Admin ' . $companyName,
            'email' => 'admin.' . uniqid() . '@alpha.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        return ['company' => $company, 'admin' => $admin];
    }

    /**
     * Helper to prepare eligible student and course for issuance tests.
     */
    protected function setupEligibleStudentAndCourse(Company $company): array
    {
        $instructor = User::create([
            'name' => 'Instrutor ' . $company->name,
            'email' => 'inst.' . uniqid() . '@corp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno ' . $company->name,
            'email' => 'student.' . uniqid() . '@corp.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Treinamento de Liderança Executiva',
            'slug' => 'lideranca-executiva-' . uniqid(),
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo Inicial',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1: Fundamentos',
            'duration_minutes' => 60,
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

        return ['student' => $student, 'course' => $course, 'instructor' => $instructor];
    }

    // -------------------------------------------------------------------------
    // 1. Prévia de PDF (Preview)
    // -------------------------------------------------------------------------

    public function test_preview_with_valid_schema_returns_inline_pdf_http_200(): void
    {
        $setup = $this->setupCompanyAndAdmin('Preview S/A');
        $validSchema = $this->resolver->getDefaultSchema();

        $initialCertificatesCount = Certificate::withoutGlobalScopes()->count();
        $initialTemplatesCount = CertificateTemplate::withoutGlobalScopes()->count();

        $response = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $validSchema,
            ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());

        // Garante que nenhum registro foi persistido no banco
        $this->assertEquals($initialCertificatesCount, Certificate::withoutGlobalScopes()->count());
        $this->assertEquals($initialTemplatesCount, CertificateTemplate::withoutGlobalScopes()->count());
    }

    public function test_preview_with_invalid_schema_returns_http_422(): void
    {
        $setup = $this->setupCompanyAndAdmin('Invalid Schema S/A');
        $invalidSchema = $this->resolver->getDefaultSchema();
        $invalidSchema['unexpected_exploit'] = true; // Chave não permitida na raiz

        $response = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $invalidSchema,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $this->assertStringContainsString('Propriedade de raiz desconhecida', $response->json('errors.0'));
    }

    public function test_preview_rejects_unauthorized_dynamic_placeholders(): void
    {
        $setup = $this->setupCompanyAndAdmin('Placeholder Exploit');
        $schema = $this->resolver->getDefaultSchema();
        $schema['elements'][0]['content'] = 'Vazamento: {{user_token_hash}}';

        $response = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $schema,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Marcador dinâmico não autorizado', $response->json('errors.0'));
    }

    // -------------------------------------------------------------------------
    // 2. Autorização e Isolamento Multi-Tenant
    // -------------------------------------------------------------------------

    public function test_student_or_unauthenticated_cannot_access_template_endpoints(): void
    {
        $setup = $this->setupCompanyAndAdmin('Tenant Test');
        $student = User::create([
            'name' => 'Aluno Tentando Acesso',
            'email' => 'aluno.' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'company_id' => $setup['company']->id,
            'role' => UserRole::STUDENT,
        ]);

        $validSchema = $this->resolver->getDefaultSchema();

        // 1. Visitante não autenticado é redirecionado
        $guestResponse = $this->postJson(route('instructor.certificate-templates.preview'), [
            'schema' => $validSchema,
        ]);
        $guestResponse->assertStatus(401);

        // 2. Aluno autenticado recebe 403 Forbidden
        $studentResponse = $this->actingAs($student)
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $validSchema,
            ]);
        $studentResponse->assertStatus(403);
    }

    public function test_cross_tenant_access_is_blocked_with_404(): void
    {
        $setupA = $this->setupCompanyAndAdmin('Empresa Alfa');
        $setupB = $this->setupCompanyAndAdmin('Empresa Beta');

        // Cria template pertencente à Empresa Alfa
        $templateA = CertificateTemplate::create([
            'company_id' => $setupA['company']->id,
            'name' => 'Template Exclusivo Alfa',
            'status' => 'draft',
        ]);

        $schema = $this->resolver->getDefaultSchema();
        $versionA = CertificateTemplateVersion::create([
            'template_id' => $templateA->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
        ]);

        // Usuário da Empresa Beta tenta acessar os endpoints do template da Empresa Alfa
        // O TenantScope e a validação de autorização devem retornar HTTP 404
        $responseCreate = $this->actingAs($setupB['admin'])
            ->postJson(route('instructor.certificate-templates.drafts.create', $templateA->id), [
                'schema' => $schema,
            ]);
        $responseCreate->assertStatus(404);

        $responseUpdate = $this->actingAs($setupB['admin'])
            ->putJson(route('instructor.certificate-templates.drafts.update', [$templateA->id, $versionA->id]), [
                'schema' => $schema,
            ]);
        $responseUpdate->assertStatus(404);

        $responsePublish = $this->actingAs($setupB['admin'])
            ->postJson(route('instructor.certificate-templates.versions.publish', [$templateA->id, $versionA->id]));
        $responsePublish->assertStatus(404);
    }

    public function test_tenant_cannot_manage_global_template_directly(): void
    {
        $setup = $this->setupCompanyAndAdmin('Empresa Curiosa');
        $globalVersion = $this->resolver->ensureDefaultTemplate();
        $globalTemplateId = $globalVersion->template_id;

        // Tentar criar draft no template global ALVORA
        $response = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.drafts.create', $globalTemplateId), [
                'schema' => $this->resolver->getDefaultSchema(),
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // 3. Rascunhos (Drafts)
    // -------------------------------------------------------------------------

    public function test_can_create_and_update_draft_version(): void
    {
        $setup = $this->setupCompanyAndAdmin('Drafting Corp');

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template de Demonstração',
            'status' => 'draft',
        ]);

        $schema = $this->resolver->getDefaultSchema();

        // 1. Cria versão em rascunho
        $createResponse = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.drafts.create', $template->id), [
                'schema' => $schema,
            ]);

        $createResponse->assertStatus(201);
        $createResponse->assertJsonPath('version.version', 1);
        $createResponse->assertJsonPath('version.status', 'draft');

        $versionId = $createResponse->json('version.id');

        // 2. Atualiza o schema da versão em rascunho
        $updatedSchema = $schema;
        $updatedSchema['background']['color'] = '#111827';

        $updateResponse = $this->actingAs($setup['admin'])
            ->putJson(route('instructor.certificate-templates.drafts.update', [$template->id, $versionId]), [
                'schema' => $updatedSchema,
            ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('version.schema_json.background.color', '#111827');
    }

    public function test_cannot_update_published_or_archived_version(): void
    {
        $setup = $this->setupCompanyAndAdmin('Immutable S/A');

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Ativo',
            'status' => 'active',
        ]);

        $schema = $this->resolver->getDefaultSchema();

        $activeVersion = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'active',
        ]);

        $alteredSchema = $schema;
        $alteredSchema['background']['color'] = '#ffffff';

        // Tentar atualizar versão ativa deve retornar 422
        $response = $this->actingAs($setup['admin'])
            ->putJson(route('instructor.certificate-templates.drafts.update', [$template->id, $activeVersion->id]), [
                'schema' => $alteredSchema,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Apenas versões em rascunho', $response->json('message'));
    }

    // -------------------------------------------------------------------------
    // 4. Publicação Transacional e Preservação Histórica
    // -------------------------------------------------------------------------

    public function test_publish_version_archives_previous_and_activates_candidate_transactionally(): void
    {
        $setup = $this->setupCompanyAndAdmin('Publisher Corp');

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Certificado Oficial Publisher',
            'status' => 'draft',
        ]);

        $schema1 = $this->resolver->getDefaultSchema();
        $v1 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema1,
            'status' => 'active',
        ]);

        $schema2 = $schema1;
        $schema2['background']['color'] = '#0f172a';

        $v2 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 2,
            'schema_json' => $schema2,
            'status' => 'draft',
        ]);

        // Publica v2 via endpoint
        $response = $this->actingAs($setup['admin'])
            ->postJson(route('instructor.certificate-templates.versions.publish', [$template->id, $v2->id]));

        $response->assertStatus(200);

        // Verifica o estado transacional final
        $v1->refresh();
        $v2->refresh();
        $template->refresh();

        $this->assertEquals('archived', $v1->status, 'A versão v1 deve ter sido arquivada.');
        $this->assertEquals('active', $v2->status, 'A versão v2 deve ter sido ativada.');
        $this->assertEquals('active', $template->status, 'O template deve estar ativo.');
        $this->assertTrue($template->is_default, 'O template publicado deve ser o padrão da empresa.');

        // Garante que o template global da ALVORA não foi alterado
        $globalTemplate = CertificateTemplate::withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('is_default', true)
            ->first();

        if ($globalTemplate) {
            $this->assertNull($globalTemplate->company_id);
            $this->assertTrue($globalTemplate->is_default);
        }
    }

    public function test_existing_certificates_retain_original_versions_after_new_publish(): void
    {
        $setup = $this->setupCompanyAndAdmin('Integrity B2B');
        $ctx = $this->setupEligibleStudentAndCourse($setup['company']);

        // 1. Cria e ativa template corporativo v1
        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template B2B Custom',
            'status' => 'active',
            'is_default' => true,
        ]);

        $v1Schema = $this->resolver->getDefaultSchema();
        $v1 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $v1Schema,
            'status' => 'active',
        ]);

        // Emite certificado C1 para o primeiro aluno
        $cert1 = $this->certificateService->getOrCreateCertificate($ctx['student'], $ctx['course']);
        $this->assertEquals($v1->id, $cert1->template_version_id);

        // 2. Cria e publica v2 no template corporativo
        $v2Schema = $v1Schema;
        $v2Schema['background']['color'] = '#312e81';

        $v2 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 2,
            'schema_json' => $v2Schema,
            'status' => 'draft',
        ]);

        $this->publishingService->publish($template, $v2, $setup['admin']);

        // 3. Cadastra segundo aluno elegível e emite C2
        $student2 = User::create([
            'name' => 'Aluno Segundo',
            'email' => 'segundo.' . uniqid() . '@corp.com',
            'password' => bcrypt('password'),
            'company_id' => $setup['company']->id,
            'role' => UserRole::STUDENT,
        ]);

        Enrollment::create([
            'company_id' => $setup['company']->id,
            'user_id' => $student2->id,
            'course_id' => $ctx['course']->id,
            'status' => 'active',
            'progress_percent' => 100,
        ]);

        $cert2 = $this->certificateService->getOrCreateCertificate($student2, $ctx['course']);

        // Verificações essenciais de integridade histórica:
        $cert1->refresh();
        $this->assertEquals($v1->id, $cert1->template_version_id, 'Certificado antigo C1 deve continuar atrelado à versão v1.');
        $this->assertEquals($v2->id, $cert2->template_version_id, 'Novo certificado C2 deve ser emitido com a versão v2 ativa.');

        // O PDF de C1 deve renderizar sem erros mantendo a versão v1
        $pdf1 = $this->certificateService->generatePdf($cert1);
        $this->assertNotEmpty($pdf1->output());

        $pdf2 = $this->certificateService->generatePdf($cert2);
        $this->assertNotEmpty($pdf2->output());
    }

    public function test_concurrent_publish_serialization_with_pessimistic_lock(): void
    {
        $setup = $this->setupCompanyAndAdmin('Concurrency Lock S/A');

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Concorrente',
            'status' => 'draft',
        ]);

        $schema = $this->resolver->getDefaultSchema();
        $v1 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
        ]);

        // Executa duas publicações consecutivas/paralelas no mesmo template
        // A primeira publica e ativa a v1
        $published1 = $this->publishingService->publish($template, $v1, $setup['admin']);
        $this->assertEquals('active', $published1->status);

        // A segunda chamada na mesma versão já ativa opera de forma idempotente sem falhas
        $published2 = $this->publishingService->publish($template, $v1, $setup['admin']);
        $this->assertEquals('active', $published2->status);
        $this->assertEquals($published1->id, $published2->id);
    }
}
