<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Settings\Notifications;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login_from_settings_routes(): void
    {
        $this->get(route('settings.profile'))->assertRedirect(route('login'));
        $this->get(route('settings.security'))->assertRedirect(route('login'));
        $this->get(route('settings.notifications'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_settings_profile_rendered_in_admin_layout(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN_ALVORA,
            'name' => 'Carlos Administrador',
            'email' => 'admin@alvora.test',
        ]);

        $this->actingAs($admin)
            ->get(route('settings.profile'))
            ->assertOk()
            ->assertSee('Carlos Administrador')
            ->assertSee('admin@alvora.test')
            ->assertSee('Configurações')
            ->assertSee('Meu Perfil');
    }

    public function test_instructor_can_access_settings_profile_rendered_in_instructor_layout(): void
    {
        $company = Company::create(['name' => 'Acme Corporation', 'status' => 'active']);
        $instructor = User::factory()->create([
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $company->id,
            'name' => 'Professora Ana',
            'email' => 'ana@acme.test',
        ]);

        $this->actingAs($instructor)
            ->get(route('settings.profile'))
            ->assertOk()
            ->assertSee('Professora Ana')
            ->assertSee('ana@acme.test')
            ->assertSee('Acme Corporation');
    }

    public function test_student_can_access_settings_profile_rendered_in_student_layout(): void
    {
        $company = Company::create(['name' => 'Tech Corp', 'status' => 'active']);
        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'company_id' => $company->id,
            'name' => 'Lucas Aluno',
            'email' => 'lucas@techcorp.test',
        ]);

        $this->actingAs($student)
            ->get(route('settings.profile'))
            ->assertOk()
            ->assertSee('Lucas Aluno')
            ->assertSee('lucas@techcorp.test')
            ->assertSee(route('settings.profile'))
            ->assertSee('Meu Perfil');

        // Also verify sidebar link on student dashboard
        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('settings.profile'))
            ->assertSee('Meu Perfil');
    }

    public function test_user_can_update_profile_information_but_email_is_immutable(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::INSTRUCTOR,
            'name' => 'Nome Original',
            'email' => 'original@alvora.test',
            'phone' => '11999999999',
            'bio' => 'Bio inicial',
        ]);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->assertSet('name', 'Nome Original')
            ->assertSet('email', 'original@alvora.test')
            ->set('name', 'Nome Atualizado')
            ->set('phone', '11988888888')
            ->set('bio', 'Bio atualizada com experiência')
            ->set('job_title', 'Tech Lead')
            ->set('department', 'Engenharia')
            ->set('linkedin_url', 'https://linkedin.com/in/perfil')
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $user->refresh();

        $this->assertSame('Nome Atualizado', $user->name);
        $this->assertSame('11988888888', $user->phone);
        $this->assertSame('Bio atualizada com experiência', $user->bio);
        $this->assertSame('Tech Lead', $user->job_title);
        $this->assertSame('Engenharia', $user->department);
        $this->assertSame('https://linkedin.com/in/perfil', $user->linkedin_url);
        $this->assertSame('original@alvora.test', $user->email);
    }

    public function test_user_can_upload_and_remove_avatar_photo(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STUDENT,
        ]);

        $file = UploadedFile::fake()->image('meu-avatar.jpg', 400, 400);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('photo', $file)
            ->call('uploadPhoto')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        // Remove avatar
        Livewire::actingAs($user)
            ->test(Profile::class)
            ->call('removePhoto')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $user->refresh();
        $this->assertNull($user->avatar);
    }

    public function test_security_page_changes_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('SenhaAntiga@123'),
        ]);

        Livewire::actingAs($user)
            ->test(Security::class)
            ->set('current_password', 'SenhaAntiga@123')
            ->set('new_password', 'NovaSenhaSegura@2026')
            ->set('new_password_confirmation', 'NovaSenhaSegura@2026')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $user->refresh();
        $this->assertTrue(Hash::check('NovaSenhaSegura@2026', $user->password));
    }

    public function test_security_page_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('SenhaCorreta@123'),
        ]);

        Livewire::actingAs($user)
            ->test(Security::class)
            ->set('current_password', 'SenhaErrada!')
            ->set('new_password', 'NovaSenhaValida@2026')
            ->set('new_password_confirmation', 'NovaSenhaValida@2026')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('SenhaCorreta@123', $user->password));
    }

    public function test_notifications_page_saves_user_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->set('email_course_updates', false)
            ->set('email_announcements', true)
            ->call('savePreferences')
            ->assertHasNoErrors()
            ->assertDispatched('toast');
    }
}
