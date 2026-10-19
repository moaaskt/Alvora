<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningTrack extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'department',
        'is_mandatory',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
        ];
    }

    /**
     * Boot model events for automatic unique slug per tenant.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (LearningTrack $track) {
            if (empty($track->slug) && !empty($track->title)) {
                $baseSlug = Str::slug($track->title);
                $track->slug = static::generateUniqueSlug($baseSlug, $track->company_id);
            }
        });
    }

    /**
     * Generate unique slug scoped to tenant.
     */
    public static function generateUniqueSlug(string $baseSlug, ?int $companyId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (static::withoutGlobalScopes()
            ->where('slug', $slug)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Company (Tenant).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Courses contained in this learning track.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'track_courses', 'track_id', 'course_id')
            ->withPivot('order')
            ->orderBy('pivot_order');
    }

    /**
     * Track enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(TrackEnrollment::class, 'track_id');
    }

    /**
     * Calculate consolidated progress percentage for a specific user.
     */
    public function calculateUserProgress(int $userId): int
    {
        $courses = $this->courses;
        $totalCourses = $courses->count();

        if ($totalCourses === 0) {
            return 0;
        }

        $sumPercent = Enrollment::where('user_id', $userId)
            ->whereIn('course_id', $courses->pluck('id'))
            ->sum('progress_percent');

        $percent = (int) round($sumPercent / $totalCourses);

        return min(100, max(0, $percent));
    }
}
