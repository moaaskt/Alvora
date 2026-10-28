<?php

namespace App\Actions\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollStudentAction
{
    /**
     * Enroll a student in a published course of their company.
     */
    public function execute(User $student, int $courseId): Enrollment
    {
        $course = Course::where('company_id', $student->company_id)
            ->where('id', $courseId)
            ->where('status', 'published')
            ->firstOrFail();

        $enrollment = Enrollment::firstOrCreate(
            [
                'company_id' => $student->company_id,
                'user_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'status' => 'active',
                'progress_percent' => 0,
                'enrolled_at' => now(),
            ]
        );

        session()->flash('success', "Matrícula realizada com sucesso no curso {$course->title}!");

        return $enrollment;
    }
}

