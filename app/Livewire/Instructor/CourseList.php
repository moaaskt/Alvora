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
        $query = Course::with('instructor')
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

        $courses = $query->paginate(9);

        return view('livewire.instructor.course-list', [
            'courses' => $courses,
        ])->layout('layouts.app', ['title' => 'Gestão de Cursos — ALVORA']);
    }
}
