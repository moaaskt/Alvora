<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicCourseController extends Controller
{
    /**
     * Display the public course details page.
     * Supports both tenant demo courses and published courses from accessible tenants.
     */
    public function show(string $slug): View|RedirectResponse
    {
        // 1. Resolve course by slug without global tenant scope (public showcase)
        $course = Course::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'instructor:id,company_id,name,role',
                'category:id,name,slug',
                'modules' => fn ($query) => $query->orderBy('order')->with([
                    'lessons' => fn ($lQuery) => $lQuery->orderBy('order'),
                ]),
                'quizzes' => fn ($query) => $query->select(['id', 'course_id', 'title']),
            ])
            ->withCount(['lessons', 'modules', 'quizzes'])
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->firstOrFail();

        // 2. Fetch related published courses from the same tenant or category (excluding current)
        $relatedCourses = Course::withoutGlobalScopes()
            ->where('company_id', $course->company_id)
            ->where('status', 'published')
            ->where('id', '!=', $course->id)
            ->select(['id', 'company_id', 'instructor_id', 'category_id', 'title', 'slug', 'description', 'thumbnail', 'status'])
            ->with(['instructor:id,company_id,name,role', 'category:id,name,slug'])
            ->withCount('lessons')
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->take(3)
            ->get();

        // Fallback: If not enough related courses in same company, fetch other published demo courses
        if ($relatedCourses->count() < 3) {
            $demoCnpj = config('app.demo_tenant_cnpj', '12.345.678/0001-90');
            $demoCompany = Company::where('cnpj', $demoCnpj)->first();
            if ($demoCompany && $demoCompany->id !== $course->company_id) {
                $additional = Course::withoutGlobalScopes()
                    ->where('company_id', $demoCompany->id)
                    ->where('status', 'published')
                    ->where('id', '!=', $course->id)
                    ->whereNotIn('id', $relatedCourses->pluck('id'))
                    ->select(['id', 'company_id', 'instructor_id', 'category_id', 'title', 'slug', 'description', 'thumbnail', 'status'])
                    ->with(['instructor:id,company_id,name,role', 'category:id,name,slug'])
                    ->withCount('lessons')
                    ->withSum('lessons as total_duration_minutes', 'duration_minutes')
                    ->take(3 - $relatedCourses->count())
                    ->get();
                $relatedCourses = $relatedCourses->concat($additional);
            }
        }

        // 3. Check if user is logged in and already enrolled in this course
        $isEnrolled = false;
        $user = Auth::user();
        if ($user && $user->isStudent()) {
            $isEnrolled = $course->enrollments()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        // 4. Identify free preview lesson (if any)
        $firstLesson = $course->modules->flatMap->lessons->first();
        $previewLesson = $course->modules->flatMap->lessons->firstWhere('is_free_preview', true) ?? $firstLesson;

        return view('courses.public-show', [
            'course' => $course,
            'relatedCourses' => $relatedCourses,
            'isEnrolled' => $isEnrolled,
            'previewLesson' => $previewLesson,
        ]);
    }
}
