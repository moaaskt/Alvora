<?php

namespace App\Livewire\Company;

use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\Invitation;
use App\Models\RiskScore;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeManager extends Component
{
    use WithPagination;

    public string $emailsText = '';
    public string $role = 'student';
    public bool $showInviteModal = false;
    public string $search = '';
    public string $tab = 'all'; // all, pending, at_risk, completed
    public int $perPage = 10;

    protected array $rules = [
        'emailsText' => 'required|string',
        'role' => 'required|in:student,instructor',
    ];

    protected array $messages = [
        'emailsText.required' => 'Informe ao menos um e-mail para enviar o convite.',
        'role.required' => 'Selecione o papel do colaborador.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function toggleInviteModal(): void
    {
        $this->showInviteModal = !$this->showInviteModal;
    }

    public function inviteEmployees(): void
    {
        $this->validate();

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            $this->dispatch('toast', message: 'Você precisa estar vinculado a uma empresa para convidar colaboradores.', type: 'error');
            return;
        }

        // D-08: Split emails by commas, semicolons or newlines
        $rawEmails = preg_split('/[\r\n,;]+/', $this->emailsText);
        $validEmails = [];
        $invalidEmails = [];

        foreach ($rawEmails as $raw) {
            $cleaned = trim($raw);
            if (empty($cleaned)) {
                continue;
            }
            if (filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = strtolower($cleaned);
            } else {
                $invalidEmails[] = $cleaned;
            }
        }

        $validEmails = array_unique($validEmails);

        if (empty($validEmails)) {
            $this->addError('emailsText', 'Nenhum e-mail válido foi encontrado. Verifique o formato informado.');
            return;
        }

        $createdCount = 0;

        foreach ($validEmails as $email) {
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                continue;
            }

            // Invalidate or update previous pending invitation if any
            Invitation::where('company_id', $company->id)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->delete();

            // D-05 & D-06: 7-day expiration
            Invitation::create([
                'company_id' => $company->id,
                'email' => $email,
                'role' => $this->role,
                'token' => Invitation::generateToken(),
                'expires_at' => now()->addDays(7),
                'created_by' => $user->id,
            ]);

            $createdCount++;
        }

        $this->reset(['emailsText', 'showInviteModal']);
        $this->role = 'student';

        if ($createdCount > 0) {
            $this->dispatch('toast', 
                message: "{$createdCount} convite(s) gerado(s) com sucesso!", 
                type: 'success'
            );
        } else {
            $this->dispatch('toast', 
                message: 'Os e-mails informados já pertencem a usuários cadastrados.', 
                type: 'info'
            );
        }
    }

    public function resendInvitation(int $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);

        // D-06: Renew token and extend validity for 7 more days
        $invitation->update([
            'token' => Invitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->dispatch('toast', message: "Convite para {$invitation->email} renovado por mais 7 dias!", type: 'success');
    }

    public function revokeInvitation(int $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);
        $email = $invitation->email;
        $invitation->delete();

        $this->dispatch('toast', message: "Convite para {$email} revogado.", type: 'info');
    }

    public function exportCsv()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            $this->dispatch('toast', message: 'Nenhuma empresa encontrada.', type: 'error');
            return null;
        }

        $employees = $company->users()
            ->where('role', 'student')
            ->with(['enrollments.course', 'latestRiskScore'])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alvora_alunos_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($employees) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Nome', 'E-mail', 'Cursos Matriculados', 'Progresso Médio (%)', 'Score de Risco', 'Nível de Risco', 'Data Cadastro'], ';');

            foreach ($employees as $emp) {
                $courses = $emp->enrollments->map(fn($e) => $e->course?->title)->filter()->join(', ');
                $avgProgress = $emp->enrollments->count() > 0 ? round($emp->enrollments->avg('progress_percent')) : 0;
                $score = $emp->latestRiskScore?->score ?? 0;
                $level = $emp->latestRiskScore?->level ?? 'low';

                fputcsv($handle, [
                    $emp->name,
                    $emp->email,
                    $courses ?: 'Nenhum curso',
                    $avgProgress . '%',
                    $score,
                    strtoupper($level),
                    $emp->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        };

        $this->dispatch('toast', message: 'Relatório de acessos CSV gerado com sucesso!', type: 'success');
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->company;

        // Base query for students of this company
        $query = $company ? $company->users()->where('role', 'student')->with(['enrollments.course', 'latestRiskScore']) : User::query()->whereRaw('1 = 0');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Metrics calculations
        $allStudents = $company ? $company->users()->where('role', 'student')->with(['enrollments', 'latestRiskScore'])->get() : collect();
        $totalStudents = $allStudents->count();

        $pendingInvitations = $company ? Invitation::where('company_id', $company->id)
            ->whereNull('accepted_at')
            ->orderByDesc('created_at')
            ->get() : collect();
        $pendingCount = $pendingInvitations->count();

        $atRiskStudents = $allStudents->filter(fn($u) => $u->latestRiskScore && ($u->latestRiskScore->isHighRisk() || $u->latestRiskScore->isMediumRisk()));
        $atRiskCount = $atRiskStudents->count();

        $completedStudents = $allStudents->filter(function ($u) {
            return $u->enrollments->isNotEmpty() && $u->enrollments->every(fn($e) => $e->completed_at !== null || $e->progress_percent >= 100);
        });
        $completedCount = $completedStudents->count();

        // Average completion rate
        $enrollments = $company ? Enrollment::where('company_id', $company->id)->get() : collect();
        $avgCompletionRate = $enrollments->count() > 0 ? round($enrollments->avg('progress_percent'), 1) : 0.0;

        // Overall risk percentage
        $avgRiskScore = $allStudents->count() > 0 ? round($allStudents->avg(fn($u) => $u->latestRiskScore?->score ?? 0), 1) : 0.0;

        // Tab filters
        if ($this->tab === 'at_risk') {
            $query->whereHas('riskScores', function ($q) {
                $q->whereIn('level', ['high', 'medium']);
            });
        } elseif ($this->tab === 'completed') {
            $query->whereHas('enrollments', function ($q) {
                $q->whereNotNull('completed_at')->orWhere('progress_percent', '>=', 100);
            });
        }

        $employees = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.company.employee-manager', [
            'company' => $company,
            'employees' => $employees,
            'pendingInvitations' => $pendingInvitations,
            'totalStudents' => $totalStudents,
            'pendingCount' => $pendingCount,
            'atRiskCount' => $atRiskCount,
            'completedCount' => $completedCount,
            'avgCompletionRate' => $avgCompletionRate,
            'avgRiskScore' => $avgRiskScore,
        ])->layout('layouts.instructor', [
            'title' => 'Gestão de Alunos & Acessos — ALVORA',
            'breadcrumb' => 'Gestão de Alunos',
        ]);
    }
}
