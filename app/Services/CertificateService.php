<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;

class CertificateService
{
    /**
     * Check if user is eligible to receive a certificate for the course.
     */
    public function isEligible(User $user, Course $course): bool
    {
        // 1. Enrollment must have 100% progress
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment || $enrollment->progress_percent < 100) {
            return false;
        }

        // 2. All mandatory quizzes of the course must be passed
        $quizzes = $course->quizzes;
        foreach ($quizzes as $quiz) {
            if (!$quiz->hasUserPassed($user->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get existing certificate or issue a new one if eligible.
     */
    public function getOrCreateCertificate(User $user, Course $course): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if (!$this->isEligible($user, $course)) {
            throw new DomainException('Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');
        }

        // Calculate total workload in minutes from all lessons of the course
        $totalMinutes = (int) $course->lessons()->sum('duration_minutes');

        return Certificate::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'total_hours_minutes' => $totalMinutes,
            'issued_at' => now(),
        ]);
    }

    /**
     * Generate DomPDF instance for download or stream.
     */
    public function generatePdf(Certificate $certificate)
    {
        $certificate->loadMissing(['user', 'course', 'company']);

        return Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
        ])->setPaper('a4', 'landscape');
    }
}
