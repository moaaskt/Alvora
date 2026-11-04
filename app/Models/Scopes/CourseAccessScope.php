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
     * - Author (instructor_id === user.id): sees their own courses in any status (draft, published, archived).
     * - Other corporate users (manager, student, other instructors): sees only published courses or enrolled courses.
     *   Drafts by other instructors are strictly omitted.
     * - Unauthenticated processes (HTTP visitors, non-context CLI / queues):
     *   No implicit global bypass. Only published courses are accessible unless explicitly bypassed via withoutGlobalScopes().
     *   Never anticipates B2C schema (no company_id IS NULL).
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();
        $user = Auth::user();

        // 1. Super Admin Alvora has global audit bypass
        if ($user && method_exists($user, 'isAlvoraAdmin') && $user->isAlvoraAdmin()) {
            return;
        }

        // 2. Unauthenticated processes or public visitors:
        // Do NOT grant implicit global bypass to CLI, queues or seeders.
        // Explicitly authorized system operations must call withoutGlobalScopes() or specify context.
        // Unauthenticated requests can only access published courses. Never assume B2C (no company_id IS NULL).
        if (!$user) {
            $builder->where($table . '.status', 'published');
            return;
        }

        // 3. Authenticated corporate users:
        // Author sees their own courses (drafts, published, etc.);
        // Other corporate users only see published courses or courses they are enrolled in.
        $builder->where(function (Builder $query) use ($user, $table) {
            $query->where($table . '.instructor_id', $user->id)
                ->orWhere($table . '.status', 'published')
                ->orWhereHas('enrollments', function (Builder $eq) use ($user) {
                    $eq->where('user_id', $user->id);
                });
        });
    }
}
