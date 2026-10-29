<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Validação de autorização no mount.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if (!$user || !$user->isAlvoraAdmin()) {
            abort(403, 'Acesso restrito ao Super Administrador da Alvora.');
        }
    }

    /**
     * Renderização da Dashboard com os KPIs Globais.
     */
    public function render()
    {
        // 1. Total de Cursos
        $totalCourses = Course::count();
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();

        // 2. Total de Aulas
        $totalLessons = Lesson::count();

        // 3. Total Geral de Matrículas em todos os cursos
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();

        // 4. Total de Alunos Ativos cadastrados
        $totalStudents = User::where('role', UserRole::STUDENT)->count();

        // 5. Total de Instrutores Cadastrados
        $totalInstructors = User::where('role', UserRole::INSTRUCTOR)->count();

        // Cursos recentes cadastrados
        $recentCourses = Course::with(['instructor', 'company'])
            ->withCount(['lessons', 'modules', 'enrollments'])
            ->latest('id')
            ->take(5)
            ->get();

        // Matrículas recentes na plataforma
        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->latest('id')
            ->take(6)
            ->get();

        // Instrutores recentes
        $recentInstructors = User::where('role', UserRole::INSTRUCTOR)
            ->withCount('courses')
            ->latest('id')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalCourses' => $totalCourses,
            'publishedCourses' => $publishedCourses,
            'draftCourses' => $draftCourses,
            'totalLessons' => $totalLessons,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'totalStudents' => $totalStudents,
            'totalInstructors' => $totalInstructors,
            'recentCourses' => $recentCourses,
            'recentEnrollments' => $recentEnrollments,
            'recentInstructors' => $recentInstructors,
        ])->layout('layouts.admin', [
            'title' => 'Dashboard Administrativo — ALVORA',
            'breadcrumb' => 'Visão Geral',
        ]);
    }
}

