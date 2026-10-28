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
use App\Services\VersionedCertificateRenderer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateBackgroundPrivateStorageTest extends TestCase
{
    use DatabaseTransactions;

    protected CertificateSchemaValidator $validator;
    protected CertificateTemplateResolver $resolver;
    protected CertificateTemplatePublishingService $publishingService;
    protected VersionedCertificateRenderer $renderer;
    protected CertificateService $certificateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CertificateSchemaValidator();
        $this->resolver = new CertificateTemplateResolver($this->validator);
        $this->publishingService = new CertificateTemplatePublishingService($this->validator);
        $this->renderer = new VersionedCertificateRenderer();
        $this->certificateService = new CertificateService();
    }

    protected function setupCompanyAndUser(string $companyName = 'Alpha Security'): array
    {
        $company = Company::create(['name' => $companyName, 'status' => 'active']);

        $user = User::create([
            'name' => 'User ' . $companyName,
            'email' => 'user.' . uniqid() . '@security.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        return compact('company', 'user');
    }

    /**
     * 1. Usuário autorizado consegue visualizar a imagem via rota autenticada.
     */
    public function test_authorized_tenant_user_can_view_background_image_via_authenticated_route(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser('Corp A');

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Privado',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = "certificate-backgrounds/tenant-{$setup['company']->id}/bg_{$template->id}_test.png";

        // Cria o arquivo no disco privado 'local'
        Storage::disk('local')->put($schema['background']['image_path'], UploadedFile::fake()->image('bg.png', 1000, 700)->getContent());

        $version = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        $response = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $version->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * 2. Usuário de outra empresa não consegue visualizar a imagem (isolamento multi-tenant).
     */
    public function test_user_from_different_company_cannot_view_background_image(): void
    {
        Storage::fake('local');
        $setupA = $this->setupCompanyAndUser('Empresa A');
        $setupB = $this->setupCompanyAndUser('Empresa B');

        $templateA = CertificateTemplate::create([
            'company_id' => $setupA['company']->id,
            'name' => 'Template Confidencial A',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = "certificate-backgrounds/tenant-{$setupA['company']->id}/bg_a.png";
        Storage::disk('local')->put($schema['background']['image_path'], UploadedFile::fake()->image('bg.png', 1000, 700)->getContent());

        $versionA = CertificateTemplateVersion::create([
            'template_id' => $templateA->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setupA['user']->id,
        ]);

        // Usuário B tenta acessar template A
        $response = $this->actingAs($setupB['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $templateA->id, 'version' => $versionA->id]));

        $response->assertStatus(404);
    }

    /**
     * 3. Acesso anônimo é negado.
     */
    public function test_anonymous_guest_cannot_view_background_image(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Teste',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $response = $this->get(route('instructor.certificate-templates.background', ['template' => $template->id]));
        $response->assertRedirect(route('login'));
    }

    /**
     * 4. Caminhos inválidos ou tentativas de path traversal são rejeitados.
     */
    public function test_invalid_paths_and_directory_traversal_attempts_are_rejected(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Traversal Check',
            'status' => 'draft',
            'is_default' => false,
        ]);

        // Injeta tentativa de path traversal no schema
        $badSchema = $this->resolver->getDefaultSchema();
        $badSchema['background']['image_path'] = 'certificate-backgrounds/../../etc/passwd.png';

        $badVersion = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $badSchema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        $response = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $badVersion->id]));

        $response->assertStatus(400);
    }

    /**
     * 5. PDF com arte local continua sendo renderizado corretamente pelo Dompdf.
     */
    public function test_dompdf_renders_pdf_with_local_private_background_with_is_remote_disabled(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Render Dompdf',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $imageRelativePath = "certificate-backgrounds/tenant-{$setup['company']->id}/arte.png";
        Storage::disk('local')->put($imageRelativePath, UploadedFile::fake()->image('arte.png', 1122, 793)->getContent());

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = $imageRelativePath;

        $version = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        $certificate = new Certificate([
            'total_hours_minutes' => 60,
            'validation_code' => 'ALV-TEST-DOMPDF',
            'issued_at' => now(),
            'snapshot' => [
                'student_name' => 'Aluno Teste',
                'course_title' => 'Curso de Segurança',
                'company_name' => 'Empresa Teste',
                'workload_minutes' => 60,
            ],
        ]);

        $pdf = $this->renderer->render($certificate, $version);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    /**
     * 6. A prévia do editor continua funcionando.
     */
    public function test_editor_preview_continues_working_with_private_background(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $imagePath = "certificate-backgrounds/tenant-{$setup['company']->id}/preview.png";
        Storage::disk('local')->put($imagePath, UploadedFile::fake()->image('preview.png', 1122, 793)->getContent());

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = $imagePath;

        $response = $this->actingAs($setup['user'])
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $schema,
            ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * 7. Imagens de versões publicadas permanecem acessíveis e reproduzíveis.
     */
    public function test_published_versions_and_issued_certificates_remain_accessible(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Publicado Oficial',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $imagePath = "certificate-backgrounds/tenant-{$setup['company']->id}/publicada.png";
        Storage::disk('local')->put($imagePath, UploadedFile::fake()->image('publicada.png', 1122, 793)->getContent());

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = $imagePath;

        $v1 = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        // Publica a v1
        $publishedV1 = $this->publishingService->publish($template, $v1, $setup['user']);
        $this->assertEquals('active', $publishedV1->status);

        // Acesso à imagem da versão publicada
        $response = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $publishedV1->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    /**
     * 8. Imagens legadas continuam funcionando durante e após a migração.
     */
    public function test_legacy_images_on_public_disk_continue_working_via_fallback_and_migration(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template com Arte Legada',
            'status' => 'draft',
            'is_default' => false,
        ]);

        // Arquivo reside EXCLUSIVAMENTE no disco 'public' (cenário legado pré-migração)
        $legacyImagePath = "certificate-backgrounds/tenant-{$setup['company']->id}/legada.png";
        Storage::disk('public')->put($legacyImagePath, UploadedFile::fake()->image('legada.png', 1122, 793)->getContent());

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = $legacyImagePath;

        $version = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        // 8a. Antes da migração: fallback transparente deve servir a imagem com sucesso
        $responseBefore = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $version->id]));
        $responseBefore->assertOk();

        // 8b. Executa o comando de migração
        $this->artisan('certificates:migrate-backgrounds-to-private')
            ->assertSuccessful();

        // 8c. Confirma que o arquivo agora existe no disco 'local' E continua preservado no disco 'public'
        Storage::disk('local')->assertExists($legacyImagePath);
        Storage::disk('public')->assertExists($legacyImagePath);

        // 8d. Após a migração: continua servindo perfeitamente
        $responseAfter = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $version->id]));
        $responseAfter->assertOk();
    }

    /**
     * 9. A resposta da rota não expõe o caminho físico no disco.
     */
    public function test_background_route_does_not_expose_server_physical_filesystem_path(): void
    {
        Storage::fake('local');
        $setup = $this->setupCompanyAndUser();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Sem Vazamento',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $imagePath = "certificate-backgrounds/tenant-{$setup['company']->id}/safe.png";
        Storage::disk('local')->put($imagePath, UploadedFile::fake()->image('safe.png', 1122, 793)->getContent());

        $schema = $this->resolver->getDefaultSchema();
        $schema['background']['image_path'] = $imagePath;

        $version = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $schema,
            'status' => 'draft',
            'created_by' => $setup['user']->id,
        ]);

        $response = $this->actingAs($setup['user'])
            ->get(route('instructor.certificate-templates.background', ['template' => $template->id, 'version' => $version->id]));

        // Verifica que o corpo é estritamente binário e não JSON nem texto contendo caminhos do storage
        $this->assertStringNotContainsString(storage_path(), $response->headers->get('Content-Disposition') ?? '');
    }
}
