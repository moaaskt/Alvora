<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseDeletionService
{
    /**
     * Delete a course under a strict atomic transaction with pessimistic locking.
     *
     * Protocol:
     * 1. Begin Database Transaction.
     * 2. Acquire pessimistic lock on the Course record (lockForUpdate).
     * 3. Re-validate authorization via Gate/CoursePolicy under active lock BEFORE querying certificates.
     * 4. Check if historical certificates exist for this course under the active lock.
     * 5. Block deletion if certificates exist (throw DomainException to abort and rollback).
     * 6. Perform physical DELETE within the same transaction (lock remains held until commit).
     * 7. Commit Database Transaction.
     *
     * @param Course|int $course
     * @param User $requester Obligatory authenticated user identity requesting the deletion.
     * @return bool
     *
     * @throws AuthorizationException If requester lacks deletion authority according to CoursePolicy.
     * @throws DomainException If certificates were issued for this course.
     */
    public function deleteCourse(Course|int $course, User $requester): bool
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return DB::transaction(function () use ($courseId, $requester) {
            // 1 & 2. Lock exclusivo no registro do curso antes de qualquer outra consulta ou operação
            $lockedCourse = Course::withoutGlobalScopes()
                ->where('id', $courseId)
                ->lockForUpdate()
                ->first();

            if (!$lockedCourse) {
                return false;
            }

            // 3. Revalidar autorização centralizada via Gate/Policy sob o lock ativo,
            // ANTES de qualquer inspeção da existência de certificados
            if (!Gate::forUser($requester)->allows('delete', $lockedCourse)) {
                throw new AuthorizationException('Usuário não autorizado a excluir este curso.');
            }

            // 4 & 5. Verificação sob lock ativo de integridade física dos certificados
            if ($lockedCourse->certificates()->exists()) {
                throw new DomainException("Não é possível excluir o curso '{$lockedCourse->title}' pois ele possui certificados emitidos.");
            }

            // 6 & 7. Executar a exclusão física dentro da mesma transação com o lock retido
            return (bool) $lockedCourse->delete();
        });
    }
}
