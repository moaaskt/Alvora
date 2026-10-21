<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiIntervention extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'risk_score_id',
        'recommended_lesson_id',
        'message',
        'status',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function riskScore(): BelongsTo
    {
        return $this->belongsTo(RiskScore::class);
    }

    public function recommendedLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'recommended_lesson_id');
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'viewed',
            'read_at' => now(),
        ]);
    }
}
