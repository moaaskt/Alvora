<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\CategoryManager;
use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagerTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $instructor;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Alvora Global Corp ' . uniqid(),
            'status' => 'active',
            'cnpj' => '12.345.678/0001-' . rand(10, 99),
        ]);

        Subscription::create([
            'company_id' => $this->company->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'amount' => 199.00,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin Teste',
            'company_id' => $this->company->id,
            'role' => UserRole::ADMIN_ALVORA,
            'email' => 'superadmin_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);

        $this->instructor = User::create([
            'name' => 'Instrutor Teste',
            'company_id' => $this->company->id,
            'role' => UserRole::INSTRUCTOR,
            'email' => 'instructor_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_guest_cannot_access_categories_management(): void
    {
        $response = $this->get('/admin/categorias');
        $response->assertRedirect('/login');
    }

    public function test_instructor_cannot_access_categories_management(): void
    {
        $response = $this->actingAs($this->instructor)->get('/admin/categorias');
        $response->assertForbidden();
    }

    public function test_super_admin_can_access_categories_management_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/categorias');
        $response->assertOk();
        $response->assertSee('Categorias e Subcategorias de Cursos');
        $response->assertSee('Mapeamento Hierárquico Completo');
    }

    public function test_can_create_root_category(): void
    {
        $uniqueSlug = 'seguranca-ofensiva-cyber-' . uniqid();

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('openCreateModal')
            ->set('name', 'Segurança Ofensiva & Cyber')
            ->set('slug', $uniqueSlug)
            ->set('icon', 'sparkles')
            ->set('description', 'Testes de intrusão, pentest e governança SOC.')
            ->call('saveCategory')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('categories', [
            'name' => 'Segurança Ofensiva & Cyber',
            'slug' => $uniqueSlug,
            'parent_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_can_create_subcategory_linked_to_parent(): void
    {
        $parent = Category::create([
            'name' => 'Dev Web Test ' . uniqid(),
            'slug' => 'dev-web-' . uniqid(),
            'is_active' => true,
        ]);

        $subSlug = 'vuejs-nuxt-' . uniqid();

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('openCreateModal', $parent->id)
            ->set('name', 'Vue.js & Nuxt')
            ->set('slug', $subSlug)
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Vue.js & Nuxt',
            'slug' => $subSlug,
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
    }

    public function test_can_update_existing_category(): void
    {
        $category = Category::create([
            'name' => 'Design Antigo ' . uniqid(),
            'slug' => 'design-antigo-' . uniqid(),
            'is_active' => true,
        ]);

        $newSlug = 'design-moderno-' . uniqid();

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('openEditModal', $category->id)
            ->set('name', 'Design & UI/UX Moderno')
            ->set('slug', $newSlug)
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Design & UI/UX Moderno',
            'slug' => $newSlug,
        ]);
    }

    public function test_validates_required_fields_and_slug_uniqueness(): void
    {
        $existingSlug = 'slug-existente-' . uniqid();
        Category::create([
            'name' => 'Existente',
            'slug' => $existingSlug,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('slug', '')
            ->call('saveCategory')
            ->assertHasErrors(['name' => 'required', 'slug' => 'required']);

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('openCreateModal')
            ->set('name', 'Outro')
            ->set('slug', $existingSlug)
            ->call('saveCategory')
            ->assertHasErrors(['slug' => 'unique']);
    }

    public function test_can_toggle_category_status(): void
    {
        $category = Category::create([
            'name' => 'Inteligência Artificial ' . uniqid(),
            'slug' => 'ia-toggle-' . uniqid(),
            'is_active' => true,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('toggleStatus', $category->id);

        $this->assertFalse($category->fresh()->is_active);

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('toggleStatus', $category->id);

        $this->assertTrue($category->fresh()->is_active);
    }

    public function test_can_delete_category_safely_nullifying_course_references(): void
    {
        $category = Category::create([
            'name' => 'Categoria Temporária ' . uniqid(),
            'slug' => 'cat-temp-' . uniqid(),
            'is_active' => true,
        ]);

        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Curso de Teste com Categoria ' . uniqid(),
            'slug' => 'curso-teste-cat-' . uniqid(),
            'status' => 'draft',
        ]);

        $this->assertEquals($category->id, $course->fresh()->category_id);

        Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->call('deleteCategory');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertNull($course->fresh()->category_id);
    }

    public function test_can_export_categories_csv(): void
    {
        Category::create([
            'name' => 'Cat Para Export ' . uniqid(),
            'slug' => 'cat-export-' . uniqid(),
            'is_active' => true,
        ]);

        $response = Livewire::actingAs($this->superAdmin)
            ->test(CategoryManager::class)
            ->call('exportCsv');

        $response->assertFileDownloaded();
    }
}
