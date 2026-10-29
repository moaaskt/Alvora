<?php

namespace App\Livewire\Instructor;

use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
    public ?int $category_id = null;
    public string $activeTab = 'basic'; // 'basic' | 'quizzes' | 'publish'

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
            'category_id' => 'nullable|exists:categories,id',
        ];
    }

    protected array $messages = [
        'title.required' => 'O título do treinamento é obrigatório.',
        'title.min' => 'O título deve ter no mínimo 3 caracteres.',
        'instructor_id.required' => 'Selecione o professor/instrutor responsável.',
        'category_id.exists' => 'A categoria selecionada é inválida.',
    ];

    public function mount(?Course $course = null): void
    {
        $requestedTab = request()->query('tab', 'basic');
        $this->activeTab = in_array($requestedTab, ['basic', 'quizzes', 'publish']) ? $requestedTab : 'basic';

        if ($course && $course->exists) {
            $this->courseId = $course->id;
            $this->title = $course->title;
            $this->slug = $course->slug;
            $this->description = $course->description ?? '';
            $this->thumbnail = $course->thumbnail ?? '';
            $this->status = $course->status;
            $this->instructor_id = $course->instructor_id;
            $this->category_id = $course->category_id;
        } else {
            $this->instructor_id = Auth::id() ?: User::whereIn('role', ['instructor', 'company_admin', 'admin_alvora'])->value('id');
            // Categoria padrão opcional (primeira de desenvolvimento ou vazia)
            $this->category_id = Category::value('id');
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['basic', 'quizzes', 'publish'])) {
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
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $firstError = collect($e->validator->errors()->all())->first();
            $this->dispatch('toast', message: $firstError ?: 'Corrija os erros do formulário.', type: 'error');
            throw $e;
        }

        $user = Auth::user();
        $companyId = $user?->company_id;

        if (!$companyId) {
            if (!empty($validated['instructor_id'])) {
                $instructorUser = User::find($validated['instructor_id']);
                $companyId = $instructorUser?->company_id;
            }
            if (!$companyId) {
                $companyId = Company::first()?->id ?? 1;
            }
        }

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
                'category_id' => $validated['category_id'] ?: null,
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
                'category_id' => $validated['category_id'] ?: null,
            ]);

            $this->dispatch('toast', message: 'Curso criado com sucesso! Agora adicione os módulos e aulas.', type: 'success');
            return redirect()->route('instructor.courses.curriculum', $course);
        }
    }

    public function deleteQuiz(int $quizId): void
    {
        if (!$this->courseId) {
            return;
        }

        $quiz = Quiz::where('course_id', $this->courseId)->findOrFail($quizId);
        $title = $quiz->title;
        $quiz->delete();

        $this->dispatch('toast', message: "Avaliação '{$title}' removida com sucesso.", type: 'info');
    }

    public function render()
    {
        $user = Auth::user();
        $instructors = User::where(function ($q) {
            $q->where('role', 'instructor')
              ->orWhere('role', 'company_admin')
              ->orWhere('role', 'admin_alvora');
        })->get();

        if ($instructors->isEmpty()) {
            $instructors = collect([$user]);
        }

        // Categorias hierárquicas reais da plataforma
        $categories = Category::with('subcategories')->root()->active()->orderBy('order')->orderBy('name')->get();

        $course = $this->courseId 
            ? Course::withCount(['modules', 'lessons', 'quizzes'])
                ->with(['quizzes.questions', 'quizzes.module'])
                ->find($this->courseId) 
            : null;

        return view('livewire.instructor.course-editor', [
            'instructors' => $instructors,
            'categories' => $categories,
            'course' => $course,
        ])->layout('layouts.instructor', [
            'title' => ($this->courseId ? 'Atualizar: ' . $this->title : 'Criar Novo Treinamento') . ' — ALVORA',
            'breadcrumb' => $this->courseId ? 'Atualizar Curso' : 'Novo Curso',
        ]);
    }
}
