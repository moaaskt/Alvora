<?php

namespace Tests\Feature;

use App\Livewire\LeadForm;
use App\Models\Lead;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ALVORA');
        $response->assertSee('Catálogo de Cursos');
        $response->assertSee('Solicitar Demonstração');
        $response->assertSee('Score Preditivo de Evasão');
    }

    public function test_landing_page_displays_only_published_demo_courses_and_never_leaks_other_tenants(): void
    {
        // 1. Empresa de Demonstração Oficial
        $demoCompany = \App\Models\Company::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            ['name' => 'Acme Corporation Brasil', 'status' => 'active']
        );

        $demoInstructor = \App\Models\User::firstOrCreate(
            ['email' => 'professor@acme.com.br'],
            [
                'name' => 'Prof. Carlos Eduardo',
                'password' => bcrypt('password'),
                'company_id' => $demoCompany->id,
                'role' => \App\Enums\UserRole::INSTRUCTOR,
            ]
        );

        $demoCourse = \App\Models\Course::firstOrCreate(
            ['slug' => 'fundamentos-de-lideranca-e-alta-performance'],
            [
                'company_id' => $demoCompany->id,
                'instructor_id' => $demoInstructor->id,
                'title' => 'Fundamentos de Liderança e Alta Performance',
                'status' => 'published',
            ]
        );

        // 2. Curso em rascunho na empresa de demonstração (não deve aparecer)
        $draftCourse = \App\Models\Course::create([
            'company_id' => $demoCompany->id,
            'instructor_id' => $demoInstructor->id,
            'title' => 'Curso de Rascunho Interno Alvora',
            'slug' => 'curso-rascunho-interno',
            'status' => 'draft',
        ]);

        // 3. Empresa Privada B totalmente separada (Tenant B)
        $privateCompany = \App\Models\Company::create(['name' => 'Empresa Confidencial B', 'status' => 'active']);
        $privateInstructor = \App\Models\User::create([
            'name' => 'Instrutor Secreto',
            'email' => 'secreto@empresa-b.com',
            'password' => bcrypt('password'),
            'company_id' => $privateCompany->id,
            'role' => \App\Enums\UserRole::INSTRUCTOR,
        ]);

        $privateCourse = \App\Models\Course::create([
            'company_id' => $privateCompany->id,
            'instructor_id' => $privateInstructor->id,
            'title' => 'Estratégia Ultrassecreta Empresa B',
            'slug' => 'estrategia-ultrassecreta',
            'status' => 'published',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verificações rigorosas de Isolamento Multi-Tenant
        $this->assertFalse(str_contains($content, 'Estratégia Ultrassecreta Empresa B'), 'O curso confidencial da Empresa B vazou na vitrine pública!');
        $this->assertFalse(str_contains($content, 'Instrutor Secreto'), 'O instrutor confidencial da Empresa B vazou na vitrine pública!');
        $this->assertFalse(str_contains($content, 'Curso de Rascunho Interno Alvora'), 'O curso em rascunho vazou na vitrine pública!');
        $this->assertTrue(str_contains($content, 'Fundamentos de Liderança e Alta Performance'), 'O curso público autorizado não foi exibido!');
        $this->assertTrue(str_contains($content, 'Prof. Carlos Eduardo'), 'O instrutor público autorizado não foi exibido!');
    }

    public function test_landing_page_renders_gracefully_when_no_courses_exist(): void
    {
        $response = $this->view('landing', [
            'showcaseCourses' => collect(),
            'featuredInstructors' => collect(),
            'featuredTracks' => collect(),
        ]);

        $response->assertSee('Catálogo em Atualização Curatorial');
        $response->assertSee('ALVORA');
    }

    public function test_pricing_page_renders_with_three_tiers(): void
    {
        $response = $this->get('/planos');

        $response->assertStatus(200);
        $response->assertSee('Starter');
        $response->assertSee('Pro + IA');
        $response->assertSee('Enterprise');
        $response->assertSee('R$ 490');
        $response->assertSee('R$ 1.290');
    }

    public function test_lead_form_validates_required_fields(): void
    {
        Livewire::test(LeadForm::class)
            ->set('name', '')
            ->set('email', '')
            ->set('company', '')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'company']);
    }

    public function test_lead_form_creates_lead_in_database(): void
    {
        Livewire::test(LeadForm::class)
            ->set('name', 'Mariana Rocha')
            ->set('email', 'mariana@techcorp.com.br')
            ->set('company', 'TechCorp Solutions')
            ->set('role', 'Gerente de RH')
            ->set('employees_count', '21-100')
            ->set('message', 'Gostaríamos de reduzir a taxa de abandono dos treinamentos internos.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('leads', [
            'name' => 'Mariana Rocha',
            'email' => 'mariana@techcorp.com.br',
            'company' => 'TechCorp Solutions',
            'role' => 'Gerente de RH',
            'employees_count' => '21-100',
            'status' => 'novo',
        ]);
    }
}
