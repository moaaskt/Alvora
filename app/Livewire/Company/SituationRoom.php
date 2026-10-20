<?php

namespace App\Livewire\Company;

use App\Models\AiIntervention;
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
    public ?int $selectedUserId = null;
    public string $manualMessage = '';
    public bool $showModal = false;

    public function updatedFilterLevel(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openInterventionModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->manualMessage = "Olá, {$user?->name}! Gostaria de conversar com você sobre o seu andamento nos cursos da empresa e como podemos te apoiar no seu desenvolvimento.";
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedUserId = null;
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
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Base query for students in company
        $query = User::where('company_id', $companyId)
            ->where('role', 'student')
            ->with(['latestRiskScore', 'interventions']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        $allStudents = (clone $query)->get();

        // Calculate KPI metrics
        $totalStudents = $allStudents->count();
        $highRiskCount = $allStudents->filter(fn($u) => $u->latestRiskScore?->isHighRisk())->count();
        $mediumRiskCount = $allStudents->filter(fn($u) => $u->latestRiskScore?->isMediumRisk())->count();
        $lowRiskCount = $allStudents->filter(fn($u) => $u->latestRiskScore?->isLowRisk())->count();

        $highRiskPercent = $totalStudents > 0 ? round(($highRiskCount / $totalStudents) * 100) : 0;
        $averageScore = $totalStudents > 0
            ? round($allStudents->avg(fn($u) => $u->latestRiskScore?->score ?? 0))
            : 0;

        $interventionsCount = AiIntervention::where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Filter list by selected risk level
        if ($this->filterLevel !== 'all') {
            $studentsList = $allStudents->filter(fn($u) => $u->latestRiskScore?->level === $this->filterLevel);
        } else {
            // High risk first by default
            $studentsList = $allStudents->sortByDesc(fn($u) => $u->latestRiskScore?->score ?? 0);
        }

        return view('livewire.company.situation-room', [
            'totalStudents' => $totalStudents,
            'highRiskCount' => $highRiskCount,
            'mediumRiskCount' => $mediumRiskCount,
            'lowRiskCount' => $lowRiskCount,
            'highRiskPercent' => $highRiskPercent,
            'averageScore' => $averageScore,
            'interventionsCount' => $interventionsCount,
            'students' => $studentsList,
        ])->layout('layouts.app');
    }
}
