<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\EnrollmentManager;
use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEnrollmentManagerTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $instructor;
    private User $student;
    private Company $company;
    private Course $course;

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
            'name' => 'Carlos Drummond Super Admin',
            'company_id' => $this->company->id,
            'role' => UserRole::ADMIN_ALVORA,
            'email' => 'superadmin_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);

        $this->instructor = User::create([
            'name' => 'Instrutor Responsável',
            'company_id' => $this->company->id,
            'role' => UserRole::INSTRUCTOR,
            'email' => 'instructor_' . uniqid() . '@alvora.com',
            'password' => bcrypt('password'),
        ]);

        $this->student = User::create([
            'name' => 'Aluno Rodrigo Martins',
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
            'email' => 'student_' . uniqid() . '@acme.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Tecnologia da Informação',
            'slug' => 'ti-' . uniqid(),
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Desenvolvimento Web Moderno',
            'slug' => 'dev-web-' . uniqid(),
            'description' => 'Curso prático de desenvolvimento web e arquitetura.',
            'status' => 'published',
            'duration_hours' => 40,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/matriculas');
        $response->assertRedirect('/login');
    }

    public function test_instructor_or_student_cannot_access_super_admin_enrollments(): void
    {
        $response = $this->actingAs($this->instructor)->get('/admin/matriculas');
        $response->assertStatus(403);

        $responseStudent = $this->actingAs($this->student)->get('/admin/matriculas');
        $responseStudent->assertStatus(403);
    }

    public function test_super_admin_can_access_enrollments_screen(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/matriculas');
        $response->assertStatus(200);
        $response->assertSeeLivewire(EnrollmentManager::class);
        $response->assertSee('Gestão de Matrículas e Acessos');
        $response->assertSee('Matricular Aluno');
    }

    public function test_super_admin_can_access_enrollment_create_screen(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/matriculas/novo');
        $response->assertStatus(200);
        $response->assertSeeLivewire(EnrollmentManager::class);
        $response->assertSee('Atribuição Manual de Matrícula');
        $response->assertSee('1. Selecionar Aluno Corporativo');
        $response->assertSee('2. Selecionar Curso do Catálogo');
        $response->assertSee('Resumo da Atribuição');
    }

    public function test_super_admin_can_enroll_student_with_lifetime_access(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('openCreateModal')
            ->set('selectedUserId', $this->student->id)
            ->set('selectedCourseId', $this->course->id)
            ->set('isLifetime', true)
            ->call('enrollStudent')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        $enrollment = Enrollment::withoutGlobalScopes()
            ->where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertTrue($enrollment->isLifetime());
        $this->assertFalse($enrollment->isExpired());
    }

    public function test_super_admin_can_enroll_student_with_expiration_date(): void
    {
        $expiration = now()->addDays(90)->format('Y-m-d');

        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('openCreateModal')
            ->set('selectedUserId', $this->student->id)
            ->set('selectedCourseId', $this->course->id)
            ->set('isLifetime', false)
            ->set('expiresAt', $expiration)
            ->call('enrollStudent')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $enrollment = Enrollment::withoutGlobalScopes()
            ->where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertFalse($enrollment->isLifetime());
        $this->assertNotNull($enrollment->expires_at);
        $this->assertEquals($expiration, $enrollment->expires_at->format('Y-m-d'));
    }

    public function test_cannot_enroll_same_student_twice_in_active_course(): void
    {
        // Cria primeira matrícula ativa
        Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        // Tenta cadastrar novamente
        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('openCreateModal')
            ->set('selectedUserId', $this->student->id)
            ->set('selectedCourseId', $this->course->id)
            ->set('isLifetime', true)
            ->call('enrollStudent')
            ->assertDispatched('toast');

        // Garante que só existe 1 registro no banco
        $count = Enrollment::withoutGlobalScopes()
            ->where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_reactivates_dropped_enrollment_upon_new_enrollment(): void
    {
        $droppedEnrollment = Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'dropped',
            'enrolled_at' => now()->subMonths(2),
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('openCreateModal')
            ->set('selectedUserId', $this->student->id)
            ->set('selectedCourseId', $this->course->id)
            ->set('isLifetime', true)
            ->call('enrollStudent')
            ->assertDispatched('toast');

        $droppedEnrollment->refresh();
        $this->assertEquals('active', $droppedEnrollment->status);
    }

    public function test_super_admin_can_revoke_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('confirmRevoke', $enrollment->id)
            ->assertSet('showRevokeModal', true)
            ->assertSet('revokingEnrollmentId', $enrollment->id)
            ->call('revokeEnrollment')
            ->assertSet('showRevokeModal', false)
            ->assertDispatched('toast');

        $enrollment->refresh();
        $this->assertEquals('dropped', $enrollment->status);
    }

    public function test_super_admin_can_delete_enrollment_permanently(): void
    {
        $enrollment = Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('deleteEnrollmentPermanently', $enrollment->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }

    public function test_super_admin_can_export_csv(): void
    {
        Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'expires_at' => null,
            'enrolled_by_id' => $this->superAdmin->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(EnrollmentManager::class)
            ->call('exportCsv')
            ->assertFileDownloaded('alvora_matriculas_historico_' . date('Y-m-d') . '.csv');
    }
}

