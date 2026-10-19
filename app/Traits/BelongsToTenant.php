<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait and attach TenantScope + automatic company_id injection on creation (D-04).
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (Auth::hasUser()) {
                $user = Auth::user();
                if (method_exists($user, 'isAlvoraAdmin') && !$user->isAlvoraAdmin()) {
                    if (empty($model->company_id) && !empty($user->company_id)) {
                        $model->company_id = $user->company_id;
                    }
                }
            }
        });
    }

    /**
     * Get the company that owns this model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
