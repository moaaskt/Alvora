<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'instructor_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'status',
    ];

    /**
     * Boot model events for automatic slug generation if not provided.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Course $course) {
            if (empty($course->slug) && !empty($course->title)) {
                $baseSlug = Str::slug($course->title);
                $course->slug = static::generateUniqueSlug($baseSlug, $course->company_id);
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
     * Course instructor.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Modules of the course.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    /**
     * Lessons belonging to this course through modules.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Enrollments of this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Quizzes of this course.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Certificates issued for this course.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Check if course is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if course is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if course is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
}
