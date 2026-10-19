<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Company\EmployeeManager;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_can_send_invitations_with_7_day_expiry(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $rhUser = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh@alfa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $this->actingAs($rhUser);

        Livewire::test(EmployeeManager::class)
            ->set('emailsText', "colaborador1@alfa.com, colaborador2@alfa.com")
            ->set('role', 'student')
            ->call('inviteEmployees')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('invitations', [
            'company_id' => $company->id,
            'email' => 'colaborador1@alfa.com',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('invitations', [
            'company_id' => $company->id,
            'email' => 'colaborador2@alfa.com',
            'role' => 'student',
        ]);

        $invitation = Invitation::where('email', 'colaborador1@alfa.com')->first();
        $this->assertNotNull($invitation->token);
        // Expiry should be around 7 days from now
        $this->assertTrue($invitation->expires_at->isFuture());
        $this->assertEquals(7, (int) round(now()->diffInDays($invitation->expires_at)));
    }

    public function test_invitation_screen_renders_for_valid_token(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $invitation = Invitation::create([
            'company_id' => $company->id,
            'email' => 'novo@alfa.com',
            'role' => 'student',
            'token' => 'valid-test-token-12345',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get('/convite/valid-test-token-12345');

        $response->assertStatus(200);
        $response->assertSee('Ativar Seu Acesso');
        $response->assertSee('Empresa Alfa');
        $response->assertSee('novo@alfa.com');
    }

    public function test_invitation_screen_shows_error_for_expired_or_invalid_token(): void
    {
        $response = $this->get('/convite/non-existing-token');

        $response->assertStatus(200);
        $response->assertSee('Este link de convite expirou ou já foi utilizado');
    }

    public function test_user_can_accept_invitation_and_set_password(): void
    {
        $company = Company::create(['name' => 'Empresa Alfa', 'status' => 'active']);

        $invitation = Invitation::create([
            'company_id' => $company->id,
            'email' => 'colaborador@alfa.com',
            'role' => 'student',
            'token' => 'active-invite-token-777',
            'expires_at' => now()->addDays(7),
        ]);

        Livewire::test(AcceptInvitation::class, ['token' => 'active-invite-token-777'])
            ->set('name', 'Roberto Dias')
            ->set('password', 'senhaSegura123')
            ->set('password_confirmation', 'senhaSegura123')
            ->call('accept')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        // User was created with correct tenant company and role
        $this->assertDatabaseHas('users', [
            'name' => 'Roberto Dias',
            'email' => 'colaborador@alfa.com',
            'company_id' => $company->id,
            'role' => 'student',
        ]);

        // Invitation marked as accepted
        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
        $this->assertTrue($invitation->isAccepted());

        // User is authenticated
        $this->assertAuthenticated();
    }
}
