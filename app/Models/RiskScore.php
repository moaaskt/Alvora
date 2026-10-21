<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScore extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'score',
        'level',
        'breakdown',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'breakdown' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isHighRisk(): bool
    {
        return $this->level === 'high';
    }

    public function isMediumRisk(): bool
    {
        return $this->level === 'medium';
    }

    public function isLowRisk(): bool
    {
        return $this->level === 'low';
    }
}
