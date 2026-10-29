<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\UserManager;
use App\Models\Company;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagerTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $instructor;
    private User $student;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Test Corp ' . uniqid(),
            'status' => 'active',
            'cnpj' => '12.345.678/0001-' . rand(10, 99),
        ]);

        Subscription::create([
            'company_id' => $this->company->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'amount' => 499.00,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin Test ' . uniqid(),
            'company_id' => null,
            'role' => UserRole::ADMIN_ALVORA,
            'email' => 'admin_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->instructor = User::create([
            'name' => 'Prof. Instructor Test ' . uniqid(),
            'company_id' => $this->company->id,
            'role' => UserRole::INSTRUCTOR,
            'email' => 'instructor_' . uniqid() . '@acme.com',
            'password' => bcrypt('password'),
            'phone' => '(11) 98888-7777',
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Lucas Student Test ' . uniqid(),
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
            'email' => 'student_' . uniqid() . '@acme.com',
            'password' => bcrypt('password'),
            'phone' => '(11) 97777-6666',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_access_user_management_routes(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Gestão de Administradores');

        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.admins'))
            ->assertOk()
            ->assertSee('Administradores');

        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.instructors'))
            ->assertOk()
            ->assertSee('Gestão de Instrutores');

        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.students'))
            ->assertOk()
            ->assertSee('Gestão de Estudantes');
    }

    public function test_student_and_instructor_cannot_access_admin_user_routes(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($this->instructor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_component_switches_tabs_correctly(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->assertSet('role', 'admin')
            ->call('switchRole', 'instructor')
            ->assertSet('role', 'instructor')
            ->assertSee('Cursos Criados')
            ->call('switchRole', 'student')
            ->assertSet('role', 'student')
            ->assertSee('Matrículas');
    }

    public function test_search_filters_users_by_name_and_email(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('switchRole', 'student')
            ->set('search', $this->student->email)
            ->assertSee($this->student->name)
            ->set('search', 'NonExistentUserEmail_12345')
            ->assertSee('Nenhum estudante encontrado');
    }

    public function test_super_admin_can_create_new_administrator(): void
    {
        $uniqueEmail = 'new_admin_' . uniqid() . '@alvora.com';

        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('openCreateModal')
            ->set('name', 'Novo Administrador de Teste')
            ->set('email', $uniqueEmail)
            ->set('phone', '(11) 91234-5678')
            ->set('userRole', UserRole::ADMIN_ALVORA->value)
            ->set('company_id', null)
            ->set('password', 'secret12345')
            ->set('password_confirmation', 'secret12345')
            ->set('is_active', true)
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('showUserModal', false);

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'name' => 'Novo Administrador de Teste',
            'role' => UserRole::ADMIN_ALVORA->value,
            'phone' => '(11) 91234-5678',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_create_new_student_for_company(): void
    {
        $uniqueEmail = 'new_student_' . uniqid() . '@acme.com';

        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('switchRole', 'student')
            ->call('openCreateModal')
            ->set('name', 'Novo Aluno Corporativo')
            ->set('email', $uniqueEmail)
            ->set('phone', '(11) 99999-8888')
            ->set('userRole', UserRole::STUDENT->value)
            ->set('company_id', $this->company->id)
            ->set('password', 'alunopass123')
            ->set('password_confirmation', 'alunopass123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT->value,
        ]);
    }

    public function test_super_admin_can_edit_existing_user(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('openEditModal', $this->student->id)
            ->assertSet('name', $this->student->name)
            ->assertSet('email', $this->student->email)
            ->set('name', 'Lucas Aluno Nome Atualizado')
            ->set('phone', '(11) 91111-2222')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->student->refresh();
        $this->assertEquals('Lucas Aluno Nome Atualizado', $this->student->name);
        $this->assertEquals('(11) 91111-2222', $this->student->phone);
    }

    public function test_super_admin_can_toggle_user_status(): void
    {
        $this->assertTrue((bool) $this->student->is_active);

        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('toggleStatus', $this->student->id);

        $this->student->refresh();
        $this->assertFalse((bool) $this->student->is_active);

        // Reativar
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('toggleStatus', $this->student->id);

        $this->student->refresh();
        $this->assertTrue((bool) $this->student->is_active);
    }

    public function test_super_admin_cannot_deactivate_themselves(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('toggleStatus', $this->superAdmin->id);

        $this->superAdmin->refresh();
        $this->assertTrue((bool) $this->superAdmin->is_active);
    }

    public function test_super_admin_can_redefine_user_password(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('openPasswordModal', $this->student->id)
            ->set('new_password', 'novasenha888')
            ->set('new_password_confirmation', 'novasenha888')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('showPasswordModal', false);

        $this->student->refresh();
        $this->assertTrue(Hash::check('novasenha888', $this->student->password));
    }

    public function test_csv_export_returns_streamed_response(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManager::class)
            ->call('switchRole', 'student')
            ->call('exportCsv')
            ->assertFileDownloaded('alvora_usuarios_student_' . date('Y-m-d') . '.csv');
    }
}
