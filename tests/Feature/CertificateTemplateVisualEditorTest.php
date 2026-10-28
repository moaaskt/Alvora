<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\CertificateTemplateEditor;
use App\Livewire\Company\CertificateTemplateList;
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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateTemplateVisualEditorTest extends TestCase
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
     * Helper to setup company, users and an initial corporate draft template.
     *
     * @return array{company: Company, admin: User, instructor: User, student: User, template: CertificateTemplate, draft: CertificateTemplateVersion}
     */
    protected function setupTenantWithDraftTemplate(string $companyName = 'Acme Editor Corp'): array
    {
        $company = Company::create([
            'name' => $companyName,
            'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Admin ' . $companyName,
            'email' => 'admin.' . uniqid() . '@editor.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $instructor = User::create([
            'name' => 'Instructor ' . $companyName,
            'email' => 'instructor.' . uniqid() . '@editor.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Student ' . $companyName,
            'email' => 'student.' . uniqid() . '@editor.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $template = CertificateTemplate::create([
            'company_id' => $company->id,
            'name' => 'Modelo Visual ' . $companyName,
            'description' => 'Template para testes do editor visual',
            'status' => 'draft',
            'is_default' => false,
        ]);

        $initialSchema = $this->resolver->getDefaultSchema();
        $initialSchema['name'] = $template->name;

        $draft = CertificateTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'schema_json' => $initialSchema,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        return compact('company', 'admin', 'instructor', 'student', 'template', 'draft');
    }

    // -------------------------------------------------------------------------
    // 1. Autorização, Rotas e Isolamento Multi-Tenant
    // -------------------------------------------------------------------------

    public function test_guest_and_student_cannot_access_visual_editor_route(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        $guestResponse = $this->get(route('instructor.certificate-templates.edit', $ctx['template']->id));
        $guestResponse->assertRedirect(route('login'));

        $studentResponse = $this->actingAs($ctx['student'])
            ->get(route('instructor.certificate-templates.edit', $ctx['template']->id));
        $studentResponse->assertStatus(403);
    }

    public function test_company_admin_and_instructor_can_open_visual_editor_and_see_a4_canvas(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        $responseAdmin = $this->actingAs($ctx['admin'])
            ->get(route('instructor.certificate-templates.edit', $ctx['template']->id));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee($ctx['template']->name);
        $responseAdmin->assertSee('certificate-a4-canvas');
        $responseAdmin->assertSee('Painel de Propriedades');

        $responseInstructor = $this->actingAs($ctx['instructor'])
            ->get(route('instructor.certificate-templates.edit', $ctx['template']->id));
        $responseInstructor->assertOk();
        $responseInstructor->assertSee('certificate-a4-canvas');

        // Verifica que a listagem exibe o link real para o Editor Visual
        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateList::class)
            ->assertSee(route('instructor.certificate-templates.edit', $ctx['template']->id))
            ->assertSee('Abrir Editor Visual');
    }

    public function test_cross_tenant_cannot_open_or_mutate_another_company_template_in_editor(): void
    {
        $tenantA = $this->setupTenantWithDraftTemplate('Empresa Alfa');
        $tenantB = $this->setupTenantWithDraftTemplate('Empresa Beta');

        // Usuário da Empresa Beta tenta acessar via HTTP a rota de edição do template da Empresa Alfa
        $response = $this->actingAs($tenantB['admin'])
            ->get(route('instructor.certificate-templates.edit', $tenantA['template']->id));
        $response->assertStatus(404);

        // Tenta montar o componente Livewire diretamente com o template de outro tenant
        $this->actingAs($tenantB['admin']);
        $responseLivewire = $this->get(route('instructor.certificate-templates.edit', [
            'template' => $tenantA['template']->id,
            'version' => $tenantA['draft']->id,
        ]));
        $responseLivewire->assertStatus(404);
    }

    public function test_tenant_cannot_open_global_alvora_default_template_in_editor(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();
        $globalVersion = $this->resolver->ensureDefaultTemplate();

        $response = $this->actingAs($ctx['admin'])
            ->get(route('instructor.certificate-templates.edit', $globalVersion->template_id));

        $response->assertStatus(404);
    }

    public function test_client_tampering_with_template_id_or_version_id_is_blocked(): void
    {
        $tenantA = $this->setupTenantWithDraftTemplate('Tenant Honest');
        $tenantB = $this->setupTenantWithDraftTemplate('Tenant Target');

        // 1. Tentar chamar selectVersion apontando para o ID de versão de outro tenant deve ser bloqueado com 404
        Livewire::actingAs($tenantA['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $tenantA['template']])
            ->call('selectVersion', $tenantB['draft']->id)
            ->assertStatus(404);

        // 2. As propriedades críticas de contexto ($templateId, $versionId, $versionStatus) são #[Locked] no Livewire 4,
        // impedindo mutação direta pelo payload do cliente
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($tenantA['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $tenantA['template']])
            ->set('templateId', $tenantB['template']->id);
    }

    // -------------------------------------------------------------------------
    // 2. Seleção, Posicionamento, Adição de Elementos e Variáveis no Canvas A4
    // -------------------------------------------------------------------------

    public function test_can_select_and_reposition_elements_within_a4_canvas_bounds(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->call('selectElement', 'student_name')
            ->assertSet('selectedElementId', 'student_name')
            ->call('updateElementPosition', 'student_name', 140, 220, 840, 50)
            ->call('saveDraft')
            ->assertSet('validationError', null);

        $savedSchema = $ctx['draft']->fresh()->schema_json;
        $studentElem = collect($savedSchema['elements'])->firstWhere('id', 'student_name');

        $this->assertNotNull($studentElem);
        $this->assertEquals(140, $studentElem['x']);
        $this->assertEquals(220, $studentElem['y']);
        $this->assertEquals(840, $studentElem['width']);
        $this->assertEquals(50, $studentElem['height']);
    }

    public function test_repositioning_clamps_coordinates_to_a4_dimensions(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->call('updateElementPosition', 'brand_logo', 5000, -100, 300, 40)
            ->call('saveDraft')
            ->assertSet('validationError', null);

        $savedSchema = $ctx['draft']->fresh()->schema_json;
        $logoElem = collect($savedSchema['elements'])->firstWhere('id', 'brand_logo');

        // 1122 - 300 = 822 max X; min Y = 0
        $this->assertEquals(822, $logoElem['x']);
        $this->assertEquals(0, $logoElem['y']);
    }

    public function test_can_add_supported_elements_and_insert_domain_placeholders(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        $component = Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->call('addElement', 'signature_group')
            ->assertSet('validationError', null);

        $newElementId = $component->get('selectedElementId');
        $this->assertStringStartsWith('signature_group_', (string) $newElementId);

        // Insere variável suportada pelo domínio no elemento recém-criado
        $component->call('insertPlaceholder', '{{issued_date}}')
            ->call('saveDraft')
            ->assertSet('validationError', null);

        $savedSchema = $ctx['draft']->fresh()->schema_json;
        $addedElem = collect($savedSchema['elements'])->firstWhere('id', $newElementId);

        $this->assertNotNull($addedElem);
        $this->assertEquals('signature_group', $addedElem['type']);
        $this->assertStringContainsString('{{issued_date}}', $addedElem['content']);
    }

    public function test_rejects_adding_unsupported_element_types_or_unauthorized_placeholders(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->call('addElement', 'arbitrary_iframe_widget')
            ->assertSee('Tipo de elemento não suportado pelo schema')
            ->call('selectElement', 'student_name')
            ->call('insertPlaceholder', '{{secret_env_key}}')
            ->assertSee('Marcador dinâmico não autorizado');
    }

    // -------------------------------------------------------------------------
    // 3. Painel de Propriedades, Validação de Schema e Salvamento de Rascunho
    // -------------------------------------------------------------------------

    public function test_can_edit_element_and_page_properties_and_save_valid_draft(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->set('schema.background.color', '#0f172a')
            ->set('schema.border.color', '#10b981')
            ->set('schema.border.width', 4)
            ->set('schema.elements.0.content', 'CERTIFICADO CORPORATIVO ALVORA')
            ->set('schema.elements.0.style.font_family', 'Times-Roman')
            ->set('schema.elements.0.style.font_size', '26px')
            ->set('schema.elements.0.style.color', '#38bdf8')
            ->call('saveDraft')
            ->assertSet('validationError', null)
            ->assertSee('Rascunho (v1) validado e salvo com sucesso.');

        $persisted = $ctx['draft']->fresh()->schema_json;
        $this->assertEquals('#0f172a', $persisted['background']['color']);
        $this->assertEquals('#10b981', $persisted['border']['color']);
        $this->assertEquals(4, $persisted['border']['width']);
        $this->assertEquals('CERTIFICADO CORPORATIVO ALVORA', $persisted['elements'][0]['content']);
        $this->assertEquals('Times-Roman', $persisted['elements'][0]['style']['font_family']);
    }

    public function test_property_panel_rejects_invalid_schema_properties_and_html_injection_on_save(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        // 1. Tentativa de injetar HTML em content
        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->set('schema.elements.0.content', '<script>alert(1)</script>')
            ->call('saveDraft')
            ->assertSee('HTML arbitrário ou tags embutidas não são permitidos');

        // 2. Tentativa de usar fonte fora da whitelist
        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->set('schema.elements.0.style.font_family', 'Papyrus')
            ->call('saveDraft')
            ->assertSee('Fonte não autorizada');

        // 3. Tentativa de injetar propriedade de estilo arbitrária não reconhecida pelo renderer
        $tamperedSchema = $ctx['draft']->schema_json;
        $tamperedSchema['elements'][0]['style']['position_hack'] = 'fixed';

        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->set('schema', $tamperedSchema)
            ->call('saveDraft')
            ->assertSee('Propriedade de estilo não autorizada');

        // Garante que o rascunho no banco permaneceu intacto com o valor original
        $this->assertEquals('ALVORA', $ctx['draft']->fresh()->schema_json['elements'][0]['content']);
    }

    // -------------------------------------------------------------------------
    // 4. Prévia em PDF (Sem Persistência e Sem Mutação de Versões Publicadas)
    // -------------------------------------------------------------------------

    public function test_preview_endpoint_renders_edited_schema_without_persisting_certificates_or_mutating_versions(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        // Publica a v1 primeiro
        $publishedV1 = $this->publishingService->publish($ctx['template'], $ctx['draft'], $ctx['admin']);
        $originalColor = $publishedV1->schema_json['background']['color'];

        // Monta um schema modificado apenas na memória (simulando o estado do editor antes de salvar/publicar)
        $unsavedPreviewSchema = $publishedV1->schema_json;
        $unsavedPreviewSchema['background']['color'] = '#312e81';
        $unsavedPreviewSchema['elements'][2]['content'] = 'Certificado de Excelência em Engenharia';

        $certsBefore = Certificate::withoutGlobalScopes()->count();
        $versionsBefore = CertificateTemplateVersion::count();

        $response = $this->actingAs($ctx['admin'])
            ->postJson(route('instructor.certificate-templates.preview'), [
                'schema' => $unsavedPreviewSchema,
            ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());

        // Nenhum certificado real foi persistido e nenhuma versão foi criada ou alterada
        $this->assertEquals($certsBefore, Certificate::withoutGlobalScopes()->count());
        $this->assertEquals($versionsBefore, CertificateTemplateVersion::count());
        $this->assertEquals($originalColor, $publishedV1->fresh()->schema_json['background']['color']);
    }

    // -------------------------------------------------------------------------
    // 5. Publicação, Imutabilidade de Versões Publicadas e Preservação Histórica
    // -------------------------------------------------------------------------

    public function test_publishing_from_editor_activates_version_and_locks_it_against_further_mutations(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        // Edita e publica a v1 pelo editor
        $component = Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->set('schema.background.color', '#1e1b4b')
            ->call('publishVersion')
            ->assertSet('validationError', null)
            ->assertSet('versionStatus', 'active');

        $v1 = $ctx['draft']->fresh();
        $this->assertEquals('active', $v1->status);
        $this->assertEquals('#1e1b4b', $v1->schema_json['background']['color']);
        $this->assertEquals('active', $ctx['template']->fresh()->status);
        $this->assertTrue((bool) $ctx['template']->fresh()->is_default);

        // Tentar chamar saveDraft numa versão já ativa deve ser rejeitado
        $component->set('schema.background.color', '#000000')
            ->call('saveDraft')
            ->assertSee('Apenas versões em rascunho');

        $this->assertEquals('#1e1b4b', $v1->fresh()->schema_json['background']['color']);
    }

    public function test_can_create_new_draft_v2_from_published_v1_and_preserve_issued_certificates_snapshot(): void
    {
        $ctx = $this->setupTenantWithDraftTemplate();

        // 1. Publica a v1 pelo serviço
        $v1 = $this->publishingService->publish($ctx['template'], $ctx['draft'], $ctx['admin']);

        // 2. Cria um curso concluído pelo aluno e emite certificado sob a v1
        $course = Course::create([
            'company_id' => $ctx['company']->id,
            'instructor_id' => $ctx['instructor']->id,
            'title' => 'Arquitetura Cloud Segura',
            'slug' => 'arquitetura-cloud-' . uniqid(),
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $ctx['company']->id,
            'course_id' => $course->id,
            'title' => 'Módulo Único',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $ctx['company']->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula Final',
            'duration_minutes' => 90,
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $ctx['company']->id,
            'user_id' => $ctx['student']->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 100,
        ]);

        LessonProgress::create([
            'company_id' => $ctx['company']->id,
            'user_id' => $ctx['student']->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $issuedCertV1 = $this->certificateService->getOrCreateCertificate($ctx['student'], $course);
        $originalSnapshot = $issuedCertV1->snapshot;
        $this->assertEquals($v1->id, $issuedCertV1->template_version_id);

        // 3. No editor visual, cria uma nova versão de rascunho (v2) a partir da v1, altera o fundo e publica a v2
        Livewire::actingAs($ctx['admin'])
            ->test(CertificateTemplateEditor::class, ['template' => $ctx['template']])
            ->assertSet('versionNumber', 1)
            ->assertSet('versionStatus', 'active')
            ->call('createNewDraftFromCurrent')
            ->assertSet('versionNumber', 2)
            ->assertSet('versionStatus', 'draft')
            ->set('schema.background.color', '#3b0764')
            ->call('publishVersion')
            ->assertSet('versionStatus', 'active')
            ->assertSet('validationError', null);

        // 4. Verifica que a v1 foi arquivada sem alterar seu schema_json e que o certificado emitido continua intacto
        $v1->refresh();
        $issuedCertV1->refresh();

        $this->assertEquals('archived', $v1->status);
        $this->assertEquals('#0b0f19', $v1->schema_json['background']['color']);
        $this->assertEquals($v1->id, $issuedCertV1->template_version_id);
        $this->assertEquals($originalSnapshot, $issuedCertV1->snapshot);
    }
}
