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
}
