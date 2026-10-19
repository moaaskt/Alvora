<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserHasRole;
use App\Models\Company;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Test Model that uses BelongsToTenant
class TestTenantDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'test_tenant_documents';
    protected $fillable = ['title', 'company_id'];
}

class TenantScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('test_tenant_documents');
        Schema::create('test_tenant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Route::middleware(['web', 'role:company_admin'])->get('/test-company-admin-route', function () {
            return response()->json(['status' => 'authorized']);
        });

        Route::middleware(['web', 'role:admin_alvora'])->get('/test-admin-alvora-route', function () {
            return response()->json(['status' => 'authorized']);
        });
    }

    public function test_tenant_scope_filters_queries_to_authenticated_user_company(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Empresa Alfa {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Empresa Beta {$unique}", 'status' => 'active']);

        $userA = User::create([
            'name' => 'João Alfa',
            'email' => "joao_{$unique}@alfa.com",
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $docA = TestTenantDocument::create(['title' => 'Documento Alfa', 'company_id' => $companyA->id]);
        $docB = TestTenantDocument::create(['title' => 'Documento Beta', 'company_id' => $companyB->id]);

        $this->actingAs($userA);

        $results = TestTenantDocument::all();

        $this->assertCount(1, $results);
        $this->assertEquals($docA->id, $results->first()->id);
        $this->assertEquals('Documento Alfa', $results->first()->title);
    }

    public function test_tenant_scope_automatically_injects_company_id_on_creation(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Tech Corp {$unique}", 'status' => 'active']);

        $user = User::create([
            'name' => 'Admin RH',
            'email' => "rh_{$unique}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $this->actingAs($user);

        // Create without passing company_id
        $doc = TestTenantDocument::create(['title' => 'Manual de Boas-Vindas']);

        $this->assertEquals($company->id, $doc->company_id);
    }

    public function test_admin_alvora_bypasses_tenant_isolation_completely(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Empresa A {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Empresa B {$unique}", 'status' => 'active']);

        TestTenantDocument::create(['title' => 'Doc 1', 'company_id' => $companyA->id]);
        TestTenantDocument::create(['title' => 'Doc 2', 'company_id' => $companyB->id]);

        $superAdmin = User::create([
            'name' => 'Admin Global',
            'email' => "admin_{$unique}@alvora.com",
            'password' => bcrypt('password'),
            'company_id' => null,
            'role' => UserRole::ADMIN_ALVORA,
        ]);

        $this->actingAs($superAdmin);

        $results = TestTenantDocument::all();

        // Admin Alvora sees all documents across all tenants
        $this->assertCount(2, $results);
    }

    public function test_ensure_user_has_role_middleware(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Acme Inc {$unique}", 'status' => 'active']);

        $rhUser = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$unique}@acme.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $studentUser = User::create([
            'name' => 'Aluno Simples',
            'email' => "aluno_{$unique}@acme.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        // Student tries to access company_admin route -> 403
        $this->actingAs($studentUser);
        $response = $this->get('/test-company-admin-route');
        $response->assertStatus(403);

        // RH accesses company_admin route -> 200
        $this->actingAs($rhUser);
        $response = $this->get('/test-company-admin-route');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'authorized']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_tenant_documents');
        parent::tearDown();
    }
}
