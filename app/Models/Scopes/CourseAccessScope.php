<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CourseAccessScope implements Scope
{
    /**
     * Whether the public showcase context is explicitly active in the current server execution.
     */
    protected static bool $publicShowcaseActive = false;

    /**
     * Execute a callback strictly within the authorized public demo showcase context.
     * Preserves and restores previous state for full reentrancy and exception safety.
     */
    public static function runAsPublicShowcase(callable $callback): mixed
    {
        $previousState = static::$publicShowcaseActive;
        static::$publicShowcaseActive = true;

        try {
            return $callback();
        } finally {
            static::$publicShowcaseActive = $previousState;
        }
    }

    /**
     * Explicitly reset public showcase state (e.g., between Octane / persistent worker ticks).
     */
    public static function resetPublicShowcase(): void
    {
        static::$publicShowcaseActive = false;
    }

    /**
     * Check if public showcase context is active.
     */
    public static function isPublicShowcaseActive(): bool
    {
        return static::$publicShowcaseActive;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     * Governs internal visibility within tenant and across roles:
     * - admin_alvora: global visibility of all courses and drafts.
     * - Author (instructor_id === user.id): sees their own courses in any status (draft, published, archived).
     * - Other corporate users (manager, student, other instructors): sees only published courses or enrolled courses.
     *   Drafts by other instructors are strictly omitted.
     * - Unauthenticated processes (HTTP visitors, non-context CLI / queues):
     *   Strictly zero courses by default (whereRaw('1 = 0')).
     *   Only when explicitly running inside the server-authorized public showcase context (runAsPublicShowcase),
     *   the official demo tenant's published courses can be retrieved.
     *   Never assumes B2C schema (no company_id IS NULL).
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();
        $user = Auth::user();

        // 1. Super Admin Alvora has global audit bypass
        if ($user && method_exists($user, 'isAlvoraAdmin') && $user->isAlvoraAdmin()) {
            return;
        }

        // 2. Unauthenticated processes, CLI, background jobs, or public visitors:
        if (!$user) {
            // Strictly check if executed inside the server-authorized public showcase context
            if (static::$publicShowcaseActive) {
                $demoCnpj = config('app.demo_tenant_cnpj', '12.345.678/0001-90');
                $demoCompanyId = \App\Models\Company::where('cnpj', $demoCnpj)->value('id');

                if ($demoCompanyId) {
                    $builder->where($table . '.company_id', $demoCompanyId)
                        ->where($table . '.status', 'published');
                    return;
                }
            }

            // Zero records for any unauthenticated query without authorized context
            $builder->whereRaw('1 = 0');
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
