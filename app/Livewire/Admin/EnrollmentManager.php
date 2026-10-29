<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $companyFilter = 'all';
    public string $statusFilter = 'all';
    public string $periodFilter = 'all';

    // Modo de Exibição da Página: 'history' (Histórico) ou 'create' (Atribuição Manual no Painel)
    public string $viewMode = 'history';
    public string $studentSearch = '';
    public ?int $selectedUserId = null;
    public ?int $selectedCourseId = null;
    public bool $isLifetime = true;
    public ?string $expiresAt = null;
    public bool $notifyStudent = true;

    // Confirmação de Revogação
    public bool $showRevokeModal = false;
    public ?int $revokingEnrollmentId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'companyFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
        'periodFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (request()->routeIs('admin.enrollments.create') || request()->query('action') === 'create') {
            $this->switchToCreate();
        } else {
            $this->viewMode = 'history';
        }
    }

    public function switchToCreate(): void
    {
        $this->resetValidation();
        $this->selectedUserId = null;
        $this->selectedCourseId = null;
        $this->studentSearch = '';
        $this->isLifetime = true;
        $this->expiresAt = now()->addMonths(6)->format('Y-m-d');
        $this->notifyStudent = true;
        $this->viewMode = 'create';
    }

    public function switchToHistory(): void
    {
        $this->resetValidation();
        $this->viewMode = 'history';
    }

    // Mantido para compatibilidade
    public function openCreateModal(): void
    {
        $this->switchToCreate();
    }

    public function closeCreateModal(): void
    {
        $this->switchToHistory();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->companyFilter = 'all';
        $this->statusFilter = 'all';
        $this->periodFilter = 'all';
        $this->resetPage();
    }

    public function selectStudent(int $userId): void
    {
        $this->selectedUserId = $userId;
    }

    public function enrollStudent(): void
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'selectedCourseId' => 'required|exists:courses,id',
            'expiresAt' => $this->isLifetime ? 'nullable' : 'required|date|after:today',
        ], [
            'selectedUserId.required' => 'Selecione o estudante que receberá o acesso.',
            'selectedUserId.exists' => 'O estudante selecionado é inválido.',
            'selectedCourseId.required' => 'Selecione o treinamento corporativo.',
            'selectedCourseId.exists' => 'O curso selecionado é inválido.',
            'expiresAt.required' => 'Informe a data limite de vigência do acesso.',
            'expiresAt.after' => 'A data de expiração deve ser posterior a hoje.',
        ]);

        $user = User::withoutGlobalScopes()->findOrFail($this->selectedUserId);
        $course = Course::withoutGlobalScopes()->findOrFail($this->selectedCourseId);

        // Validação estrita contra duplicidade de matrícula ativa
        $existingEnrollment = Enrollment::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'active' || $existingEnrollment->status === 'completed') {
                $this->dispatch('toast', 
                    message: "O colaborador {$user->name} já possui uma matrícula ativa ou concluída no curso '{$course->title}'.", 
                    type: 'error'
                );
                return;
            }

            // Se existia mas estava cancelada/dropped, reativa
            $existingEnrollment->update([
                'status' => 'active',
                'enrolled_at' => now(),
                'expires_at' => $this->isLifetime ? null : Carbon::parse($this->expiresAt)->endOfDay(),
                'enrolled_by_id' => Auth::id(),
            ]);

            $this->closeCreateModal();
            $this->dispatch('toast', 
                message: "Matrícula de {$user->name} no curso '{$course->title}' reativada com sucesso!", 
                type: 'success'
            );
            return;
        }

        Enrollment::create([
            'company_id' => $course->company_id ?: ($user->company_id ?: 1),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 0,
            'enrolled_at' => now(),
            'expires_at' => $this->isLifetime ? null : Carbon::parse($this->expiresAt)->endOfDay(),
            'enrolled_by_id' => Auth::id(),
        ]);

        $this->closeCreateModal();
        $this->dispatch('toast', 
            message: "Matrícula realizada com sucesso para {$user->name}!", 
            type: 'success'
        );
    }

    public function confirmRevoke(int $enrollmentId): void
    {
        $this->revokingEnrollmentId = $enrollmentId;
        $this->showRevokeModal = true;
    }

    public function cancelRevoke(): void
    {
        $this->showRevokeModal = false;
        $this->revokingEnrollmentId = null;
    }

    public function revokeEnrollment(): void
    {
        if (!$this->revokingEnrollmentId) {
            return;
        }

        $enrollment = Enrollment::withoutGlobalScopes()
            ->with(['user', 'course'])
            ->findOrFail($this->revokingEnrollmentId);

        $studentName = $enrollment->user?->name ?? 'Aluno';
        $courseTitle = $enrollment->course?->title ?? 'Curso';

        // Atualiza status para 'dropped' (revogado/cancelado)
        $enrollment->update([
            'status' => 'dropped',
        ]);

        $this->showRevokeModal = false;
        $this->revokingEnrollmentId = null;

        $this->dispatch('toast', 
            message: "Acesso de {$studentName} ao curso '{$courseTitle}' revogado com sucesso.", 
            type: 'info'
        );
    }

    public function deleteEnrollmentPermanently(int $enrollmentId): void
    {
        $enrollment = Enrollment::withoutGlobalScopes()
            ->with(['user', 'course'])
            ->findOrFail($enrollmentId);

        $studentName = $enrollment->user?->name ?? 'Aluno';
        $courseTitle = $enrollment->course?->title ?? 'Curso';

        $enrollment->delete();

        $this->dispatch('toast', 
            message: "Registro de matrícula de {$studentName} em '{$courseTitle}' removido permanentemente.", 
            type: 'info'
        );
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alvora_matriculas_historico_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                '# ID',
                'Nome do Aluno',
                'E-mail do Aluno',
                'Treinamento / Curso',
                'Empresa (Tenant)',
                'Data da Matrícula',
                'Data de Validade',
                'Tipo de Licença',
                'Progresso (%)',
                'Status',
                'Responsável pela Matrícula',
            ], ';');

            $enrollments = Enrollment::withoutGlobalScopes()
                ->with(['user', 'course', 'company', 'enrolledBy'])
                ->latest('id')
                ->get();

            foreach ($enrollments as $e) {
                fputcsv($handle, [
                    $e->id,
                    $e->user?->name ?? 'N/A',
                    $e->user?->email ?? 'N/A',
                    $e->course?->title ?? 'N/A',
                    $e->company?->name ?? 'Global HQ',
                    $e->enrolled_at ? $e->enrolled_at->format('d/m/Y H:i') : '-',
                    $e->expires_at ? $e->expires_at->format('d/m/Y') : 'Acesso Vitalício',
                    $e->isLifetime() ? 'Vitalício' : 'Prazo Estipulado',
                    $e->progress_percent . '%',
                    $e->status === 'active' ? 'Ativo' : ($e->status === 'completed' ? 'Concluído' : ($e->status === 'dropped' ? 'Revogado' : $e->status)),
                    $e->enrolledBy?->name ?? 'Sistema / Gateway',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render()
    {
        // 1. Métricas Globais (KPIs)
        $totalEnrollments = Enrollment::withoutGlobalScopes()->count();
        $activeEnrollments = Enrollment::withoutGlobalScopes()->where('status', 'active')->count();
        $lifetimeEnrollments = Enrollment::withoutGlobalScopes()->whereNull('expires_at')->count();
        $monthEnrollments = Enrollment::withoutGlobalScopes()
            ->where('enrolled_at', '>=', now()->startOfMonth())
            ->count();

        // 2. Filtros e Consulta de Histórico
        $companies = Company::orderBy('name')->get();

        $query = Enrollment::withoutGlobalScopes()
            ->with(['user', 'course.category', 'company', 'enrolledBy']);

        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', $term)
                       ->orWhere('email', 'like', $term);
                })->orWhereHas('course', function ($cq) use ($term) {
                    $cq->where('title', 'like', $term);
                });
            });
        }

        if ($this->companyFilter !== 'all') {
            $query->where('company_id', $this->companyFilter);
        }

        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            } elseif ($this->statusFilter === 'lifetime') {
                $query->whereNull('expires_at');
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if ($this->periodFilter !== 'all') {
            if ($this->periodFilter === '30days') {
                $query->where('enrolled_at', '>=', now()->subDays(30));
            } elseif ($this->periodFilter === 'quarter') {
                $query->where('enrolled_at', '>=', now()->subMonths(3));
            } elseif ($this->periodFilter === 'year') {
                $query->where('enrolled_at', '>=', now()->subYear());
            }
        }

        $enrollments = $query->latest('id')->paginate(10);

        // 3. Dados para Atribuição Manual de Matrícula (Página Dedicada)
        $availableStudents = collect();
        if ($this->viewMode === 'create') {
            $studentQuery = User::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('role', 'student')
                      ->orWhere('role', 'instructor')
                      ->orWhere('role', 'company_admin');
                });

            if (!empty($this->studentSearch)) {
                $stTerm = '%' . trim($this->studentSearch) . '%';
                $studentQuery->where(function ($sq) use ($stTerm) {
                    $sq->where('name', 'like', $stTerm)
                       ->orWhere('email', 'like', $stTerm);
                });
            }

            $availableStudents = $studentQuery->with('company')->take(20)->get();
        }

        $availableCourses = $this->viewMode === 'create' 
            ? Course::withoutGlobalScopes()->where('status', 'published')->orderBy('title')->get() 
            : collect();

        $selectedStudent = $this->selectedUserId ? User::withoutGlobalScopes()->with('company')->find($this->selectedUserId) : null;
        $selectedCourse = $this->selectedCourseId ? Course::withoutGlobalScopes()->find($this->selectedCourseId) : null;

        return view('livewire.admin.enrollment-manager', [
            'enrollments' => $enrollments,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'lifetimeEnrollments' => $lifetimeEnrollments,
            'monthEnrollments' => $monthEnrollments,
            'companies' => $companies,
            'availableStudents' => $availableStudents,
            'availableCourses' => $availableCourses,
            'selectedStudent' => $selectedStudent,
            'selectedCourse' => $selectedCourse,
        ])->layout('layouts.admin', [
            'title' => 'Gestão de Matrículas & Controle de Acessos — ALVORA Super Admin',
            'breadcrumb' => 'Matrículas',
        ]);
    }
}

