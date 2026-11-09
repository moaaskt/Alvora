<?php

use App\Http\Controllers\BillingReturnController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\PublicCourseController;
use App\Livewire\Admin\AdminBilling;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\CourseManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\EnrollmentManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\Company\CertificateTemplateList;
use App\Livewire\Company\CompanyBilling;
use App\Livewire\Company\EmployeeManager;
use App\Livewire\Company\TrackManager;
use App\Livewire\Instructor\CourseEditor;
use App\Livewire\Instructor\CourseList;
use App\Livewire\Instructor\CurriculumBuilder;
use App\Livewire\Instructor\QuizEditor;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\LessonPlayer;
use App\Livewire\Student\Catalog as StudentCatalog;
use App\Livewire\Student\QuizRunner;
use App\Livewire\Student\Tracks as StudentTracks;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Resolução segura de Tenant para a Vitrine Pública:
    // Identifica exclusivamente a empresa parceira de demonstração oficial (Acme Corporation Brasil).
    // Garante com rigor que cursos e dados privados de outros tenants jamais sejam vazados.
    $demoCnpj = config('app.demo_tenant_cnpj', '12.345.678/0001-90');
    $demoCompany = \App\Models\Company::where('cnpj', $demoCnpj)->first();

    $showcaseCourses = collect();
    $featuredInstructors = collect();
    $featuredTracks = collect();

    if ($demoCompany) {
        // Consulta explícita restrita a $demoCompany->id e status 'published'
        // Executada dentro do contexto de vitrine pública explicitamente autorizado pelo servidor
        $showcaseCourses = \App\Models\Course::runAsPublicShowcase(function () use ($demoCompany) {
            return \App\Models\Course::where('company_id', $demoCompany->id)
                ->where('status', 'published')
                ->select(['id', 'company_id', 'instructor_id', 'category_id', 'title', 'slug', 'description', 'thumbnail', 'status'])
                ->with([
                    'instructor' => fn ($query) => $query->select(['id', 'company_id', 'name', 'role', 'avatar']),
                    'category' => fn ($query) => $query->select(['id', 'name', 'slug']),
                ])
                ->withCount(['lessons', 'modules'])
                ->withSum('lessons as total_duration_minutes', 'duration_minutes')
                ->latest('id')
                ->take(6)
                ->get();
        });

        // Instrutores públicos atuantes nesses cursos de demonstração (somente nome e id)
        $instructorIds = $showcaseCourses->pluck('instructor_id')->filter()->unique();
        if ($instructorIds->isNotEmpty()) {
            $featuredInstructors = \App\Models\User::where('company_id', $demoCompany->id)
                ->whereIn('id', $instructorIds)
                ->where('role', \App\Enums\UserRole::INSTRUCTOR)
                ->select(['id', 'company_id', 'name', 'role', 'avatar', 'job_title', 'bio'])
                ->get();
        }

        // Trilhas ativas associadas à empresa demonstrativa
        $featuredTracks = \App\Models\LearningTrack::where('company_id', $demoCompany->id)
            ->select(['id', 'company_id', 'title', 'slug', 'description', 'department', 'is_mandatory'])
            ->withCount('courses')
            ->take(4)
            ->get();
    }

    return view('landing', [
        'showcaseCourses' => $showcaseCourses,
        'featuredInstructors' => $featuredInstructors,
        'featuredTracks' => $featuredTracks,
    ]);
})->name('home');

// Detalhes Públicos do Treinamento Corporativo (Sem exigência de login)
Route::get('/cursos/{slug}', [PublicCourseController::class, 'show'])->name('courses.public.show');

Route::get('/planos', function () {
    return view('pricing');
})->name('pricing');

// Validação pública de autenticidade de certificados (sem login)
Route::get('/validar-certificado/{code}', [CertificateController::class, 'verify'])
    ->middleware('throttle:60,1')
    ->name('certificates.verify');

Route::middleware(['auth', 'subscription.active'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $companyId = $user->company_id;

        if ($user->isStudent() && !$user->canManageCompany($companyId ?? 0) && !$user->canTeachInContext($companyId)) {
            return redirect()->route('student.dashboard');
        }

        if ($user->isAlvoraAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $user = auth()->user();
        $isGlobalAdmin = $user->isAlvoraAdmin();
        $companyId = $user->company_id;
        $company = $user->company;

        $coursesQuery = \App\Models\Course::query();
        if (!$isGlobalAdmin) {
            $coursesQuery->where('company_id', $companyId);
        }

        $courses = $coursesQuery
            ->with(['instructor', 'company'])
            ->withCount(['lessons', 'modules', 'enrollments'])
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->latest('id')
            ->get();

        $totalCourses = $courses->count();
        $publishedCourses = $courses->where('status', 'published')->count();
        $draftCourses = $courses->where('status', 'draft')->count();
        $archivedCourses = $courses->where('status', 'archived')->count();

        $studentsQuery = \App\Models\User::where('role', \App\Enums\UserRole::STUDENT);
        if (!$isGlobalAdmin) {
            $studentsQuery->where('company_id', $companyId);
        }

        $students = $studentsQuery
            ->with(['latestRiskScore', 'interventions'])
            ->get();

        $totalStudents = $students->count();
        $highRiskStudents = $students->filter(fn($s) => $s->latestRiskScore?->isHighRisk());
        $mediumRiskStudents = $students->filter(fn($s) => $s->latestRiskScore?->isMediumRisk());
        $lowRiskStudents = $students->filter(fn($s) => $s->latestRiskScore?->isLowRisk());

        $avgEvasionScore = $totalStudents > 0
            ? round($students->avg(fn($s) => $s->latestRiskScore?->score ?? 0))
            : 0;

        $interventionsQuery = \App\Models\AiIntervention::query();
        if (!$isGlobalAdmin) {
            $interventionsQuery->where('company_id', $companyId);
        }

        $recentInterventions = (clone $interventionsQuery)
            ->with(['user', 'recommendedLesson'])
            ->latest('id')
            ->take(5)
            ->get();

        $interventionsCount = (clone $interventionsQuery)->count();

        return view('dashboard', [
            'user' => $user,
            'company' => $company,
            'courses' => $courses,
            'totalCourses' => $totalCourses,
            'publishedCourses' => $publishedCourses,
            'draftCourses' => $draftCourses,
            'archivedCourses' => $archivedCourses,
            'students' => $students,
            'totalStudents' => $totalStudents,
            'highRiskCount' => $highRiskStudents->count(),
            'mediumRiskCount' => $mediumRiskStudents->count(),
            'lowRiskCount' => $lowRiskStudents->count(),
            'avgEvasionScore' => $avgEvasionScore,
            'recentInterventions' => $recentInterventions,
            'interventionsCount' => $interventionsCount,
        ]);
    })->name('dashboard');

    // Portal do Aluno / Colaborador
    Route::get('/aluno/dashboard', StudentDashboard::class)->name('student.dashboard');
    Route::get('/aluno/trilhas', StudentTracks::class)->name('student.tracks');
    Route::get('/aluno/catalogo', StudentCatalog::class)->name('student.catalog');
    Route::get('/aluno/cursos/{course:slug}/aulas/{lesson?}', LessonPlayer::class)->name('student.player');
    Route::get('/aluno/cursos/{course:slug}/certificado', [CertificateController::class, 'download'])->name('student.certificate');
    Route::get('/aluno/quizzes/{quiz}', QuizRunner::class)->name('student.quiz');

    // Portal Corporativo da Empresa (Gestão de RH, Colaboradores, Trilhas, Assinatura e Métricas)
    Route::middleware('can.manage-company')->prefix('empresa')->group(function () {
        Route::get('/colaboradores', EmployeeManager::class)->name('employees.index');
        Route::get('/trilhas', TrackManager::class)->name('company.tracks.index');
        Route::get('/faturamento', CompanyBilling::class)->name('company.billing');
        Route::prefix('faturamento')->name('company.billing.')->group(function () {
            Route::get('/sucesso', [BillingReturnController::class, 'success'])->name('success');
            Route::get('/pendente', [BillingReturnController::class, 'pending'])->name('pending');
            Route::get('/erro', [BillingReturnController::class, 'failure'])->name('failure');
        });
        Route::get('/sala-de-situacao', \App\Livewire\Company\SituationRoom::class)->name('company.situation-room');
        Route::get('/insights-executivos', \App\Livewire\Company\AiExecutiveInsights::class)->name('company.ai-insights');
    });

    // Retrocompatibilidade: Redirecionamentos de /instrutor/* (gestão corporativa antiga) para o domínio canônico /empresa/*
    Route::redirect('/instrutor/colaboradores', '/empresa/colaboradores');
    Route::redirect('/instrutor/trilhas', '/empresa/trilhas');
    Route::redirect('/instrutor/faturamento', '/empresa/faturamento');
    Route::prefix('instrutor/faturamento')->group(function () {
        Route::redirect('/sucesso', '/empresa/faturamento/sucesso');
        Route::redirect('/pendente', '/empresa/faturamento/pendente');
        Route::redirect('/erro', '/empresa/faturamento/erro');
    });
    Route::redirect('/instrutor/sala-de-situacao', '/empresa/sala-de-situacao');
    Route::redirect('/instrutor/insights-executivos', '/empresa/insights-executivos');
    Route::redirect('/empresa/certificados/templates', '/instrutor/certificados/templates');

    // Super Admin Alvora - Governança Global, Cursos e Operações
    Route::middleware('role:admin_alvora')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/categorias', CategoryManager::class)->name('categories.index');
        Route::get('/matriculas', EnrollmentManager::class)->name('enrollments.index');
        Route::get('/matriculas/novo', EnrollmentManager::class)->name('enrollments.create');
        Route::get('/faturamento', AdminBilling::class)->name('billing');
        Route::get('/cursos', CourseManager::class)->name('courses.index');
        Route::get('/cursos/novo', CourseEditor::class)->name('courses.create');
        Route::get('/cursos/{course}/editar', CourseEditor::class)->name('courses.edit');
        Route::get('/cursos/{course}/curriculo', CurriculumBuilder::class)->name('courses.curriculum');
        Route::get('/certificados', CertificateTemplateList::class)->name('certificates.index');
        Route::get('/usuarios', UserManager::class)->name('users.index');
        Route::get('/usuarios/administradores', UserManager::class)->name('users.admins');
        Route::get('/usuarios/instrutores', UserManager::class)->name('users.instructors');
        Route::get('/usuarios/estudantes', UserManager::class)->name('users.students');
    });

    // Rotas de Gestão Didática de Conteúdo (Instrutor / Pedagógico)
    Route::middleware('can.teach')->prefix('instrutor')->name('instructor.')->group(function () {
        Route::get('/cursos', CourseList::class)->name('courses.index');
        Route::get('/cursos/novo', CourseEditor::class)->name('courses.create');
        Route::get('/cursos/{course}/editar', CourseEditor::class)->name('courses.edit');
        Route::get('/cursos/{course}/curriculo', CurriculumBuilder::class)->name('courses.curriculum');
        Route::get('/cursos/{course}/quizzes/novo', QuizEditor::class)->name('courses.quizzes.create');
        Route::get('/cursos/{course}/quizzes/{quiz}/editar', QuizEditor::class)->name('courses.quizzes.edit');
        Route::get('/quizzes/{quiz}/editar', QuizEditor::class)->name('quizzes.edit');
    });

    // Central de Configurações do Usuário (Navegação interna no Dashboard)
    Route::prefix('configuracoes')->name('settings.')->group(function () {
        Route::get('/perfil', \App\Livewire\Settings\Profile::class)->name('profile');
        Route::get('/seguranca', \App\Livewire\Settings\Security::class)->name('security');
        Route::get('/notificacoes', \App\Livewire\Settings\Notifications::class)->name('notifications');
    });

    // Gestão e Backend do Editor de Templates de Certificados
    Route::middleware('role:instructor,company_admin')->prefix('instrutor/certificados/templates')->name('instructor.certificate-templates.')->group(function () {
        Route::get('/', \App\Livewire\Company\CertificateTemplateList::class)->name('index');
        Route::get('/{template}/editar/{version?}', \App\Livewire\Company\CertificateTemplateEditor::class)->name('edit');
        Route::get('/{template}/background/{version?}', [\App\Http\Controllers\Company\CertificateTemplateController::class, 'background'])->name('background');
        Route::post('/preview', [\App\Http\Controllers\Company\CertificateTemplateController::class, 'preview'])
            ->middleware('throttle:60,1')
            ->name('preview');
        Route::post('/{template}/drafts', [\App\Http\Controllers\Company\CertificateTemplateController::class, 'createDraftVersion'])
            ->name('drafts.create');
        Route::put('/{template}/drafts/{version}', [\App\Http\Controllers\Company\CertificateTemplateController::class, 'saveDraft'])
            ->name('drafts.update');
        Route::post('/{template}/versions/{version}/publish', [\App\Http\Controllers\Company\CertificateTemplateController::class, 'publish'])
            ->name('versions.publish');
    });
});

require __DIR__.'/auth.php';
