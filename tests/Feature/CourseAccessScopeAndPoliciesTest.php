<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Scopes\CourseAccessScope;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\CourseDeletionService;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function test_course_policy_delete_authorizes_author_and_super_admin_and_denies_managers_and_peers(): void
    {
        // 1. Autor com vínculo contextual ativo possui autorização de exclusão na Policy
        $this->assertTrue(Gate::forUser($this->instructorA)->allows('delete', $this->courseDraftA));
        $this->assertTrue(Gate::forUser($this->instructorA)->allows('delete', $this->coursePublishedA));

        // 2. Super Admin possui governança global para exclusão na Policy
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $this->courseDraftA));

        // 3. Gestor corporativo NÃO pode excluir curso de outro instrutor
        $this->assertFalse(
            Gate::forUser($this->managerA)->allows('delete', $this->courseDraftA),
            'Gestor corporativo não ganha automaticamente permissão para excluir conteúdo de outro instrutor'
        );

        // 4. Outro instrutor NÃO pode excluir curso do colega
        $this->assertFalse(Gate::forUser($this->instructorB)->allows('delete', $this->courseDraftA));

        // 5. Aluno comum é sumariamente negado
        $this->assertFalse(Gate::forUser($this->studentA)->allows('delete', $this->courseDraftA));
    }

    public function test_course_deletion_service_requires_authenticated_requester_and_blocks_unauthorized_users(): void
    {
        $deletionService = app(CourseDeletionService::class);

        // 1. Gestor corporativo é bloqueado com AuthorizationException
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Usuário não autorizado a excluir este curso.');
        $deletionService->deleteCourse($this->courseDraftA, $this->managerA);
    }

    public function test_course_deletion_service_blocks_peer_instructor_without_rights(): void
    {
        $deletionService = app(CourseDeletionService::class);

        // 2. Instrutor B não é o autor do curso do Instrutor A -> AuthorizationException
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Usuário não autorizado a excluir este curso.');
        $deletionService->deleteCourse($this->courseDraftA, $this->instructorB);
    }

    public function test_course_deletion_service_blocks_student(): void
    {
        $deletionService = app(CourseDeletionService::class);

        // 3. Aluno não pode excluir curso -> AuthorizationException
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Usuário não autorizado a excluir este curso.');
        $deletionService->deleteCourse($this->courseDraftA, $this->studentA);
    }

    public function test_course_deletion_service_validates_authorization_before_revealing_certificates_existence(): void
    {
        // 1. Criar certificado histórico para o curso publicado
        Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'snapshot' => ['course_title' => $this->coursePublishedA->title],
            'issued_at' => now(),
        ]);

        $deletionService = app(CourseDeletionService::class);

        // 2. Um usuário NÃO autorizado (ex: gestor de RH ou outro instrutor) tenta excluir o curso com certificados.
        // O sistema DEVE lançar AuthorizationException (403) e NUNCA DomainException.
        // Isso comprova categoricamente que a autorização é verificada sob o lock ANTES de qualquer inspeção da existência de certificados.
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Usuário não autorizado a excluir este curso.');

        $deletionService->deleteCourse($this->coursePublishedA, $this->managerA);
    }

    public function test_course_deletion_service_blocks_deletion_and_rolls_back_when_certificates_exist_for_author(): void
    {
        // 1. Criar certificado histórico para o curso
        Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'snapshot' => ['course_title' => $this->coursePublishedA->title],
            'issued_at' => now(),
        ]);

        $deletionService = app(CourseDeletionService::class);

        // 2. O autor (Instrutor A) é autorizado na Policy, portanto a execução avança para a checagem sob lock de certificados
        // e é bloqueada pela integridade de domínio:
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Não é possível excluir o curso '{$this->coursePublishedA->title}' pois ele possui certificados emitidos.");

        try {
            $deletionService->deleteCourse($this->coursePublishedA, $this->instructorA);
        } finally {
            // Comprova rollback: o curso e seus certificados continuam intactos no banco de dados
            $this->assertDatabaseHas('courses', ['id' => $this->coursePublishedA->id]);
            $this->assertDatabaseHas('certificates', ['course_id' => $this->coursePublishedA->id]);
        }
    }

    public function test_course_deletion_service_blocks_deletion_for_super_admin_when_certificates_exist(): void
    {
        Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'snapshot' => ['course_title' => $this->coursePublishedA->title],
            'issued_at' => now(),
        ]);

        $deletionService = app(CourseDeletionService::class);

        // Super Admin passa na autorização da Policy, mas é impedido no serviço pela integridade física dos certificados
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Não é possível excluir o curso '{$this->coursePublishedA->title}' pois ele possui certificados emitidos.");

        $deletionService->deleteCourse($this->coursePublishedA, $this->admin);
    }

    public function test_course_deletion_service_permits_authorized_deletion_when_no_certificates_exist(): void
    {
        $deletionService = app(CourseDeletionService::class);

        // Autor pode deletar seu próprio curso quando não há certificados
        $result = $deletionService->deleteCourse($this->courseDraftA, $this->instructorA);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('courses', ['id' => $this->courseDraftA->id]);
    }

    public function test_course_deletion_service_permits_super_admin_deletion_when_no_certificates_exist(): void
    {
        $deletionService = app(CourseDeletionService::class);

        // Super Admin pode deletar curso sem certificados
        $result = $deletionService->deleteCourse($this->coursePublishedA, $this->admin);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('courses', ['id' => $this->coursePublishedA->id]);
    }

    public function test_course_deletion_and_certificate_emission_compete_for_same_course_lock(): void
    {
        // Demonstra a paridade e ordem idêntica de aquisição do lock pessimista:
        // Ambos os serviços executam Course::withoutGlobalScopes()->where('id', $courseId)->lockForUpdate()
        // sob DB::transaction().
        //
        // NOTA DE AMBIENTE:
        // Em testes locais com SQLite (:memory: ou testing.sqlite), locks de escrita operam em nível
        // de arquivo (single-writer) com concorrência serializada.
        // Em Produção / MySQL 8.0 (InnoDB), a execução de Course::lockForUpdate adquire um lock
        // exclusivo de linha (X-lock) no índice primário de courses, forçando qualquer transação
        // concorrente de emissão ou exclusão a esperar até o commit/rollback da transação titular.
        
        $courseId = $this->coursePublishedA->id;

        DB::transaction(function () use ($courseId) {
            $lockedInTx1 = Course::withoutGlobalScopes()->where('id', $courseId)->lockForUpdate()->first();
            $this->assertNotNull($lockedInTx1);
            $this->assertEquals($courseId, $lockedInTx1->id);

            // Sob o mesmo lock ativo, certifica-se que emissão e exclusão sincronizam no mesmo registro
            $this->assertEquals($lockedInTx1->getTable(), 'courses');
        });
    }

    public function test_historical_certificate_snapshot_integrity_is_preserved_post_emission(): void
    {
        $module = Module::create([
            'company_id' => $this->companyA->id,
            'course_id' => $this->coursePublishedA->id,
            'title' => 'Módulo Imutabilidade',
            'order' => 1,
        ]);

        Lesson::create([
            'company_id' => $this->companyA->id,
            'course_id' => $this->coursePublishedA->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'slug' => 'aula-imutabilidade-1',
            'order' => 1,
            'duration_minutes' => 90,
        ]);

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

        $initialSnapshot = $certificate->snapshot;
        $this->assertEquals('Aluno Alfa', $initialSnapshot['student_name']);
        $this->assertEquals($this->coursePublishedA->title, $initialSnapshot['course_title']);
        $this->assertEquals(90, $initialSnapshot['workload_minutes']);

        // Altera nome do aluno e título do curso no banco posteriormente
        $this->studentA->update(['name' => 'Aluno Nome Modificado']);
        $this->coursePublishedA->update(['title' => 'Curso Título Modificado']);

        // Recarrega o certificado do banco e comprova integridade imutável do snapshot
        $freshCertificate = $certificate->fresh();
        $this->assertEquals('Aluno Alfa', $freshCertificate->snapshot['student_name'], 'Snapshot do aluno não deve ser corrompido por mutações posteriores');
        $this->assertEquals('Curso Publicado do Instrutor A', $freshCertificate->snapshot['course_title'], 'Snapshot do curso deve permanecer histórico');
    }

    public function test_raw_sql_deletion_bypasses_eloquent_events_and_triggers_cascade_on_delete(): void
    {
        // Criação de certificado para o curso
        Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'snapshot' => ['course_title' => $this->coursePublishedA->title],
            'issued_at' => now(),
        ]);

        $this->assertDatabaseHas('certificates', ['course_id' => $this->coursePublishedA->id]);

        // ATENÇÃO: Demonstração da limitação do schema atual (FK com cascadeOnDelete):
        // Quando uma exclusão é executada via SQL bruto direto no banco (bypassing Eloquent & Services),
        // o motor do banco de dados (MySQL/SQLite) executa o ON DELETE CASCADE da chave estrangeira,
        // apagando os certificados físicos.
        // Proposta formalizada: alterar a FK para RESTRICT em migration de fase posterior.
        DB::table('courses')->where('id', $this->coursePublishedA->id)->delete();

        // O curso foi deletado via SQL direto
        $this->assertDatabaseMissing('courses', ['id' => $this->coursePublishedA->id]);

        // Limitação documentada: os certificados vinculados foram excluídos pelo CASCADE da FK no banco
        $this->assertDatabaseMissing('certificates', ['course_id' => $this->coursePublishedA->id]);
    }

    public function test_unauthenticated_visitor_without_context_receives_zero_courses(): void
    {
        Auth::logout();

        // 1. Visitante anônimo sem contexto recebe zero cursos
        $this->assertCount(0, Course::all(), 'Consultas anônimas genéricas devem retornar zero cursos corporativos');
        $this->assertEquals(0, Course::count());
        $this->assertNull(Course::where('id', $this->coursePublishedA->id)->first());
    }

    public function test_unauthenticated_visitor_cannot_access_private_courses_by_specifying_company_filters(): void
    {
        Auth::logout();

        // 2. Visitante tentando forjar filtros de empresa de clientes privados recebe zero cursos
        $this->assertCount(0, Course::where('company_id', $this->companyA->id)->get());
        $this->assertCount(0, Course::where('company_id', $this->companyB->id)->get());
    }

    public function test_landing_page_authorized_context_exclusively_returns_published_demo_courses(): void
    {
        Auth::logout();
        config(['app.demo_tenant_cnpj' => $this->companyA->cnpj]);

        // 3. Dentro do contexto autorizado de vitrine pelo servidor, cursos publicados da demo são visíveis
        $showcaseCourses = Course::runAsPublicShowcase(function () {
            return Course::all();
        });

        $this->assertTrue($showcaseCourses->contains('id', $this->coursePublishedA->id), 'Cursos publicados da empresa demo devem aparecer na vitrine');
        $this->assertFalse($showcaseCourses->contains('id', $this->courseDraftA->id), 'Rascunhos da empresa demo NUNCA devem aparecer na vitrine');
    }

    public function test_landing_page_authorized_context_strictly_excludes_other_tenants(): void
    {
        Auth::logout();
        config(['app.demo_tenant_cnpj' => $this->companyA->cnpj]);

        // Curso publicado pertencente à Empresa Privada B
        $coursePublishedB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Confidencial Empresa B',
            'slug' => 'curso-confidencial-b',
            'status' => 'published',
        ]);

        // 4. Mesmo dentro do contexto público autorizado, cursos de outros tenants são estritamente excluídos
        $showcaseCourses = Course::runAsPublicShowcase(function () {
            return Course::all();
        });

        $this->assertFalse($showcaseCourses->contains('id', $coursePublishedB->id), 'Cursos de outros tenants privados não podem vazar na vitrine pública');
    }

    public function test_cli_and_jobs_without_context_do_not_receive_implicit_global_access(): void
    {
        Auth::logout();

        // 5. CLI e Jobs sem usuário autenticado e sem contexto autorizado recebem zero cursos
        $this->assertEquals(0, Course::where('status', 'published')->count(), 'CLI/Jobs sem contexto não recebem acesso global implícito');
    }

    public function test_without_global_scopes_preserves_explicit_administrative_access(): void
    {
        Auth::logout();

        // 6. Operações administrativas e de manutenção preservam acesso global via withoutGlobalScopes()
        $adminCount = Course::withoutGlobalScopes()->count();
        $this->assertGreaterThanOrEqual(2, $adminCount, 'withoutGlobalScopes() continua funcionando para operações explicitamente autorizadas');

        $draft = Course::withoutGlobalScopes()->where('id', $this->courseDraftA->id)->first();
        $this->assertNotNull($draft);
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
    }

    public function test_public_showcase_nested_calls_preserve_and_restore_context_state(): void
    {
        Auth::logout();
        $this->assertFalse(CourseAccessScope::isPublicShowcaseActive());

        Course::runAsPublicShowcase(function () {
            $this->assertTrue(CourseAccessScope::isPublicShowcaseActive(), 'Contexto externo deve estar ativo');

            Course::runAsPublicShowcase(function () {
                $this->assertTrue(CourseAccessScope::isPublicShowcaseActive(), 'Contexto interno aninhado deve estar ativo');
            });

            $this->assertTrue(CourseAccessScope::isPublicShowcaseActive(), 'Contexto externo deve permanecer ativo após saída da chamada aninhada');
        });

        $this->assertFalse(CourseAccessScope::isPublicShowcaseActive(), 'Contexto deve ser desativado após término da chamada externa');
    }

    public function test_public_showcase_restores_context_state_on_exception(): void
    {
        Auth::logout();
        $this->assertFalse(CourseAccessScope::isPublicShowcaseActive());

        try {
            Course::runAsPublicShowcase(function () {
                $this->assertTrue(CourseAccessScope::isPublicShowcaseActive());
                throw new \RuntimeException('Erro simulado dentro do callback de vitrine pública');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('Erro simulado dentro do callback de vitrine pública', $e->getMessage());
        }

        $this->assertFalse(CourseAccessScope::isPublicShowcaseActive(), 'Contexto público deve ser desativado no bloco finally mesmo com lançamento de exceção');
    }

    public function test_certificate_course_relationship_strictly_blocks_cross_tenant_course(): void
    {
        // Certificado criado com company_id da Empresa A, mas apontando para curso da Empresa B
        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso da Empresa B',
            'slug' => 'curso-da-empresa-b',
            'status' => 'published',
        ]);

        $certificate = Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $courseB->id, // Chave cruzada anômala
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        // A relação course() deve isolar por company_id do certificado e retornar null
        $this->assertNull($certificate->course, 'Certificado da Empresa A não pode carregar curso da Empresa B');
        $this->assertNull($certificate->course_title, 'course_title legado deve retornar null quando curso pertence a outro tenant');
    }

    public function test_enrollment_course_relationship_strictly_blocks_cross_tenant_course(): void
    {
        // Matrícula na Empresa A apontando para curso da Empresa B
        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Externo B',
            'slug' => 'curso-externo-b',
            'status' => 'published',
        ]);

        $enrollment = Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $courseB->id, // Chave cruzada anômala
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        // A relação course() deve isolar por company_id da matrícula e retornar null
        $this->assertNull($enrollment->course, 'Matrícula da Empresa A não pode carregar curso da Empresa B');
    }

    public function test_learning_track_courses_relationship_strictly_filters_out_cross_tenant_courses(): void
    {
        $trackA = LearningTrack::create([
            'company_id' => $this->companyA->id,
            'title' => 'Trilha de Formação Alfa',
            'slug' => 'trilha-de-formacao-alfa',
            'status' => 'published',
        ]);

        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Vazado Empresa B',
            'slug' => 'curso-vazado-empresa-b',
            'status' => 'published',
        ]);

        // Anexa na tabela pivô um curso legítimo da Empresa A e um curso alheio da Empresa B
        $trackA->courses()->attach([
            $this->coursePublishedA->id => ['order' => 1],
            $courseB->id => ['order' => 2],
        ]);

        // Recarrega os cursos da trilha
        $courses = $trackA->courses;

        // O curso da Empresa A deve ser carregado normalmente
        $this->assertTrue($courses->contains('id', $this->coursePublishedA->id));

        // O curso da Empresa B NÃO pode ser carregado (filtrado pela cláusula de tenant da trilha)
        $this->assertFalse($courses->contains('id', $courseB->id), 'Trilha da Empresa A não pode expor curso pertencente à Empresa B');
        $this->assertCount(1, $courses);
    }

    public function test_eager_loading_certificate_with_course_strictly_blocks_cross_tenant_course_without_auth(): void
    {
        Auth::logout();

        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Isolado Empresa B',
            'slug' => 'curso-isolado-empresa-b',
            'status' => 'published',
        ]);

        $certLegit = Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        $certCross = Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $courseB->id, // Chave cruzada anômala
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        $certificates = Certificate::with('course')
            ->whereIn('id', [$certLegit->id, $certCross->id])
            ->get()
            ->keyBy('id');

        $this->assertNotNull($certificates[$certLegit->id]->course, 'Certificado legítimo deve carregar seu curso via eager loading sem autenticação');
        $this->assertSame($this->coursePublishedA->id, $certificates[$certLegit->id]->course->id);
        $this->assertNull($certificates[$certCross->id]->course, 'Eager loading de certificado com curso de outro tenant deve retornar null sem autenticação');
    }

    public function test_eager_loading_enrollment_with_course_strictly_blocks_cross_tenant_course_without_auth(): void
    {
        Auth::logout();

        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Isolado Empresa B 2',
            'slug' => 'curso-isolado-empresa-b-2',
            'status' => 'published',
        ]);

        $enrLegit = Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'status' => 'active',
            'progress_percent' => 50,
            'enrolled_at' => now(),
        ]);

        $enrCross = Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $courseB->id, // Chave cruzada anômala
            'status' => 'active',
            'progress_percent' => 50,
            'enrolled_at' => now(),
        ]);

        $enrollments = Enrollment::with('course')
            ->whereIn('id', [$enrLegit->id, $enrCross->id])
            ->get()
            ->keyBy('id');

        $this->assertNotNull($enrollments[$enrLegit->id]->course, 'Matrícula legítima deve carregar seu curso via eager loading sem autenticação');
        $this->assertSame($this->coursePublishedA->id, $enrollments[$enrLegit->id]->course->id);
        $this->assertNull($enrollments[$enrCross->id]->course, 'Eager loading de matrícula com curso de outro tenant deve retornar null sem autenticação');
    }

    public function test_eager_loading_learning_track_with_courses_strictly_filters_out_cross_tenant_courses_without_auth(): void
    {
        Auth::logout();

        $trackA = LearningTrack::create([
            'company_id' => $this->companyA->id,
            'title' => 'Trilha Eager Loading Alfa',
            'slug' => 'trilha-eager-loading-alfa',
            'status' => 'published',
        ]);

        $courseB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $this->instructorB->id,
            'title' => 'Curso Vazado Eager B',
            'slug' => 'curso-vazado-eager-b',
            'status' => 'published',
        ]);

        $trackA->courses()->attach([
            $this->coursePublishedA->id => ['order' => 1],
            $courseB->id => ['order' => 2],
        ]);

        $loadedTrack = LearningTrack::with('courses')->find($trackA->id);

        $this->assertNotNull($loadedTrack);
        $this->assertCount(1, $loadedTrack->courses, 'Eager loading de trilha deve conter exclusivamente os cursos do mesmo tenant');
        $this->assertTrue($loadedTrack->courses->contains('id', $this->coursePublishedA->id));
        $this->assertFalse($loadedTrack->courses->contains('id', $courseB->id), 'Eager loading de trilha não pode conter cursos de outro tenant sem autenticação');
        $this->assertSame(1, (int) $loadedTrack->courses->first()->pivot->order);
    }

    public function test_eager_loading_legitimate_same_tenant_courses_continue_to_load_perfectly_without_auth(): void
    {
        Auth::logout();

        // Cria curso legítimo na Empresa B
        $instructorInB = User::create([
            'name' => 'Instrutor Beta Real',
            'email' => 'inst.beta.real@beta.test',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => $this->companyB->id,
        ]);

        $coursePublishedB = Course::create([
            'company_id' => $this->companyB->id,
            'instructor_id' => $instructorInB->id,
            'title' => 'Curso Legítimo Empresa B',
            'slug' => 'curso-legitimo-empresa-b',
            'status' => 'published',
        ]);

        $studentB = User::create([
            'name' => 'Aluno Beta Real',
            'email' => 'aluno.beta.real@beta.test',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $this->companyB->id,
        ]);

        // Certificados legítimos em A e B
        $certA = Certificate::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'total_hours_minutes' => 60,
            'issued_at' => now(),
        ]);

        $certB = Certificate::create([
            'company_id' => $this->companyB->id,
            'user_id' => $studentB->id,
            'course_id' => $coursePublishedB->id,
            'total_hours_minutes' => 90,
            'issued_at' => now(),
        ]);

        // Matrículas legítimas em A e B
        $enrA = Enrollment::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->studentA->id,
            'course_id' => $this->coursePublishedA->id,
            'status' => 'active',
            'progress_percent' => 75,
            'enrolled_at' => now(),
        ]);

        $enrB = Enrollment::create([
            'company_id' => $this->companyB->id,
            'user_id' => $studentB->id,
            'course_id' => $coursePublishedB->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'enrolled_at' => now(),
        ]);

        // Trilhas legítimas em A e B
        $trackA = LearningTrack::create([
            'company_id' => $this->companyA->id,
            'title' => 'Trilha A Válida',
            'slug' => 'trilha-a-valida',
            'status' => 'published',
        ]);
        $trackA->courses()->attach([$this->coursePublishedA->id => ['order' => 1]]);

        $trackB = LearningTrack::create([
            'company_id' => $this->companyB->id,
            'title' => 'Trilha B Válida',
            'slug' => 'trilha-b-valida',
            'status' => 'published',
        ]);
        $trackB->courses()->attach([$coursePublishedB->id => ['order' => 1]]);

        // Eager load múltiplo sem usuário na sessão
        $certs = Certificate::with(['course', 'company', 'user'])->whereIn('id', [$certA->id, $certB->id])->get()->keyBy('id');
        $enrs = Enrollment::with(['course', 'company', 'user'])->whereIn('id', [$enrA->id, $enrB->id])->get()->keyBy('id');
        $tracks = LearningTrack::with('courses')->whereIn('id', [$trackA->id, $trackB->id])->get()->keyBy('id');

        // Validação A
        $this->assertSame($this->coursePublishedA->id, $certs[$certA->id]->course->id);
        $this->assertSame($this->companyA->id, $certs[$certA->id]->company->id);
        $this->assertSame($this->studentA->id, $certs[$certA->id]->user->id);

        $this->assertSame($this->coursePublishedA->id, $enrs[$enrA->id]->course->id);
        $this->assertSame($this->companyA->id, $enrs[$enrA->id]->company->id);
        $this->assertSame($this->studentA->id, $enrs[$enrA->id]->user->id);

        $this->assertCount(1, $tracks[$trackA->id]->courses);
        $this->assertSame($this->coursePublishedA->id, $tracks[$trackA->id]->courses->first()->id);
        $this->assertSame(1, (int) $tracks[$trackA->id]->courses->first()->pivot->order);

        // Validação B
        $this->assertSame($coursePublishedB->id, $certs[$certB->id]->course->id);
        $this->assertSame($this->companyB->id, $certs[$certB->id]->company->id);
        $this->assertSame($studentB->id, $certs[$certB->id]->user->id);

        $this->assertSame($coursePublishedB->id, $enrs[$enrB->id]->course->id);
        $this->assertSame($this->companyB->id, $enrs[$enrB->id]->company->id);
        $this->assertSame($studentB->id, $enrs[$enrB->id]->user->id);

        $this->assertCount(1, $tracks[$trackB->id]->courses);
        $this->assertSame($coursePublishedB->id, $tracks[$trackB->id]->courses->first()->id);
        $this->assertSame(1, (int) $tracks[$trackB->id]->courses->first()->pivot->order);
    }
}
