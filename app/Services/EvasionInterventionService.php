<?php

namespace App\Services;

use App\Models\AiIntervention;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\RiskScore;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EvasionInterventionService
{
    /**
     * Trigger autonomous intervention if score is high (>= 70) and no recent intervention sent in last 48h.
     */
    public function triggerForRiskScore(RiskScore $riskScore): ?AiIntervention
    {
        if ($riskScore->score < 70) {
            return null;
        }

        $user = $riskScore->user;
        if (!$user) {
            return null;
        }

        // Avoid notification fatigue: check if an intervention was already sent in the last 48 hours
        $hasRecentIntervention = AiIntervention::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(48))
            ->exists();

        if ($hasRecentIntervention) {
            Log::info("Intervenção evitada para user {$user->id} devido a envio recente (< 48h).");
            return null;
        }

        // Identify recommendation target (next uncompleted lesson or low-scoring quiz lesson)
        $recommendedLesson = $this->findRecommendedLesson($user);

        // Generate tailored empathetic message
        $message = $this->generateMessage($user, $riskScore, $recommendedLesson);

        return AiIntervention::create([
            'company_id' => $riskScore->company_id,
            'user_id' => $user->id,
            'risk_score_id' => $riskScore->id,
            'recommended_lesson_id' => $recommendedLesson?->id,
            'message' => $message,
            'status' => 'sent',
        ]);
    }

    public function createManualIntervention(User $user, string $message, ?int $recommendedLessonId = null): AiIntervention
    {
        return AiIntervention::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'risk_score_id' => $user->latestRiskScore?->id,
            'recommended_lesson_id' => $recommendedLessonId,
            'message' => $message,
            'status' => 'sent',
        ]);
    }

    protected function findRecommendedLesson(User $user): ?Lesson
    {
        // 1. Check if there's a failed quiz attempt
        $failedAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('passed', false)
            ->latest()
            ->first();

        if ($failedAttempt && $failedAttempt->quiz?->lesson) {
            return $failedAttempt->quiz->lesson;
        }

        // 2. Otherwise find the first uncompleted lesson in the user's active enrolled courses
        $enrollments = Enrollment::where('user_id', $user->id)->get();
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (!$course) continue;

            $completedLessonIds = LessonProgress::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->toArray();

            $nextLesson = Lesson::where('course_id', $course->id)
                ->whereNotIn('id', $completedLessonIds)
                ->orderBy('order')
                ->first();

            if ($nextLesson) {
                return $nextLesson;
            }
        }

        return null;
    }

    protected function generateMessage(User $user, RiskScore $riskScore, ?Lesson $lesson): string
    {
        $firstName = explode(' ', $user->name)[0];

        if ($lesson) {
            return "Olá, {$firstName}! Notamos que sua rotina de treinamentos deu uma desacelerada. Para ajudar você a retomar o ritmo com leveza, recomendamos a aula \"{$lesson->title}\". Lembre-se que cada passo na sua trilha fortalece seu crescimento profissional!";
        }

        return "Olá, {$firstName}! Sentimos sua falta nos treinamentos corporativos da sua empresa. Estamos prontos para ajudar você a continuar sua evolução. Que tal acessar sua trilha hoje e assistir a uma aula rápida?";
    }
}
