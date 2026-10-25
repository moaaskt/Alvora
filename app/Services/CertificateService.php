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
            ->where('company_id', $user->company_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if (!$this->isEligible($user, $course)) {
            throw new DomainException('Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado.');
        }

        // Calculate total workload in minutes from all lessons of the course
        $totalMinutes = (int) $course->lessons()->sum('duration_minutes');

        $snapshot = [
            'mode' => 'original',
            'student_name' => $user->name,
            'course_title' => $course->title,
            'company_name' => $course->company?->name ?? $user->company?->name ?? 'Empresa Cliente',
            'workload_minutes' => $totalMinutes,
            'captured_at' => now()->toIso8601String(),
        ];

        try {
            return Certificate::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'total_hours_minutes' => $totalMinutes,
                'snapshot' => $snapshot,
                'issued_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return $this->resolveConcurrentCertificate($user, $course, $e);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                return $this->resolveConcurrentCertificate($user, $course, $e);
            }

            // Falhas de banco não relacionadas a unicidade nunca são mascaradas
            throw $e;
        }
    }

    /**
     * Retrieve the existing certificate in case of verified concurrent collision.
     */
    protected function resolveConcurrentCertificate(User $user, Course $course, \Throwable $originalException): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('company_id', $user->company_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        throw $originalException;
    }

    /**
     * Determine if a QueryException represents a unique constraint violation across database drivers.
     */
    protected function isUniqueConstraintViolation(\Illuminate\Database\QueryException $e): bool
    {
        $code = (string) $e->getCode();
        $message = $e->getMessage();
        $driverCode = $e->errorInfo[1] ?? null;

        // MySQL / MariaDB (driver code 1062)
        if ($driverCode === 1062 || str_contains($message, 'Duplicate entry')) {
            return true;
        }

        // SQLite (driver code 19 / 2067 ou mensagem 'UNIQUE constraint failed')
        if ($driverCode === 19 || $driverCode === 2067 || str_contains($message, 'UNIQUE constraint failed')) {
            return true;
        }

        // PostgreSQL (SQLSTATE 23505)
        if ($code === '23505' || str_contains($message, 'unique constraint')) {
            return true;
        }

        return false;
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
