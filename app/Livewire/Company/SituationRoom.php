<?php

namespace App\Livewire\Company;

use App\Models\AiIntervention;
use App\Models\LearningTrack;
use App\Models\RiskScore;
use App\Models\User;
use App\Services\EvasionInterventionService;
use App\Services\EvasionRiskCalculatorService;
use Livewire\Component;
use Livewire\WithPagination;

class SituationRoom extends Component
{
    use WithPagination;

    public string $filterLevel = 'all'; // all, high, medium, low
    public string $search = '';
    public ?string $department = null;
    public $trackId = null;
    public string $sortBy = 'risk_desc'; // risk_desc, risk_asc, name_asc, name_desc, inactivity_desc

    public ?int $selectedUserId = null;
    public string $manualMessage = '';
    public bool $showModal = false;
    public ?User $selectedUser = null;

    public function updatedFilterLevel(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedTrackId(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $level): void
    {
        $this->filterLevel = $level;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterLevel = 'all';
        $this->search = '';
        $this->department = null;
        $this->trackId = null;
        $this->sortBy = 'risk_desc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->filterLevel !== 'all'
            || !empty(trim($this->search))
            || !empty($this->department)
            || !empty($this->trackId)
            || $this->sortBy !== 'risk_desc';
    }

    public function openInterventionModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $user = User::with(['latestRiskScore', 'interventions'])->find($userId);
        $this->selectedUser = $user;

        // Se houver mensagem de intervenção pré-gerada no banco, carregar ela
        $latestIntervention = $user?->interventions()->latest('id')->first();
        if ($latestIntervention && !empty($latestIntervention->message)) {
            $this->manualMessage = $latestIntervention->message;
        } else {
            $this->manualMessage = "Olá, {$user?->name}! Gostaria de conversar com você sobre o seu andamento nos cursos da empresa e como podemos te apoiar no seu desenvolvimento.";
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->manualMessage = '';
    }

    public function sendManualIntervention(): void
    {
        $this->validate([
            'manualMessage' => 'required|string|min:10|max:1000',
        ]);

        $user = User::find($this->selectedUserId);
        if ($user && $user->company_id === auth()->user()->company_id) {
            app(EvasionInterventionService::class)->createManualIntervention($user, $this->manualMessage);
            session()->flash('success', "Intervenção enviada com sucesso para {$user->name}.");
        }

        $this->closeModal();
    }

    public function recalculateAllScores(): void
    {
        $company = auth()->user()->company;
        if ($company) {
            app(EvasionRiskCalculatorService::class)->calculateForCompany($company);
            session()->flash('success', 'Scores de risco recomputados com sucesso para todos os colaboradores!');
            $this->dispatch('toast', message: 'Scores de risco recomputados com sucesso via Copilot IA!', type: 'success');
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Opções de Departamentos existentes da empresa
        $departments = User::where('company_id', $companyId)
            ->where('role', 'student')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        // Opções de Trilhas da empresa
        $tracks = LearningTrack::where('company_id', $companyId)
            ->orderBy('title')
            ->get(['id', 'title']);

        // Query Base com escopo de Equipe, Trilha e Busca
        $baseQuery = User::where('company_id', $companyId)
            ->where('role', 'student')
            ->with(['latestRiskScore', 'interventions', 'trackEnrollments.track']);

        if (!empty($this->department)) {
            $baseQuery->where('department', $this->department);
        }

        if (!empty($this->trackId)) {
            $trackId = (int) $this->trackId;
            $baseQuery->whereHas('trackEnrollments', fn($q) => $q->where('track_id', $trackId));
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $scopedStudents = $baseQuery->get();

        // KPIs calculados sobre o escopo selecionado
        $totalStudents = $scopedStudents->count();
        $highRiskCount = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isHighRisk())->count();
        $mediumRiskCount = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isMediumRisk())->count();
        $lowRiskCount = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isLowRisk())->count();

        $highRiskPercent = $totalStudents > 0 ? round(($highRiskCount / $totalStudents) * 100) : 0;
        $averageScore = $totalStudents > 0
            ? round($scopedStudents->avg(fn($u) => $u->latestRiskScore?->score ?? 0))
            : 0;

        $interventionsCount = AiIntervention::where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Cohorts para o Heatmap (dentro do escopo selecionado)
        $safeStudents = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isLowRisk() || (!$u->latestRiskScore));
        $warningStudents = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isMediumRisk());
        $criticalStudents = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->isHighRisk());

        // Filtragem da tabela pelo Nível de Risco selecionado
        if ($this->filterLevel !== 'all') {
            $tableStudents = $scopedStudents->filter(fn($u) => $u->latestRiskScore?->level === $this->filterLevel);
        } else {
            $tableStudents = $scopedStudents;
        }

        // Ordenação da tabela
        $studentsList = match ($this->sortBy) {
            'risk_asc' => $tableStudents->sortBy(fn($u) => $u->latestRiskScore?->score ?? 0),
            'name_asc' => $tableStudents->sortBy(fn($u) => strtolower($u->name)),
            'name_desc' => $tableStudents->sortByDesc(fn($u) => strtolower($u->name)),
            'inactivity_desc' => $tableStudents->sortByDesc(fn($u) => (int) ($u->latestRiskScore?->breakdown['inactivity']['score'] ?? 0)),
            default => $tableStudents->sortByDesc(fn($u) => $u->latestRiskScore?->score ?? 0), // risk_desc
        };

        return view('livewire.company.situation-room', [
            'totalStudents' => $totalStudents,
            'highRiskCount' => $highRiskCount,
            'mediumRiskCount' => $mediumRiskCount,
            'lowRiskCount' => $lowRiskCount,
            'highRiskPercent' => $highRiskPercent,
            'averageScore' => $averageScore,
            'interventionsCount' => $interventionsCount,
            'students' => $studentsList,
            'filteredCount' => $studentsList->count(),
            'departments' => $departments,
            'tracks' => $tracks,
            'hasActiveFilters' => $this->hasActiveFilters(),
            'safeStudents' => $safeStudents,
            'warningStudents' => $warningStudents,
            'criticalStudents' => $criticalStudents,
        ])->layout('layouts.instructor', [
            'title' => 'Sala de Situação — Telemetria Preditiva & Retenção IA — ALVORA',
            'breadcrumb' => 'Sala de Situação',
        ]);
    }
}
