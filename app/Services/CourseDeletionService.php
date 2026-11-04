<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CourseDeletionService
{
    /**
     * Delete a course under a strict atomic transaction with pessimistic locking.
     *
     * Protocol:
     * 1. Begin Database Transaction.
     * 2. Acquire pessimistic lock on the Course record (lockForUpdate).
     * 3. Re-validate authorization for the requester (author with active corporate teaching rights or admin_alvora).
     * 4. Check if historical certificates exist for this course under the active lock.
     * 5. Block deletion if certificates exist (throw DomainException to abort and rollback).
     * 6. Perform physical DELETE within the same transaction (lock remains held until commit).
     * 7. Commit Database Transaction.
     *
     * @throws DomainException If certificates were issued for this course.
     * @throws AuthorizationException If requester lacks deletion authority.
     */
    public function deleteCourse(Course|int $course, ?User $requester = null): bool
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return DB::transaction(function () use ($courseId, $requester) {
            // 1 & 2. Obter lock exclusivo no registro do curso antes de qualquer operação
            $lockedCourse = Course::withoutGlobalScopes()
                ->where('id', $courseId)
                ->lockForUpdate()
                ->first();

            if (!$lockedCourse) {
                return false;
            }

            // 3. Verificação sob lock ativo de integridade absoluta: aborta se houver certificados
            if ($lockedCourse->certificates()->exists()) {
                throw new DomainException("Não é possível excluir o curso '{$lockedCourse->title}' pois ele possui certificados emitidos.");
            }

            // 4. Re-validar autorização do solicitante
            if ($requester) {
                $isAuthorized = $requester->isAlvoraAdmin()
                    || ($lockedCourse->instructor_id === $requester->id && $requester->canTeachInContext($lockedCourse->company_id));

                if (!$isAuthorized) {
                    throw new AuthorizationException('Usuário não autorizado a excluir este curso.');
                }
            }

            // 6 & 7. Executar a exclusão física dentro da mesma transação
            return (bool) $lockedCourse->delete();
        });
    }
}
