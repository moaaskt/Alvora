<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CertificateService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CourseAccessScopeAndPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $instructorA;
    protected User $instructorB;
    protected User $managerA;
    protected User $studentA;
    protected User $admin;
    protected Course $courseDraftA;
    protected Course $coursePublishedA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::create([
            'name' => 'Empresa Alfa Testes',
            'cnpj' => '11.111.111/0001-11',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        $this->companyB = Company::create([
            'name' => 'Empresa Beta Testes',
            'cnpj' => '22.222.222/0001-22',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        Subscription::create([
            'company_id' => $this->companyA->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin Alvora',
            'email' => 'admin@alvora.global',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->instructorA = User::create([
            'name' => 'Instrutor Alfa 1',
            'email' => 'prof1@alfa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->companyA->id,
        ]);
        $this->instructorA->companies()->attach($this->companyA->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->instructorB = User::create([
            'name' => 'Instrutor Alfa 2',
            'email' => 'prof2@alfa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->companyA->id,
        ]);
        $this->instructorB->companies()->attach($this->companyA->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->managerA = User::create([
            'name' => 'Gestor RH Alfa',
            'email' => 'gestor@alfa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $this->companyA->id,
        ]);
        $this->managerA->companies()->attach($this->companyA->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->studentA = User::create([
            'name' => 'Aluno Alfa',
            'email' => 'aluno@alfa.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->companyA->id,
        ]);
        $this->studentA->companies()->attach($this->companyA->id, [
            'role' => 'student',
            'status' => 'active',
        ]);

        // Criação de cursos da Empresa A
        $this->courseDraftA = Course::create([
            'company_id' => $this->companyA->id,
            'instructor_id' => $this->instructorA->id,
            'title' => 'Curso em Rascunho do Instrutor A',
            'slug' => 'curso-rascunho-a',
            'status' => 'draft',
        ]);

        $this->coursePublishedA = Course::create([
            'company_id' => $this->companyA->id,
            'instructor_id' => $this->instructorA->id,
            'title' => 'Curso Publicado do Instrutor A',
            'slug' => 'curso-publicado-a',
            'status' => 'published',
        ]);
    }

    public function test_course_access_scope_restricts_draft_visibility_to_author_and_super_admin(): void
    {
        // 1. O próprio autor (Instrutor A) visualiza seu curso em rascunho
        $this->actingAs($this->instructorA);
        $coursesAuthor = Course::all();
        $this->assertTrue($coursesAuthor->contains($this->courseDraftA));
        $this->assertTrue($coursesAuthor->contains($this->coursePublishedA));

        // 2. Outro instrutor da mesma empresa (Instrutor B) NÃO vê o rascunho de A
        $this->actingAs($this->instructorB);
        $coursesOtherInstructor = Course::all();
        $this->assertFalse($coursesOtherInstructor->contains($this->courseDraftA), 'Rascunho de outro autor não deve ser visível para Instrutor B');
        $this->assertTrue($coursesOtherInstructor->contains($this->coursePublishedA), 'Cursos publicados são visíveis para a equipe');

        // 3. Gestor corporativo da empresa NÃO vê o rascunho de terceiros
        $this->actingAs($this->managerA);
        $coursesManager = Course::all();
        $this->assertFalse($coursesManager->contains($this->courseDraftA), 'Gestor não deve ver rascunho didático de instrutores');
        $this->assertTrue($coursesManager->contains($this->coursePublishedA));

        // 4. Colaborador comum não matriculado NÃO vê o rascunho
        $this->actingAs($this->studentA);
        $coursesStudent = Course::all();
        $this->assertFalse($coursesStudent->contains($this->courseDraftA));
        $this->assertTrue($coursesStudent->contains($this->coursePublishedA));

        // 5. Super Admin Alvora possui governança global e vê todos os cursos e rascunhos
        $this->actingAs($this->admin);
        $coursesAdmin = Course::all();
        $this->assertTrue($coursesAdmin->contains($this->courseDraftA));
        $this->assertTrue($coursesAdmin->contains($this->coursePublishedA));
    }

    public function test_course_access_scope_permits_student_to_see_enrolled_courses_even_if_not_published(): void
    {
        // Aluno matriculado no curso em rascunho passa a visualizá-lo via escopo
        Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->courseDraftA->id,
            'status' => 'active',
            'progress_percent' => 50,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($this->studentA);
        $courses = Course::all();
        $this->assertTrue($courses->contains($this->courseDraftA), 'Aluno matriculado deve enxergar o curso');
    }

    public function test_course_policy_update_permits_author_and_forbids_other_instructors_and_managers(): void
    {
        // 1. Autor com vínculo ativo tem permissão de update
        $this->assertTrue(Gate::forUser($this->instructorA)->allows('update', $this->courseDraftA));

        // 2. Outro instrutor NÃO tem permissão de update
        $this->assertFalse(Gate::forUser($this->instructorB)->allows('update', $this->courseDraftA));

        // 3. Gestor corporativo NÃO pode alterar conteúdo didático de terceiros
        $this->assertFalse(Gate::forUser($this->managerA)->allows('update', $this->courseDraftA));

        // 4. Aluno comum não pode alterar
        $this->assertFalse(Gate::forUser($this->studentA)->allows('update', $this->courseDraftA));

        // 5. Super Admin tem permissão global
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $this->courseDraftA));
    }

    public function test_course_policy_delete_requires_author_and_strictly_blocks_deletion_if_certificates_exist(): void
    {
        // 1. Autor pode deletar curso sem certificados
        $this->assertTrue(Gate::forUser($this->instructorA)->allows('delete', $this->courseDraftA));

        // 2. Terceiros são proibidos
        $this->assertFalse(Gate::forUser($this->instructorB)->allows('delete', $this->courseDraftA));
        $this->assertFalse(Gate::forUser($this->managerA)->allows('delete', $this->courseDraftA));

        // 3. Emissão de certificado histórico para o curso
        Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 120,
            'snapshot' => ['course_title' => $this->coursePublishedA->title],
            'issued_at' => now(),
        ]);

        // 4. Com certificados emitidos, a exclusão é PROIBIDA na Policy até mesmo para o Super Admin
        $this->assertFalse(
            Gate::forUser($this->instructorA)->allows('delete', $this->coursePublishedA),
            'Autor não pode excluir curso com certificados emitidos'
        );
        $this->assertFalse(
            Gate::forUser($this->admin)->allows('delete', $this->coursePublishedA),
            'Super Admin não pode excluir curso com certificados emitidos pela Policy'
        );

        // 5. Tentativa de exclusão direta via model lança DomainException
        $this->expectException(DomainException::class);
        $this->coursePublishedA->delete();
    }

    public function test_company_policy_enforces_active_management_membership(): void
    {
        // 1. Gestor com vínculo ativo na Empresa A pode gerenciar suas operações
        $this->assertTrue(Gate::forUser($this->managerA)->allows('manageEmployees', $this->companyA));
        $this->assertTrue(Gate::forUser($this->managerA)->allows('manageTracks', $this->companyA));
        $this->assertTrue(Gate::forUser($this->managerA)->allows('manageBilling', $this->companyA));
        $this->assertTrue(Gate::forUser($this->managerA)->allows('viewSituationRoom', $this->companyA));
        $this->assertTrue(Gate::forUser($this->managerA)->allows('viewAiInsights', $this->companyA));

        // 2. Bloqueio Cross-Tenant: Gestor da Empresa A NÃO pode gerenciar a Empresa B
        $this->assertFalse(
            Gate::forUser($this->managerA)->allows('manageEmployees', $this->companyB),
            'Gestor da Empresa A não pode gerenciar a Empresa B'
        );
        $this->assertFalse(Gate::forUser($this->managerA)->allows('manageBilling', $this->companyB));

        // 3. Instrutor puro e aluno comum NÃO possuem autorização de gestão
        $this->assertFalse(Gate::forUser($this->instructorA)->allows('manageEmployees', $this->companyA));
        $this->assertFalse(Gate::forUser($this->studentA)->allows('manageBilling', $this->companyA));

        // 4. Super Admin possui bypass global
        $this->assertTrue(Gate::forUser($this->admin)->allows('manageEmployees', $this->companyA));
        $this->assertTrue(Gate::forUser($this->admin)->allows('manageEmployees', $this->companyB));
    }

    public function test_certificate_service_acquires_course_pessimistic_lock_and_synchronizes_emission(): void
    {
        // Configuração de curso com aula para carga horária
        $module = Module::create([
            'company_id' => $this->companyA->id,
            'course_id' => $this->coursePublishedA->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $this->companyA->id,
            'course_id' => $this->coursePublishedA->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'slug' => 'aula-1',
            'order' => 1,
            'duration_minutes' => 60,
        ]);

        // Matrícula 100% concluída para elegibilidade
        Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'enrolled_at' => now(),
        ]);

        $service = app(CertificateService::class);
        $certificate = $service->getOrCreateCertificate($this->studentA, $this->coursePublishedA);

        $this->assertNotNull($certificate);
        $this->assertEquals($this->coursePublishedA->id, $certificate->course_id);
        $this->assertEquals($this->studentA->id, $certificate->user_id);

        // Após emissão sob lock, o curso fica imutavelmente protegido contra exclusão
        $this->expectException(DomainException::class);
        $this->coursePublishedA->delete();
    }
}
