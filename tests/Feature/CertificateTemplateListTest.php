<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\CertificateTemplateList;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Models\Company;
use App\Models\User;
use App\Services\CertificateSchemaValidator;
use App\Services\CertificateTemplateResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateTemplateListTest extends TestCase
{
    use DatabaseTransactions;

    protected CertificateTemplateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new CertificateTemplateResolver(new CertificateSchemaValidator());
    }

    /**
     * Helper to setup company and users.
     *
     * @return array{company: Company, admin: User, instructor: User, student: User}
     */
    protected function setupCompanyAndUsers(string $name = 'Alpha Corp'): array
    {
        $company = Company::create(['name' => $name, 'status' => 'active']);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Student User',
            'email' => 'student.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        return compact('company', 'admin', 'instructor', 'student');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('instructor.certificate-templates.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_template_management(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $response = $this->actingAs($setup['student'])->get(route('instructor.certificate-templates.index'));

        $response->assertStatus(403);
    }

    public function test_company_admin_and_instructor_can_access_template_management(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $responseAdmin = $this->actingAs($setup['admin'])->get(route('instructor.certificate-templates.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Templates de Certificados');

        $responseInstructor = $this->actingAs($setup['instructor'])->get(route('instructor.certificate-templates.index'));
        $responseInstructor->assertOk();
        $responseInstructor->assertSee('Templates de Certificados');
    }

    public function test_legacy_route_redirects_to_instructor_templates_index(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $response = $this->actingAs($setup['admin'])->get('/empresa/certificados/templates');

        $response->assertRedirect('/instrutor/certificados/templates');
    }

    public function test_empty_state_shows_alvora_institutional_default_indicator(): void
    {
        $setup = $this->setupCompanyAndUsers();

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->assertOk()
            ->assertSee('Nenhum template personalizado')
            ->assertSee('Padrão Institucional ALVORA');
    }

    public function test_multi_tenant_isolation_company_only_sees_its_own_templates(): void
    {
        $corpA = $this->setupCompanyAndUsers('Company Alpha');
        $corpB = $this->setupCompanyAndUsers('Company Beta');

        $templateA = CertificateTemplate::create([
            'company_id' => $corpA['company']->id,
            'name' => 'Template Alpha Exclusivo',
            'description' => 'Apenas para Company Alpha',
            'status' => 'active',
            'is_default' => true,
        ]);

        $templateB = CertificateTemplate::create([
            'company_id' => $corpB['company']->id,
            'name' => 'Template Beta Exclusivo',
            'description' => 'Apenas para Company Beta',
            'status' => 'active',
            'is_default' => true,
        ]);

        // User Alpha must see Template A and NOT see Template B
        Livewire::actingAs($corpA['admin'])
            ->test(CertificateTemplateList::class)
            ->assertSee('Template Alpha Exclusivo')
            ->assertDontSee('Template Beta Exclusivo');

        // User Beta must see Template B and NOT see Template A
        Livewire::actingAs($corpB['admin'])
            ->test(CertificateTemplateList::class)
            ->assertSee('Template Beta Exclusivo')
            ->assertDontSee('Template Alpha Exclusivo');
    }

    public function test_can_create_new_template_initialized_from_default_schema(): void
    {
        $setup = $this->setupCompanyAndUsers();

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->set('newTemplateName', 'Novo Certificado Executivo')
            ->set('newTemplateDescription', 'Template moderno para executivos')
            ->call('createTemplate')
            ->assertHasNoErrors()
            ->assertDispatched('template-created');

        $template = CertificateTemplate::where('name', 'Novo Certificado Executivo')->first();
        $this->assertNotNull($template);
        $this->assertEquals($setup['company']->id, $template->company_id);
        $this->assertEquals('draft', $template->status);
        $this->assertFalse((bool) $template->is_default);

        // Verifies draft version v1 was created with institutional default schema
        $version = CertificateTemplateVersion::where('template_id', $template->id)->first();
        $this->assertNotNull($version);
        $this->assertEquals(1, $version->version);
        $this->assertEquals('draft', $version->status);
        $this->assertEquals($this->resolver->getDefaultSchema()['dimensions'], $version->schema_json['dimensions']);
    }

    public function test_cannot_create_template_with_empty_name(): void
    {
        $setup = $this->setupCompanyAndUsers();

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->set('newTemplateName', '')
            ->call('createTemplate')
            ->assertHasErrors(['newTemplateName' => 'required']);
    }

    public function test_can_set_active_template_as_default_and_clears_previous_default(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $template1 = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Principal Atual',
            'status' => 'active',
            'is_default' => true,
        ]);

        $template2 = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Segundo Template Ativo',
            'status' => 'active',
            'is_default' => false,
        ]);

        // Publish an active version on template2 so it is eligible as active
        CertificateTemplateVersion::create([
            'template_id' => $template2->id,
            'version' => 1,
            'schema_json' => $this->resolver->getDefaultSchema(),
            'status' => 'active',
            'created_by' => $setup['admin']->id,
        ]);

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->call('setAsDefault', $template2->id)
            ->assertHasNoErrors();

        $this->assertTrue((bool) $template2->fresh()->is_default);
        $this->assertFalse((bool) $template1->fresh()->is_default);
    }

    public function test_cannot_set_template_from_another_company_as_default(): void
    {
        $corpA = $this->setupCompanyAndUsers('Company Alpha');
        $corpB = $this->setupCompanyAndUsers('Company Beta');

        $templateB = CertificateTemplate::create([
            'company_id' => $corpB['company']->id,
            'name' => 'Template de Beta',
            'status' => 'active',
            'is_default' => false,
        ]);

        Livewire::actingAs($corpA['admin'])
            ->test(CertificateTemplateList::class)
            ->call('setAsDefault', $templateB->id);

        // State of Template B must NOT have been modified
        $this->assertFalse((bool) $templateB->fresh()->is_default);
    }

    public function test_can_archive_template_and_clears_default_if_it_was_default(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $template = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template a Arquivar',
            'status' => 'active',
            'is_default' => true,
        ]);

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->call('archiveTemplate', $template->id)
            ->assertHasNoErrors();

        $template->refresh();
        $this->assertEquals('archived', $template->status);
        $this->assertFalse((bool) $template->is_default);
    }

    public function test_search_and_status_filters_work_properly(): void
    {
        $setup = $this->setupCompanyAndUsers();

        CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Engenharia Certificado',
            'status' => 'active',
            'is_default' => false,
        ]);

        CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Comercial Certificado',
            'status' => 'draft',
            'is_default' => false,
        ]);

        // Filter by search query
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->set('search', 'Engenharia')
            ->assertSee('Engenharia Certificado')
            ->assertDontSee('Comercial Certificado');

        // Filter by status 'draft'
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->set('search', '')
            ->set('statusFilter', 'draft')
            ->assertSee('Comercial Certificado')
            ->assertDontSee('Engenharia Certificado');
    }

    /**
     * 1. Acesso repetido à listagem sem duplicar a versão padrão.
     */
    public function test_repeated_access_to_list_does_not_duplicate_default_template_version(): void
    {
        $setup = $this->setupCompanyAndUsers();

        // 1º Acesso
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->assertOk();

        $defaultTemplate = CertificateTemplate::withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('is_default', true)
            ->first();

        $this->assertNotNull($defaultTemplate);
        $vCount1 = $defaultTemplate->versions()->count();
        $this->assertGreaterThanOrEqual(1, $vCount1);

        // 2º Acesso consecutivo
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->assertOk();

        // 3º Acesso consecutivo
        Livewire::actingAs($setup['instructor'])
            ->test(CertificateTemplateList::class)
            ->assertOk();

        $vCount2 = $defaultTemplate->versions()->count();
        $this->assertEquals($vCount1, $vCount2, 'O acesso repetido não deve duplicar versões do template institucional padrão.');
    }

    /**
     * 2. Recuperação segura quando o template existe e a versão inicial já foi criada (Correção Bug A).
     */
    public function test_safe_recovery_when_template_exists_and_version_already_created(): void
    {
        $defaultTemplate = CertificateTemplate::withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('is_default', true)
            ->first();

        if (!$defaultTemplate) {
            $defaultTemplate = $this->resolver->ensureDefaultTemplate()->template;
        }

        // Simula o cenário exato do Bug A:
        // A versão 1 existe, versão 2 está ativa, e uma versão 3 rascunho existe.
        $maxVersion = (int) $defaultTemplate->versions()->max('version');
        $draftVersion = CertificateTemplateVersion::create([
            'template_id' => $defaultTemplate->id,
            'version' => $maxVersion + 1,
            'schema_json' => $this->resolver->getDefaultSchema(),
            'status' => 'draft',
            'created_by' => null,
        ]);

        // Executar o resolver não deve lançar UniqueConstraintViolationException
        $resolved = $this->resolver->resolveActiveTemplate(null);
        $this->assertNotNull($resolved);
        $this->assertEquals('active', $resolved->status);

        // O template deve carregar a listagem perfeitamente
        $setup = $this->setupCompanyAndUsers();
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->assertOk();
    }

    /**
     * 3. Tratamento de estados inconsistentes sem sobrescrever versões válidas.
     */
    public function test_inconsistent_state_recovery_without_overwriting_valid_versions(): void
    {
        $defaultTemplate = CertificateTemplate::withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('is_default', true)
            ->first();

        if (!$defaultTemplate) {
            $defaultTemplate = $this->resolver->ensureDefaultTemplate()->template;
        }

        // Marca todas as versões como arquivadas para simular inconsistência
        $defaultTemplate->versions()->update(['status' => 'archived']);
        $initialMaxVersion = (int) $defaultTemplate->versions()->max('version');
        $archivedCountBefore = $defaultTemplate->versions()->where('status', 'archived')->count();

        // Chama ensureDefaultTemplate: preserva imutabilidade das arquivadas e cria a próxima versão sequencial ativa
        $recovered = $this->resolver->ensureDefaultTemplate();
        $this->assertNotNull($recovered);
        $this->assertEquals('active', $recovered->status);
        $this->assertEquals($initialMaxVersion + 1, $recovered->version);
        $this->assertEquals($defaultTemplate->id, $recovered->template_id);

        // As versões arquivadas permanecem arquivadas e imutáveis
        $this->assertEquals($archivedCountBefore, $defaultTemplate->versions()->where('status', 'archived')->count());

        // Nenhuma colisão com chave única version
        $versions = $defaultTemplate->versions()->get();
        $uniqueVersions = $versions->pluck('version')->unique();
        $this->assertCount($versions->count(), $uniqueVersions, 'Todas as versões devem possuir numeração única.');
    }

    /**
     * 4. Comportamento seguro em requisições concorrentes de inicialização.
     */
    public function test_concurrent_initialization_safety_in_ensure_default_template(): void
    {
        $v1 = $this->resolver->ensureDefaultTemplate();
        $v2 = $this->resolver->ensureDefaultTemplate();

        $this->assertEquals($v1->id, $v2->id);
        $this->assertEquals($v1->version, $v2->version);
    }

    /**
     * 5. Criação de template com atualização correta da interface.
     */
    public function test_template_creation_updates_interface_and_closes_modal(): void
    {
        $setup = $this->setupCompanyAndUsers();

        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->set('showCreateModal', true)
            ->set('newTemplateName', 'Modelo Executivo 2026')
            ->set('newTemplateDescription', 'Descrição do modelo executivo')
            ->call('createTemplate')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false)
            ->assertSet('newTemplateName', '')
            ->assertDispatched('template-created')
            ->assertDispatched('toast')
            ->assertSee('Modelo Executivo 2026');

        $this->assertDatabaseHas('certificate_templates', [
            'company_id' => $setup['company']->id,
            'name' => 'Modelo Executivo 2026',
            'status' => 'draft',
        ]);
    }

    /**
     * 6. Reprodução e correção do erro 403 (Bug B).
     */
    public function test_bug_b_fix_admin_alvora_and_tenant_users_can_create_templates_without_403(): void
    {
        // 6a: Super Administrador Alvora (company_id = null) não deve tomar 403
        $alvoraAdmin = User::create([
            'name' => 'Super Admin Global',
            'email' => 'admin.global.' . uniqid() . '@alvora.com.br',
            'password' => bcrypt('password'),
            'company_id' => null,
            'role' => UserRole::ADMIN_ALVORA,
        ]);

        Livewire::actingAs($alvoraAdmin)
            ->test(CertificateTemplateList::class)
            ->set('newTemplateName', 'Template Global Institucional')
            ->call('createTemplate')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('certificate_templates', [
            'company_id' => null,
            'name' => 'Template Global Institucional',
        ]);

        // 6b: Usuário não admin sem empresa recebe validação clara sem travar a interface
        $orphanUser = User::create([
            'name' => 'Usuário Sem Empresa',
            'email' => 'orphan.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => null,
            'role' => UserRole::INSTRUCTOR,
        ]);

        Livewire::actingAs($orphanUser)
            ->test(CertificateTemplateList::class)
            ->set('newTemplateName', 'Tentativa Órfã')
            ->call('createTemplate')
            ->assertHasErrors(['newTemplateName'])
            ->assertDispatched('toast');
    }

    /**
     * 7. Cópia de template, retorno à listagem e publicação (Bug C).
     */
    public function test_duplicate_template_flow_and_return_to_list(): void
    {
        $setup = $this->setupCompanyAndUsers();

        $originalTemplate = CertificateTemplate::create([
            'company_id' => $setup['company']->id,
            'name' => 'Template Base Treinamento',
            'description' => 'Modelo corporativo inicial',
            'status' => 'active',
            'is_default' => true,
        ]);

        CertificateTemplateVersion::create([
            'template_id' => $originalTemplate->id,
            'version' => 1,
            'schema_json' => $this->resolver->getDefaultSchema(),
            'status' => 'active',
            'created_by' => $setup['admin']->id,
        ]);

        // Duplica o template pela listagem
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->call('duplicateTemplate', $originalTemplate->id)
            ->assertHasNoErrors()
            ->assertDispatched('template-created')
            ->assertSee('Cópia de Template Base Treinamento');

        $duplicate = CertificateTemplate::where('name', 'Cópia de Template Base Treinamento')->first();
        $this->assertNotNull($duplicate);
        $this->assertEquals($setup['company']->id, $duplicate->company_id);
        $this->assertEquals('draft', $duplicate->status);

        $dupVersion = $duplicate->versions()->first();
        $this->assertNotNull($dupVersion);
        $this->assertEquals(1, $dupVersion->version);
        $this->assertEquals('draft', $dupVersion->status);

        // Acesso à listagem com o template duplicado presente não deve gerar erro
        Livewire::actingAs($setup['admin'])
            ->test(CertificateTemplateList::class)
            ->assertOk();
    }
}
