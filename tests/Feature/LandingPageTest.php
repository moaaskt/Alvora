<?php

namespace Tests\Feature;

use App\Livewire\LeadForm;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ALVORA');
        $response->assertSee('41,6%');
        $response->assertSee('Solicitar Demonstração');
        $response->assertSee('Score Preditivo de Evasão');
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
