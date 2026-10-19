<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * D-01: Transparent tenant filtering by session company_id.
     * D-03: admin_alvora bypasses tenant isolation completely.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::hasUser()) {
            return;
        }

        $user = Auth::user();

        // D-03: admin_alvora has unrestricted global visibility
        if (method_exists($user, 'isAlvoraAdmin') && $user->isAlvoraAdmin()) {
            return;
        }

        // Apply tenant isolation if user belongs to a company
        if (!empty($user->company_id)) {
            $builder->where($model->getTable() . '.company_id', $user->company_id);
        }
    }
}
