<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admin (admin_alvora) has audited global governance rights.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAlvoraAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the course details.
     */
    public function view(User $user, Course $course): bool
    {
        if ($course->instructor_id === $user->id) {
            return true;
        }

        if ($course->status === 'published') {
            if ($course->company_id === null) {
                return true;
            }
            return (int) $user->company_id === (int) $course->company_id || $user->canManageCompany($course->company_id);
        }

        return $course->enrollments()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create courses in the given context.
     */
    public function create(User $user, ?int $companyId = null): bool
    {
        return $user->canTeachInContext($companyId);
    }

    /**
     * Determine whether the user can update the course.
     * Authorized ONLY for the course author with active corporate teaching rights,
     * or admin_alvora (handled in before).
     * Corporate managers CANNOT modify didactic content created by other instructors.
     */
    public function update(User $user, Course $course): bool
    {
        if ($course->instructor_id !== $user->id) {
            return false;
        }

        return $user->canTeachInContext($course->company_id);
    }

    /**
     * Determine whether the user can delete the course.
     * Authorized ONLY for the course author with active corporate teaching rights,
     * or admin_alvora (handled in before).
     * Corporate managers CANNOT delete courses created by other instructors.
     * Physical deletion must be orchestrated via CourseDeletionService under
     * an atomic transaction with pessimistic locking.
     */
    public function delete(User $user, Course $course): bool
    {
        if ($course->instructor_id !== $user->id) {
            return false;
        }

        return $user->canTeachInContext($course->company_id);
    }
}

