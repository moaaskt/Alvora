<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'course_id',
        'uuid',
        'validation_code',
        'total_hours_minutes',
        'snapshot',
        'issued_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_hours_minutes' => 'integer',
            'snapshot' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    /**
     * Boot model events for automatic uuid and code generation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Certificate $certificate) {
            if (empty($certificate->uuid)) {
                $certificate->uuid = (string) Str::uuid();
            }

            if (empty($certificate->validation_code)) {
                $certificate->validation_code = static::generateUniqueCode();
            }

            if (empty($certificate->issued_at)) {
                $certificate->issued_at = now();
            }
        });
    }

    /**
     * Generate unique validation code in format ALV-YYYY-XXXXXX.
     */
    public static function generateUniqueCode(): string
    {
        $year = date('Y');
        do {
            $random = strtoupper(Str::random(6));
            $code = "ALV-{$year}-{$random}";
        } while (static::withoutGlobalScopes()->where('validation_code', $code)->exists());

        return $code;
    }

    /**
     * User who earned the certificate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Course completed.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Company (Tenant).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if certificate has an original immutable snapshot captured at issuance.
     */
    public function hasOriginalSnapshot(): bool
    {
        return is_array($this->snapshot) && ($this->snapshot['mode'] ?? null) === 'original';
    }

    /**
     * Check if certificate has a snapshot retroactively reconstructed.
     */
    public function isReconstructed(): bool
    {
        return is_array($this->snapshot) && ($this->snapshot['mode'] ?? null) === 'reconstructed';
    }

    /**
     * Check if certificate is a legacy record without snapshot.
     */
    public function isLegacyWithoutSnapshot(): bool
    {
        return empty($this->snapshot);
    }

    /**
     * Get effective student name from snapshot or current user fallback.
     */
    public function getStudentNameAttribute(): string
    {
        return $this->snapshot['student_name']
            ?? $this->user?->name
            ?? 'Colaborador';
    }

    /**
     * Get effective course title from snapshot or current course fallback.
     */
    public function getCourseTitleAttribute(): string
    {
        return $this->snapshot['course_title']
            ?? $this->course?->title
            ?? 'Curso Concluído';
    }

    /**
     * Get effective company name from snapshot or current company fallback.
     */
    public function getCompanyNameAttribute(): string
    {
        return $this->snapshot['company_name']
            ?? $this->company?->name
            ?? 'Empresa Cliente';
    }

    /**
     * Get effective workload in minutes from snapshot or total_hours_minutes column.
     */
    public function getEffectiveWorkloadMinutesAttribute(): int
    {
        if (isset($this->snapshot['workload_minutes'])) {
            return (int) $this->snapshot['workload_minutes'];
        }

        return (int) $this->total_hours_minutes;
    }

    /**
     * Formatted course workload string.
     */
    public function formattedWorkload(): string
    {
        $minutesTotal = $this->effective_workload_minutes;
        $hours = floor($minutesTotal / 60);
        $minutes = $minutesTotal % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}min";
        } elseif ($hours > 0) {
            return "{$hours} horas";
        } else {
            return "{$minutes} minutos";
        }
    }
}
