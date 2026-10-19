<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackEnrollment extends Model
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
        'track_id',
        'status',
        'progress_percent',
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
            'progress_percent' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Company (Tenant).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Student / User enrolled.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Learning Track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(LearningTrack::class, 'track_id');
    }

    /**
     * Recalculate and update consolidated progress for this user in this track.
     */
    public function updateConsolidatedProgress(): void
    {
        $calculatedProgress = $this->track->calculateUserProgress($this->user_id);
        $this->progress_percent = $calculatedProgress;

        if ($calculatedProgress >= 100) {
            $this->status = 'completed';
            if (! $this->completed_at) {
                $this->completed_at = now();
            }
        }

        $this->save();
    }
}
