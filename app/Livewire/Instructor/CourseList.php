<?php

namespace App\Livewire\Instructor;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CourseList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function togglePublish(int $courseId): void
    {
        $course = Course::findOrFail($courseId);

        $newStatus = $course->status === 'published' ? 'draft' : 'published';
        $course->update(['status' => $newStatus]);

        $message = $newStatus === 'published' 
            ? "Curso '{$course->title}' publicado com sucesso!" 
            : "Curso '{$course->title}' revertido para rascunho.";

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function deleteCourse(int $courseId): void
    {
        $course = Course::findOrFail($courseId);
        $title = $course->title;
        $course->delete();

        $this->dispatch('toast', message: "Curso '{$title}' removido com sucesso.", type: 'info');
    }

    public function render()
    {
        $totalCount = Course::count();
        $publishedCount = Course::where('status', 'published')->count();
        $draftCount = Course::where('status', 'draft')->count();

        $query = Course::with('instructor')
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->when(!empty($this->search), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at');

        $courses = $query->paginate(10);

        return view('livewire.instructor.course-list', [
            'courses' => $courses,
            'totalCount' => $totalCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount,
        ])->layout('layouts.instructor', [
            'title' => 'Meus Cursos — Catálogo do Instrutor — ALVORA',
            'breadcrumb' => 'Meus Cursos',
        ]);
    }
}
