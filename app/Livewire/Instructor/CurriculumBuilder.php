<?php

namespace App\Livewire\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CurriculumBuilder extends Component
{
    public int $courseId;

    // State for new module
    public string $newModuleTitle = '';
    public string $newModuleDescription = '';
    public bool $showNewModuleModal = false;

    // State for new lesson
    public ?int $targetModuleId = null;
    public string $newLessonTitle = '';
    public string $newLessonDescription = '';
    public string $newLessonVideoUrl = '';
    public int $newLessonDuration = 10;
    public bool $newLessonIsPreview = false;
    public bool $showNewLessonModal = false;

    // State for attachment
    public ?int $targetLessonId = null;
    public string $attachmentTitle = '';
    public string $attachmentUrl = '';
    public bool $showAttachmentModal = false;

    public function mount(Course $course): void
    {
        $this->courseId = $course->id;
    }

    public function addModule(): void
    {
        $this->validate([
            'newModuleTitle' => 'required|string|min:2|max:255',
        ], [
            'newModuleTitle.required' => 'O título do módulo é obrigatório.',
        ]);

        $course = Course::findOrFail($this->courseId);
        $maxOrder = $course->modules()->max('order') ?? 0;

        Module::create([
            'course_id' => $course->id,
            'company_id' => $course->company_id,
            'title' => $this->newModuleTitle,
            'description' => $this->newModuleDescription ?: null,
            'order' => $maxOrder + 1,
        ]);

        $this->reset(['newModuleTitle', 'newModuleDescription', 'showNewModuleModal']);
        $this->dispatch('toast', message: 'Módulo adicionado com sucesso!', type: 'success');
    }

    public function deleteModule(int $moduleId): void
    {
        $module = Module::where('course_id', $this->courseId)->findOrFail($moduleId);
        $title = $module->title;
        $module->delete();

        $this->dispatch('toast', message: "Módulo '{$title}' removido com sucesso.", type: 'info');
    }

    public function moveModule(int $moduleId, string $direction): void
    {
        $modules = Module::where('course_id', $this->courseId)->orderBy('order')->get();
        $index = $modules->search(fn ($m) => $m->id === $moduleId);

        if ($index === false) {
            return;
        }

        if ($direction === 'up' && $index > 0) {
            $prev = $modules[$index - 1];
            $current = $modules[$index];

            $temp = $current->order;
            $current->update(['order' => $prev->order]);
            $prev->update(['order' => $temp]);
        } elseif ($direction === 'down' && $index < count($modules) - 1) {
            $next = $modules[$index + 1];
            $current = $modules[$index];

            $temp = $current->order;
            $current->update(['order' => $next->order]);
            $next->update(['order' => $temp]);
        }

        $this->dispatch('toast', message: 'Ordem dos módulos atualizada.', type: 'success');
    }

    public function openNewLessonModal(int $moduleId): void
    {
        $this->targetModuleId = $moduleId;
        $this->showNewLessonModal = true;
    }

    public function addLesson(): void
    {
        $this->validate([
            'targetModuleId' => 'required|exists:modules,id',
            'newLessonTitle' => 'required|string|min:2|max:255',
            'newLessonVideoUrl' => 'nullable|url|max:2048',
            'newLessonDuration' => 'required|integer|min:1|max:600',
        ], [
            'newLessonTitle.required' => 'O título da aula é obrigatório.',
            'newLessonVideoUrl.url' => 'Informe uma URL de vídeo válida.',
        ]);

        $module = Module::findOrFail($this->targetModuleId);
        $maxOrder = $module->lessons()->max('order') ?? 0;

        Lesson::create([
            'module_id' => $module->id,
            'course_id' => $this->courseId,
            'company_id' => $module->company_id,
            'title' => $this->newLessonTitle,
            'description' => $this->newLessonDescription ?: null,
            'video_url' => $this->newLessonVideoUrl ?: null,
            'duration_minutes' => $this->newLessonDuration,
            'is_free_preview' => $this->newLessonIsPreview,
            'order' => $maxOrder + 1,
        ]);

        $this->reset(['targetModuleId', 'newLessonTitle', 'newLessonDescription', 'newLessonVideoUrl', 'newLessonDuration', 'newLessonIsPreview', 'showNewLessonModal']);
        $this->dispatch('toast', message: 'Aula adicionada com sucesso!', type: 'success');
    }

    public function deleteLesson(int $lessonId): void
    {
        $lesson = Lesson::where('course_id', $this->courseId)->findOrFail($lessonId);
        $title = $lesson->title;
        $lesson->delete();

        $this->dispatch('toast', message: "Aula '{$title}' removida.", type: 'info');
    }

    public function moveLesson(int $lessonId, string $direction): void
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lessons = Lesson::where('module_id', $lesson->module_id)->orderBy('order')->get();
        $index = $lessons->search(fn ($l) => $l->id === $lessonId);

        if ($index === false) {
            return;
        }

        if ($direction === 'up' && $index > 0) {
            $prev = $lessons[$index - 1];
            $current = $lessons[$index];

            $temp = $current->order;
            $current->update(['order' => $prev->order]);
            $prev->update(['order' => $temp]);
        } elseif ($direction === 'down' && $index < count($lessons) - 1) {
            $next = $lessons[$index + 1];
            $current = $lessons[$index];

            $temp = $current->order;
            $current->update(['order' => $next->order]);
            $next->update(['order' => $temp]);
        }

        $this->dispatch('toast', message: 'Ordem das aulas atualizada.', type: 'success');
    }

    public function openAttachmentModal(int $lessonId): void
    {
        $this->targetLessonId = $lessonId;
        $this->showAttachmentModal = true;
    }

    public function addAttachment(): void
    {
        $this->validate([
            'targetLessonId' => 'required|exists:lessons,id',
            'attachmentTitle' => 'required|string|min:2|max:255',
            'attachmentUrl' => 'required|url|max:2048',
        ], [
            'attachmentTitle.required' => 'O título do anexo é obrigatório.',
            'attachmentUrl.required' => 'Informe a URL do arquivo ou material.',
        ]);

        $lesson = Lesson::findOrFail($this->targetLessonId);

        LessonAttachment::create([
            'lesson_id' => $lesson->id,
            'company_id' => $lesson->company_id,
            'title' => $this->attachmentTitle,
            'file_path' => $this->attachmentUrl,
            'file_type' => 'link',
        ]);

        $this->reset(['targetLessonId', 'attachmentTitle', 'attachmentUrl', 'showAttachmentModal']);
        $this->dispatch('toast', message: 'Material complementar anexado com sucesso!', type: 'success');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = LessonAttachment::findOrFail($attachmentId);
        $attachment->delete();

        $this->dispatch('toast', message: 'Material anexo removido.', type: 'info');
    }

    public function deleteQuiz(int $quizId): void
    {
        $quiz = \App\Models\Quiz::where('course_id', $this->courseId)->findOrFail($quizId);
        $title = $quiz->title;
        $quiz->delete();

        $this->dispatch('toast', message: "Avaliação '{$title}' removida.", type: 'info');
    }

    public function render()
    {
        $course = Course::with(['modules.lessons.attachments', 'quizzes.questions'])->findOrFail($this->courseId);

        return view('livewire.instructor.curriculum-builder', [
            'course' => $course,
        ])->layout('layouts.app', ['title' => 'Grade Curricular — ' . $course->title]);
    }
}
