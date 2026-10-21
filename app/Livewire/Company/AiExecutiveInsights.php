<?php

namespace App\Livewire\Company;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\RiskScore;
use App\Models\User;
use App\Services\OpenRouterResilientService;
use Livewire\Component;

class AiExecutiveInsights extends Component
{
    public ?string $executiveSummary = null;
    public ?string $modelUsed = null;
    public bool $isRefreshing = false;

    public function mount(): void
    {
        $this->loadCachedOrGenerateSummary();
    }

    public function refreshInsights(): void
    {
        $this->isRefreshing = true;
        $this->generateSummary();
        $this->isRefreshing = false;
        session()->flash('success', 'Diagnóstico executivo de IA atualizado com sucesso!');
    }

    protected function loadCachedOrGenerateSummary(): void
    {
        // Se já tiver insight na sessão ou gera novo
        $this->generateSummary();
    }

    protected function generateSummary(): void
    {
        $company = auth()->user()->company;
        if (!$company) return;

        $metrics = $this->calculateMetrics();

        $prompt = <<<PROMPT
Você é o Consultor Estratégico de Aprendizagem & People Analytics do ALVORA.
Analise a telemetria corporativa desta organização e forneça um Diagnóstico Executivo de Retenção e ROI de Treinamento em 3 parágrafos objetivos:
1. Diagnóstico do Engajamento & Risco de Evasão
2. Gargalos Identificados e Impacto nos Treinamentos
3. Três Recomendações Prioritárias para o RH

Métricas Atuais da Empresa ({$company->name}):
- Total de Colaboradores: {$metrics['totalUsers']}
- Total de Matrículas Ativas: {$metrics['totalEnrollments']}
- Aulas Concluídas: {$metrics['completedLessons']}
- Taxa Geral de Conclusão: {$metrics['completionRate']}%
- Colaboradores em Alto Risco de Abandono: {$metrics['highRiskCount']} ({$metrics['highRiskPercent']}%)
- Média do Score de Risco: {$metrics['avgRiskScore']}/100

Escreva em tom executivo, fundamentado em dados e orientado à retenção ativa.
PROMPT;

        $result = app(OpenRouterResilientService::class)->complete($prompt, ['temperature' => 0.4]);
        $this->executiveSummary = $result['content'];
        $this->modelUsed = $result['model_used'];
    }

    protected function calculateMetrics(): array
    {
        $companyId = auth()->user()->company_id;

        $students = User::where('company_id', $companyId)->where('role', 'student')->get();
        $totalUsers = $students->count();

        $totalEnrollments = Enrollment::where('company_id', $companyId)->count();
        $completedLessons = LessonProgress::where('company_id', $companyId)->whereNotNull('completed_at')->count();

        $highRiskCount = RiskScore::where('company_id', $companyId)
            ->whereIn('id', function ($query) use ($companyId) {
                $query->selectRaw('MAX(id)')->from('risk_scores')->where('company_id', $companyId)->groupBy('user_id');
            })
            ->where('level', 'high')
            ->count();

        $avgRiskScore = (int) round(
            RiskScore::where('company_id', $companyId)
                ->whereIn('id', function ($query) use ($companyId) {
                    $query->selectRaw('MAX(id)')->from('risk_scores')->where('company_id', $companyId)->groupBy('user_id');
                })
                ->avg('score') ?? 0
        );

        $highRiskPercent = $totalUsers > 0 ? round(($highRiskCount / $totalUsers) * 100) : 0;
        $completionRate = $totalEnrollments > 0 ? min(100, round(($completedLessons / ($totalEnrollments * 5)) * 100)) : 0;

        return [
            'totalUsers' => $totalUsers,
            'totalEnrollments' => $totalEnrollments,
            'completedLessons' => $completedLessons,
            'highRiskCount' => $highRiskCount,
            'highRiskPercent' => $highRiskPercent,
            'avgRiskScore' => $avgRiskScore,
            'completionRate' => $completionRate,
        ];
    }

    public function render()
    {
        $metrics = $this->calculateMetrics();

        return view('livewire.company.ai-executive-insights', [
            'metrics' => $metrics,
        ])->layout('layouts.app');
    }
}
