<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
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

        return $percent;
    }
}
