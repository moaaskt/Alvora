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
        // Seleciona exclusivamente campos públicos, sem expor dados internos de negócio
        $showcaseCourses = \App\Models\Course::where('company_id', $demoCompany->id)
            ->where('status', 'published')
            ->select(['id', 'company_id', 'instructor_id', 'category_id', 'title', 'slug', 'description', 'thumbnail', 'status'])
            ->with([
                'instructor' => fn ($query) => $query->select(['id', 'company_id', 'name', 'role']),
                'category' => fn ($query) => $query->select(['id', 'name', 'slug']),
            ])
            ->withCount(['lessons', 'modules'])
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->latest('id')
            ->take(6)
            ->get();

        // Instrutores públicos atuantes nesses cursos de demonstração (somente nome e id)
        $instructorIds = $showcaseCourses->pluck('instructor_id')->filter()->unique();
        if ($instructorIds->isNotEmpty()) {
            $featuredInstructors = \App\Models\User::where('company_id', $demoCompany->id)
                ->whereIn('id', $instructorIds)
                ->where('role', \App\Enums\UserRole::INSTRUCTOR)
                ->select(['id', 'company_id', 'name', 'role'])
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
        if (auth()->user()->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        if (auth()->user()->isAlvoraAdmin()) {
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

    // Portal do Instrutor / Gestão Corporativa (o instrutor é a própria empresa)
    Route::get('/instrutor/colaboradores', EmployeeManager::class)
        ->middleware('role:instructor,company_admin')
        ->name('employees.index');

    Route::get('/instrutor/trilhas', TrackManager::class)
        ->middleware('role:instructor,company_admin')
        ->name('company.tracks.index');

    Route::get('/instrutor/faturamento', CompanyBilling::class)
        ->middleware('role:instructor,company_admin')
        ->name('company.billing');

    Route::get('/instrutor/sala-de-situacao', \App\Livewire\Company\SituationRoom::class)
        ->middleware('role:instructor,company_admin')
        ->name('company.situation-room');

    Route::get('/instrutor/insights-executivos', \App\Livewire\Company\AiExecutiveInsights::class)
        ->middleware('role:instructor,company_admin,admin_alvora')
        ->name('company.ai-insights');

    // Retorno do Checkout Mercado Pago
    Route::prefix('instrutor/faturamento')->name('company.billing.')->group(function () {
        Route::get('/sucesso', [BillingReturnController::class, 'success'])->name('success');
        Route::get('/pendente', [BillingReturnController::class, 'pending'])->name('pending');
        Route::get('/erro', [BillingReturnController::class, 'failure'])->name('failure');
    });

    // Redirecionamentos de retrocompatibilidade de /empresa/* para /instrutor/*
    Route::redirect('/empresa/colaboradores', '/instrutor/colaboradores');
    Route::redirect('/empresa/trilhas', '/instrutor/trilhas');
    Route::redirect('/empresa/faturamento', '/instrutor/faturamento');
    Route::redirect('/empresa/faturamento/sucesso', '/instrutor/faturamento/sucesso');
    Route::redirect('/empresa/faturamento/pendente', '/instrutor/faturamento/pendente');
    Route::redirect('/empresa/faturamento/erro', '/instrutor/faturamento/erro');
    Route::redirect('/empresa/sala-de-situacao', '/instrutor/sala-de-situacao');
    Route::redirect('/empresa/insights-executivos', '/instrutor/insights-executivos');
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

    // Rotas de Gestão de Conteúdo (Instrutor / RH)
    Route::middleware('role:instructor,company_admin')->prefix('instrutor')->name('instructor.')->group(function () {
        Route::get('/cursos', CourseList::class)->name('courses.index');
        Route::get('/cursos/novo', CourseEditor::class)->name('courses.create');
        Route::get('/cursos/{course}/editar', CourseEditor::class)->name('courses.edit');
        Route::get('/cursos/{course}/curriculo', CurriculumBuilder::class)->name('courses.curriculum');
        Route::get('/cursos/{course}/quizzes/novo', QuizEditor::class)->name('courses.quizzes.create');
        Route::get('/cursos/{course}/quizzes/{quiz}/editar', QuizEditor::class)->name('courses.quizzes.edit');
        Route::get('/quizzes/{quiz}/editar', QuizEditor::class)->name('quizzes.edit');
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
