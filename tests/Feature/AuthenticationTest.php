<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Portal Corporativo');
        $response->assertSee('Entrar na Plataforma');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $company = Company::create(['name' => 'Empresa Teste', 'status' => 'active']);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@empresa.com.br',
            'password' => Hash::make('password123'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@empresa.com.br')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $company = Company::create(['name' => 'Empresa Teste', 'status' => 'active']);

        User::create([
            'name' => 'Test User',
            'email' => 'test@empresa.com.br',
            'password' => Hash::make('password123'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@empresa.com.br')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $company = Company::create(['name' => 'Empresa Teste', 'status' => 'active']);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@empresa.com.br',
            'password' => Hash::make('password123'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
