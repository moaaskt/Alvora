<?php

namespace App\Livewire\Instructor;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class CourseEditor extends Component
{
    public ?int $courseId = null;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $thumbnail = '';
    public string $status = 'draft';
    public ?int $instructor_id = null;
    public string $activeTab = 'basic'; // 'basic' | 'publish'

    protected $queryString = [
        'activeTab' => ['except' => 'basic', 'as' => 'tab'],
    ];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'slug' => 'nullable|string|min:3|max:255',
            'description' => 'nullable|string|max:3000',
            'thumbnail' => 'nullable|string|max:2048',
            'status' => 'required|in:draft,published,archived',
            'instructor_id' => 'required|exists:users,id',
        ];
    }

    protected array $messages = [
        'title.required' => 'O título do treinamento é obrigatório.',
        'title.min' => 'O título deve ter no mínimo 3 caracteres.',
        'instructor_id.required' => 'Selecione o professor/instrutor responsável.',
    ];

    public function mount(?Course $course = null): void
    {
        $requestedTab = request()->query('tab', 'basic');
        $this->activeTab = in_array($requestedTab, ['basic', 'publish']) ? $requestedTab : 'basic';

        if ($course && $course->exists) {
            $this->courseId = $course->id;
            $this->title = $course->title;
            $this->slug = $course->slug;
            $this->description = $course->description ?? '';
            $this->thumbnail = $course->thumbnail ?? '';
            $this->status = $course->status;
            $this->instructor_id = $course->instructor_id;
        } else {
            $this->instructor_id = Auth::id();
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['basic', 'publish'])) {
            $this->activeTab = $tab;
        }
    }

    public function updatedTitle(): void
    {
        if (empty($this->courseId) || empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function save()
    {
        $validated = $this->validate();

        $user = Auth::user();
        $companyId = $user->company_id;

        // Auto-generate slug if empty
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure unique slug per tenant
        $slugExists = Course::withoutGlobalScopes()
            ->where('slug', $validated['slug'])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($this->courseId, fn ($q) => $q->where('id', '!=', $this->courseId))
            ->exists();

        if ($slugExists) {
            $validated['slug'] = Course::generateUniqueSlug(Str::slug($validated['slug']), $companyId);
        }

        if ($this->courseId) {
            $course = Course::findOrFail($this->courseId);
            $course->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?: null,
                'thumbnail' => $validated['thumbnail'] ?: null,
                'status' => $validated['status'],
                'instructor_id' => $validated['instructor_id'],
            ]);

            $this->dispatch('toast', message: 'Informações do curso atualizadas com sucesso!', type: 'success');
            return null;
        } else {
            $course = Course::create([
                'company_id' => $companyId,
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?: null,
                'thumbnail' => $validated['thumbnail'] ?: null,
                'status' => $validated['status'],
                'instructor_id' => $validated['instructor_id'],
            ]);

            $this->dispatch('toast', message: 'Curso criado com sucesso! Agora adicione os módulos e aulas.', type: 'success');
            return redirect()->route('instructor.courses.curriculum', $course);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $instructors = User::where(function ($q) {
            $q->where('role', 'instructor')
              ->orWhere('role', 'company_admin');
        })->get();

        if ($instructors->isEmpty()) {
            $instructors = collect([$user]);
        }

        $course = $this->courseId 
            ? Course::withCount(['modules', 'lessons', 'quizzes'])->find($this->courseId) 
            : null;

        return view('livewire.instructor.course-editor', [
            'instructors' => $instructors,
            'course' => $course,
        ])->layout('layouts.app', ['title' => ($this->courseId ? 'Editar Curso' : 'Novo Curso') . ' — ALVORA']);
    }
}
