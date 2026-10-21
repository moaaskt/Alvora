<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\EvasionRiskAlertEvent;
use App\Events\NewInterventionDispatchedEvent;
use App\Models\AiIntervention;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\RiskScore;
use App\Models\User;
use App\Services\EvasionInterventionService;
use App\Services\EvasionRiskCalculatorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RealtimeAndQueueEventsTest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Company $otherCompany;
    protected User $companyAdmin;
    protected User $otherAdmin;
    protected User $alvoraAdmin;
    protected User $student;
    protected User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $uniq = uniqid();

        $this->company = Company::create(['name' => "Tech Corp {$uniq}", 'status' => 'active']);
        $this->otherCompany = Company::create(['name' => "Acme Corp {$uniq}", 'status' => 'active']);

        $this->companyAdmin = User::create([
            'name' => 'Gestor RH',
            'email' => "rh_{$uniq}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $this->otherAdmin = User::create([
            'name' => 'Gestor Acme',
            'email' => "rh_{$uniq}@acme.com",
            'password' => bcrypt('password'),
            'company_id' => $this->otherCompany->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $this->alvoraAdmin = User::create([
            'name' => 'Admin Alvora Master',
            'email' => "admin_{$uniq}@alvora.local",
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => UserRole::ADMIN_ALVORA,
        ]);

        $this->student = User::create([
            'name' => 'Ana Clara',
            'email' => "anaclara_{$uniq}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
        ]);

        $this->otherStudent = User::create([
            'name' => 'Lucas Lima',
            'email' => "lucas_{$uniq}@techcorp.com",
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
        ]);
    }

    public function test_evasion_risk_alert_event_is_dispatched_when_student_enters_high_risk(): void
    {
        Event::fake([EvasionRiskAlertEvent::class, NewInterventionDispatchedEvent::class]);

        $uniq = uniqid();

        // Força inatividade de 20 dias
        $this->student->created_at = now()->subDays(25);
        $this->student->updated_at = now()->subDays(20);
        $this->student->saveQuietly();

        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->companyAdmin->id,
            'title' => "Curso Realtime {$uniq}",
            'slug' => "curso-realtime-{$uniq}",
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $this->company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $this->company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 1',
            'order' => 1,
            'duration_minutes' => 20,
        ]);

        $enrollment = Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()->subDays(20),
        ]);
        $enrollment->created_at = now()->subDays(20);
        $enrollment->saveQuietly();

        $quiz = \App\Models\Quiz::create([
            'company_id' => $this->company->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Inicial',
            'passing_score' => 70,
        ]);

        \App\Models\QuizAttempt::create([
            'company_id' => $this->company->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'score' => 10,
            'passed' => false,
        ]);

        $service = app(EvasionRiskCalculatorService::class);
        $riskScore = $service->calculateForUser($this->student);

        $this->assertGreaterThanOrEqual(70, $riskScore->score);

        Event::assertDispatched(EvasionRiskAlertEvent::class, function (EvasionRiskAlertEvent $event) use ($riskScore) {
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn($c) => $c->name, $channels);

            return $event->riskScoreId === $riskScore->id &&
                $event->companyId === $this->company->id &&
                $event->userId === $this->student->id &&
                $event->studentName === 'Ana Clara' &&
                $event->score === $riskScore->score &&
                $event->level === 'high' &&
                in_array('private-company.' . $this->company->id, $channelNames);
        });
    }

    public function test_new_intervention_dispatched_event_is_broadcast_when_intervention_is_created(): void
    {
        Event::fake([NewInterventionDispatchedEvent::class]);

        $uniq = uniqid();

        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->companyAdmin->id,
            'title' => "Curso Seguranca {$uniq}",
            'slug' => "curso-seguranca-{$uniq}",
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $this->company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $this->company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Introdução à Segurança',
            'order' => 1,
            'duration_minutes' => 10,
        ]);

        $riskScore = RiskScore::create([
            'company_id' => $this->company->id,
            'user_id' => $this->student->id,
            'score' => 85,
            'level' => 'high',
            'breakdown' => ['inactivity' => ['score' => 100]],
            'calculated_at' => now(),
        ]);

        $interventionService = app(EvasionInterventionService::class);
        $intervention = $interventionService->createManualIntervention(
            $this->student,
            'Olá Ana, temos uma aula recomendada!',
            $lesson->id
        );

        Event::assertDispatched(NewInterventionDispatchedEvent::class, function (NewInterventionDispatchedEvent $event) use ($intervention, $lesson) {
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn($c) => $c->name, $channels);

            return $event->interventionId === $intervention->id &&
                $event->userId === $this->student->id &&
                $event->recommendedLessonTitle === $lesson->title &&
                in_array('private-user.' . $this->student->id, $channelNames);
        });
    }

    public function test_company_channel_authorization_rules(): void
    {
        $companyChannelCallback = function (User $user, $companyId) {
            if ($user->isAlvoraAdmin()) {
                return true;
            }
            return (int) $user->company_id === (int) $companyId && $user->isCompanyAdmin();
        };

        // Gestor da mesma empresa pode ouvir
        $this->assertTrue($companyChannelCallback($this->companyAdmin, $this->company->id));

        // Gestor de outra empresa não pode ouvir
        $this->assertFalse($companyChannelCallback($this->otherAdmin, $this->company->id));

        // Aluno da mesma empresa não pode ouvir o canal administrativo da empresa
        $this->assertFalse($companyChannelCallback($this->student, $this->company->id));

        // Super Admin da Alvora tem acesso global
        $this->assertTrue($companyChannelCallback($this->alvoraAdmin, $this->company->id));
    }

    public function test_user_channel_authorization_rules(): void
    {
        $userChannelCallback = function (User $user, $userId) {
            return (int) $user->id === (int) $userId;
        };

        // Aluno tem acesso ao seu próprio canal
        $this->assertTrue($userChannelCallback($this->student, $this->student->id));

        // Outro aluno tem acesso negado ao canal alheio
        $this->assertFalse($userChannelCallback($this->otherStudent, $this->student->id));

        // Gestor não tem acesso ao canal privado do aluno
        $this->assertFalse($userChannelCallback($this->companyAdmin, $this->student->id));
    }

    public function test_horizon_dashboard_gate_restricts_access_to_alvora_admins(): void
    {
        // Admin Alvora deve ter permissão liberada
        $this->assertTrue(Gate::forUser($this->alvoraAdmin)->allows('viewHorizon'));

        // Gestor de Empresa deve ter permissão negada
        $this->assertFalse(Gate::forUser($this->companyAdmin)->allows('viewHorizon'));

        // Aluno deve ter permissão negada
        $this->assertFalse(Gate::forUser($this->student)->allows('viewHorizon'));

        // Guest deve ter permissão negada
        $this->assertFalse(Gate::allows('viewHorizon'));
    }
}
