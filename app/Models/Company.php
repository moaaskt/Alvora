<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cnpj',
        'plan',
        'status',
        'max_users',
    ];

    /**
     * Get all users directly associated with this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get employees through company_users relationship.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    /**
     * Get all subscriptions for this company.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all invoices for this company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the active or overdue subscription for this company.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'overdue'])
            ->latest('id')
            ->first();
    }

    /**
     * Check if company has an active or in-grace subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Check if company status is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if company is in overdue/grace period.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    /**
     * Check if company is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if company is on Starter plan.
     */
    public function isStarter(): bool
    {
        return $this->plan === 'starter';
    }

    /**
     * Check if company is on Pro plan.
     */
    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    /**
     * Check if company is on Enterprise plan.
     */
    public function isEnterprise(): bool
    {
        return $this->plan === 'enterprise';
    }

    /**
     * Get remaining seats/slots for users.
     */
    public function availableSlots(): int
    {
        $currentUsersCount = $this->users()->count();
        return max(0, $this->max_users - $currentUsersCount);
    }
}
