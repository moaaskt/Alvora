<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\RiskScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvasionRiskCalculatorService
{
    /**
     * Weight definitions (total = 100%):
     * 1. Inactivity (days without login): 35%
     * 2. Lesson stagnation (days without completing a lesson): 25%
     * 3. Quiz performance: 20%
     * 4. Peer deviation (progress vs company average): 20%
     */
    public function calculateForUser(User $user): RiskScore
    {
        $companyId = $user->company_id;

        // 1. Inactivity Factor (0 to 100) -> Weight 35%
        $inactivityScore = $this->calculateInactivityFactor($user);

        // 2. Lesson Stagnation Factor (0 to 100) -> Weight 25%
        $stagnationScore = $this->calculateStagnationFactor($user);

        // 3. Quiz Performance Factor (0 to 100) -> Weight 20%
        $quizRiskScore = $this->calculateQuizRiskFactor($user);

        // 4. Peer Deviation Factor (0 to 100) -> Weight 20%
        $peerDeviationScore = $this->calculatePeerDeviationFactor($user);

        // Weighted total
        $finalScore = (int) round(
            ($inactivityScore * 0.35) +
            ($stagnationScore * 0.25) +
            ($quizRiskScore * 0.20) +
            ($peerDeviationScore * 0.20)
        );

        $finalScore = max(0, min(100, $finalScore));

        // Risk Level: low (0-39), medium (40-69), high (70-100)
        $level = match (true) {
            $finalScore >= 70 => 'high',
            $finalScore >= 40 => 'medium',
            default => 'low',
        };

        $breakdown = [
            'inactivity' => [
                'score' => $inactivityScore,
                'weight' => 35,
                'weighted' => round($inactivityScore * 0.35, 1),
            ],
            'stagnation' => [
                'score' => $stagnationScore,
                'weight' => 25,
                'weighted' => round($stagnationScore * 0.25, 1),
            ],
            'quiz' => [
                'score' => $quizRiskScore,
                'weight' => 20,
                'weighted' => round($quizRiskScore * 0.20, 1),
            ],
            'peer_deviation' => [
                'score' => $peerDeviationScore,
                'weight' => 20,
                'weighted' => round($peerDeviationScore * 0.20, 1),
            ],
        ];

        $riskScore = RiskScore::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'score' => $finalScore,
            'level' => $level,
            'breakdown' => $breakdown,
            'calculated_at' => now(),
        ]);

        // Auto trigger autonomous AI intervention if in high risk
        if ($finalScore >= 70) {
            try {
                app(EvasionInterventionService::class)->triggerForRiskScore($riskScore);
            } catch (\Throwable $e) {
                // Log and don't break calculation
                \Illuminate\Support\Facades\Log::warning("Falha ao disparar intervenção automática: " . $e->getMessage());
            }
        }

        return $riskScore;
    }

    /**
     * Calculate for all active students in a company.
     */
    public function calculateForCompany(Company $company): Collection
    {
        $students = $company->users()->where('role', 'student')->get();
        $results = collect();

        foreach ($students as $student) {
            $results->push($this->calculateForUser($student));
        }

        return $results;
    }

    protected function calculateInactivityFactor(User $user): int
    {
        $lastActivity = $user->updated_at ?? $user->created_at;
        $daysInactive = Carbon::parse($lastActivity)->diffInDays(now());

        return match (true) {
            $daysInactive >= 14 => 100,
            $daysInactive >= 7 => 70,
            $daysInactive >= 4 => 40,
            $daysInactive >= 2 => 20,
            default => 0,
        };
    }

    protected function calculateStagnationFactor(User $user): int
    {
        $hasEnrollment = Enrollment::where('user_id', $user->id)->exists();
        if (!$hasEnrollment) {
            return 30; // Unenrolled or no courses started yet
        }

        $latestProgress = LessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        if (!$latestProgress) {
            $enrollment = Enrollment::where('user_id', $user->id)->oldest('created_at')->first();
            $daysSinceEnrollment = $enrollment ? Carbon::parse($enrollment->created_at)->diffInDays(now()) : 0;

            return $daysSinceEnrollment >= 7 ? 90 : 40;
        }

        $daysSinceCompletion = Carbon::parse($latestProgress->completed_at)->diffInDays(now());

        return match (true) {
            $daysSinceCompletion >= 14 => 100,
            $daysSinceCompletion >= 7 => 75,
            $daysSinceCompletion >= 4 => 40,
            $daysSinceCompletion >= 2 => 15,
            default => 0,
        };
    }

    protected function calculateQuizRiskFactor(User $user): int
    {
        $attempts = QuizAttempt::where('user_id', $user->id)->get();

        if ($attempts->isEmpty()) {
            return 20; // Sem quizzes ainda = risco neutro/baixo
        }

        $avgScore = $attempts->avg('score');

        // Se a nota média for 100, risco é 0. Se for 0, risco é 100.
        $risk = (int) round(100 - $avgScore);

        return max(0, min(100, $risk));
    }

    protected function calculatePeerDeviationFactor(User $user): int
    {
        // Progresso do usuário atual
        $userCompletedCount = LessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        // Média de conclusões dos colegas de empresa
        $peers = User::where('company_id', $user->company_id)
            ->where('role', 'student')
            ->pluck('id');

        if ($peers->count() <= 1) {
            return 0; // Único aluno na empresa, sem desvio relativo
        }

        $totalPeersCompleted = LessonProgress::whereIn('user_id', $peers)
            ->whereNotNull('completed_at')
            ->count();

        $peersAverage = $totalPeersCompleted / max(1, $peers->count());

        if ($peersAverage == 0) {
            return 0;
        }

        // Se o usuário está acima ou igual à média, risco 0
        if ($userCompletedCount >= $peersAverage) {
            return 0;
        }

        // Distância proporcional abaixo da média da turma
        $gapPercent = (($peersAverage - $userCompletedCount) / $peersAverage) * 100;

        return (int) min(100, round($gapPercent));
    }
}
