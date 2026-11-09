<?php

namespace App\Models;

use App\Events\CourseProgressUpdated;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
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
        'status',
        'progress_percent',
        'enrolled_at',
        'completed_at',
        'expires_at',
        'enrolled_by_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'progress_percent' => 'integer',
        ];
    }

    /**
     * Enrolled user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Course being taken.
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
     * User who manually performed the enrollment (if manual).
     */
    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_id');
    }

    /**
     * Check if the enrollment has lifetime access.
     */
    public function isLifetime(): bool
    {
        return is_null($this->expires_at);
    }

    /**
     * Check if the enrollment has expired.
     */
    public function isExpired(): bool
    {
        return !$this->isLifetime() && $this->expires_at->isPast();
    }

    /**
     * Recalculate progress percentage based on completed lessons.
     */
    public function recalculateProgress(): int
    {
        $lessonIds = $this->course->lessons()->pluck('id');
        $totalLessons = $lessonIds->count();

        if ($totalLessons === 0) {
            $this->update([
                'progress_percent' => 0,
            ]);
            CourseProgressUpdated::dispatch($this);
            return 0;
        }

        $completedCount = LessonProgress::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->count();

        $percent = (int) round(($completedCount / $totalLessons) * 100);
        $percent = min(100, max(0, $percent));

        $attributes = [
            'progress_percent' => $percent,
        ];

        if ($percent >= 100 && is_null($this->completed_at)) {
            $attributes['completed_at'] = now();
            $attributes['status'] = 'completed';
        } elseif ($percent < 100 && $this->status === 'completed') {
            $attributes['completed_at'] = null;
            $attributes['status'] = 'active';
        }

        $this->update($attributes);

        CourseProgressUpdated::dispatch($this);

        return $percent;
    }
}
