<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CourseAccessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Governs internal visibility within tenant and across roles:
     * - admin_alvora: global visibility of all courses and drafts.
     * - Console / Artisan / Seeders: unrestricted when no HTTP session exists.
     * - Author (instructor_id === user.id): sees their own courses in any status (draft, published, archived).
     * - Company Manager / Student: sees published courses or courses with active enrollment.
     *   Drafts by third-party instructors are strictly omitted.
     * - Unauthenticated visitors: only published global courses without company_id.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // 1. Super Admin Alvora has global bypass for audit and oversight
        if ($user && method_exists($user, 'isAlvoraAdmin') && $user->isAlvoraAdmin()) {
            return;
        }

        // 2. Unauthenticated processes
        if (!$user) {
            // CLI commands, seeders and background queues without session operate unrestricted
            if (app()->runningInConsole()) {
                return;
            }

            // Public unauthenticated HTTP requests only access published independent courses
            $table = $model->getTable();
            $builder->where($table . '.status', 'published')
                ->whereNull($table . '.company_id');
            return;
        }

        // 3. Authenticated users: Author sees everything; others see only published or enrolled courses
        $table = $model->getTable();
        $builder->where(function (Builder $query) use ($user, $table) {
            $query->where($table . '.instructor_id', $user->id)
                ->orWhere($table . '.status', 'published')
                ->orWhereHas('enrollments', function (Builder $eq) use ($user) {
                    $eq->where('user_id', $user->id);
                });
        });
    }
}

