<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'course_id',
        'company_id',
        'title',
        'description',
        'video_url',
        'duration_minutes',
        'order',
        'is_free_preview',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_free_preview' => 'boolean',
            'duration_minutes' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Module that owns this lesson.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Course that owns this lesson.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Complementary materials / attachments for download.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    /**
     * Format duration string (e.g., '15 min' or '1h 20m').
     */
    public function formattedDuration(): string
    {
        if ($this->duration_minutes < 60) {
            return "{$this->duration_minutes} min";
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return $minutes > 0 ? "{$hours}h {$minutes}min" : "{$hours}h";
    }
}
