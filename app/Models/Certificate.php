<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        'template_version_id',
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
            'template_version_id' => 'integer',
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
        $relation = $this->belongsTo(Course::class)
            ->withoutGlobalScope(\App\Models\Scopes\CourseAccessScope::class);

        if ($this->exists && !empty($this->company_id)) {
            $relation->where('courses.company_id', $this->company_id);
        }

        return $relation;
    }

    /**
     * Create a new BelongsTo relationship instance, enforcing tenant isolation on eager loading.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        if ($relation === 'course') {
            return new class($query, $child, $foreignKey, $ownerKey, $relation) extends BelongsTo {
                public function match(array $models, Collection $results, $relation)
                {
                    $foreign = $this->foreignKey;
                    $owner = $this->ownerKey;

                    $dictionary = [];
                    foreach ($results as $result) {
                        $dictionary[$result->{$owner}] = $result;
                    }

                    foreach ($models as $model) {
                        if (isset($dictionary[$model->{$foreign}])) {
                            $candidate = $dictionary[$model->{$foreign}];
                            if (!empty($model->company_id) && (int) $model->company_id === (int) $candidate->company_id) {
                                $model->setRelation($relation, $candidate);
                            } else {
                                $model->setRelation($relation, null);
                            }
                        }
                    }

                    return $models;
                }
            };
        }

        return parent::newBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Company (Tenant).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Template version used to format this certificate (if versioned).
     */
    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplateVersion::class, 'template_version_id');
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
    public function getStudentNameAttribute(): ?string
    {
        if (is_array($this->snapshot) && array_key_exists('student_name', $this->snapshot)) {
            return $this->snapshot['student_name'];
        }

        return $this->user?->name;
    }

    /**
     * Get effective course title from snapshot or current course fallback.
     */
    public function getCourseTitleAttribute(): ?string
    {
        if (is_array($this->snapshot) && array_key_exists('course_title', $this->snapshot)) {
            return $this->snapshot['course_title'];
        }

        return $this->course?->title;
    }

    /**
     * Get effective company name from snapshot or current company fallback.
     */
    public function getCompanyNameAttribute(): ?string
    {
        if (is_array($this->snapshot) && array_key_exists('company_name', $this->snapshot)) {
            return $this->snapshot['company_name'];
        }

        return $this->company?->name;
    }

    /**
     * Get effective workload in minutes from snapshot or total_hours_minutes column.
     */
    public function getEffectiveWorkloadMinutesAttribute(): int
    {
        if (is_array($this->snapshot) && isset($this->snapshot['workload_minutes'])) {
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
