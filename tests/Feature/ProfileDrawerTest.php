<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\ProfileDrawer;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileDrawerTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $instructor;
    private User $student;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->company = Company::create([
            'name' => 'Alvora Test Corp ' . uniqid(),
            'status' => 'active',
            'cnpj' => '12.345.678/0001-' . rand(10, 99),
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::ADMIN_ALVORA,
            'is_active' => true,
        ]);

        $this->instructor = User::create([
            'name' => 'Instructor Test',
            'email' => 'inst_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password123'),
            'company_id' => $this->company->id,
            'role' => UserRole::INSTRUCTOR,
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Student Test',
            'email' => 'stud_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password123'),
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
    }

    public function test_drawer_can_be_opened_and_loads_user_data(): void
    {
        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->assertSet('isOpen', false)
            ->dispatch('open-profile-drawer')
            ->assertSet('isOpen', true)
            ->assertSet('name', 'Student Test')
            ->assertSet('email', $this->student->email)
            ->assertSee('Dados Cadastrais')
            ->assertSee('Segurança & Senha', false)
            ->assertSee('Atividades');
    }

    public function test_admin_does_not_see_activities_tab(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->assertSet('isOpen', true)
            ->assertSee('Dados Cadastrais')
            ->assertSee('Segurança & Senha', false)
            ->assertDontSee('Atividades');
    }

    public function test_user_can_update_profile_and_email_remains_unchanged(): void
    {
        $originalEmail = $this->student->email;

        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->set('name', 'Student Name Updated')
            ->set('phone', '(11) 99999-8888')
            ->set('bio', 'Minha biografia profissional de tecnologia.')
            ->set('linkedin_url', 'https://linkedin.com/in/student-test')
            ->set('email', 'attempt_to_hack@alvora.com') // Should NOT change
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertDispatched('profile-updated');

        $this->student->refresh();
        $this->assertEquals('Student Name Updated', $this->student->name);
        $this->assertEquals('(11) 99999-8888', $this->student->phone);
        $this->assertEquals('Minha biografia profissional de tecnologia.', $this->student->bio);
        $this->assertEquals('https://linkedin.com/in/student-test', $this->student->linkedin_url);
        $this->assertEquals($originalEmail, $this->student->email); // Email strictly unchanged
    }

    public function test_instructor_can_update_job_title_and_department(): void
    {
        Livewire::actingAs($this->instructor)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->set('name', 'Instructor Updated')
            ->set('job_title', 'Tech Specialist')
            ->set('department', 'Engenharia & Dados')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->instructor->refresh();
        $this->assertEquals('Instructor Updated', $this->instructor->name);
        $this->assertEquals('Tech Specialist', $this->instructor->job_title);
        $this->assertEquals('Engenharia & Dados', $this->instructor->department);
    }

    public function test_user_can_upload_and_remove_avatar_photo(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);

        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->set('photo', $file)
            ->call('uploadPhoto')
            ->assertHasNoErrors()
            ->assertDispatched('profile-updated');

        $this->student->refresh();
        $this->assertNotNull($this->student->avatar);
        Storage::disk('public')->assertExists($this->student->avatar);
        $this->assertStringContainsString('storage/avatars/', $this->student->avatar_url);

        // Remove photo
        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->call('removePhoto')
            ->assertDispatched('profile-updated');

        $this->student->refresh();
        $this->assertNull($this->student->avatar);
        $this->assertNull($this->student->avatar_url);
    }

    public function test_password_update_requires_correct_current_password(): void
    {
        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->set('activeTab', 'security')
            ->set('current_password', 'wrong_password')
            ->set('new_password', 'newSecurePassword123!')
            ->set('new_password_confirmation', 'newSecurePassword123!')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->student->refresh();
        $this->assertTrue(Hash::check('password123', $this->student->password));
    }

    public function test_password_can_be_successfully_updated(): void
    {
        Livewire::actingAs($this->student)
            ->test(ProfileDrawer::class)
            ->dispatch('open-profile-drawer')
            ->set('activeTab', 'security')
            ->set('current_password', 'password123')
            ->set('new_password', 'newSecurePassword123!')
            ->set('new_password_confirmation', 'newSecurePassword123!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->student->refresh();
        $this->assertTrue(Hash::check('newSecurePassword123!', $this->student->password));
    }
}
