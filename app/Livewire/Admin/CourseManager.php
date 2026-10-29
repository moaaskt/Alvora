<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $companyFilter = 'all';
    public string $categoryFilter = 'all';
    public string $statusFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'companyFilter' => ['except' => 'all'],
        'categoryFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->companyFilter = 'all';
        $this->categoryFilter = 'all';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function togglePublish(int $courseId): void
    {
        $course = Course::withoutGlobalScopes()->findOrFail($courseId);

        $newStatus = $course->status === 'published' ? 'draft' : 'published';
        $course->update(['status' => $newStatus]);

        $message = $newStatus === 'published'
            ? "Curso '{$course->title}' foi publicado com sucesso!"
            : "Curso '{$course->title}' foi revertido para rascunho.";

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function deleteCourse(int $courseId): void
    {
        $course = Course::withoutGlobalScopes()->findOrFail($courseId);

        if ($course->certificates()->exists()) {
            $this->dispatch('toast', message: "Não é possível excluir o curso '{$course->title}' pois ele possui certificados emitidos.", type: 'error');
            return;
        }

        $title = $course->title;
        $course->delete();

        $this->dispatch('toast', message: "Curso '{$title}' removido com sucesso.", type: 'info');
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alvora_cursos_globais_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Título do Curso',
                'Slug',
                'Empresa (Tenant)',
                'Categoria',
                'Instrutor',
                'E-mail Instrutor',
                'Módulos',
                'Aulas',
                'Carga Horária (min)',
                'Matrículas Ativas',
                'Status',
                'Criado em',
            ], ';');

            $courses = Course::withoutGlobalScopes()
                ->with(['company', 'instructor', 'category'])
                ->withCount(['modules', 'lessons', 'enrollments'])
                ->withSum('lessons as total_duration_minutes', 'duration_minutes')
                ->latest('id')
                ->get();

            foreach ($courses as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->title,
                    $c->slug,
                    $c->company?->name ?? 'Global HQ',
                    $c->category?->name ?? 'Não categorizado',
                    $c->instructor?->name ?? 'N/A',
                    $c->instructor?->email ?? 'N/A',
                    $c->modules_count,
                    $c->lessons_count,
                    $c->total_duration_minutes ?? 0,
                    $c->enrollments_count,
                    $c->status === 'published' ? 'Publicado' : ($c->status === 'draft' ? 'Rascunho' : 'Arquivado'),
                    $c->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render()
    {
        // Métricas Executivas Globais (sem filtro de tenant)
        $totalCourses = Course::withoutGlobalScopes()->count();
        $publishedCourses = Course::withoutGlobalScopes()->where('status', 'published')->count();
        $draftCourses = Course::withoutGlobalScopes()->where('status', 'draft')->count();
        $totalDurationMinutes = Lesson::withoutGlobalScopes()->sum('duration_minutes') ?? 0;
        $totalHours = round($totalDurationMinutes / 60, 1);
        $totalModules = \App\Models\Module::withoutGlobalScopes()->count();
        $totalLessons = Lesson::withoutGlobalScopes()->count();

        // Listas para Filtros
        $companies = Company::orderBy('name')->get();
        $categories = Category::root()->active()->orderBy('name')->get();

        // Consulta filtrada com busca multi-tenant
        $query = Course::withoutGlobalScopes()
            ->with(['company', 'instructor', 'category.parent'])
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->withSum('lessons as total_duration_minutes', 'duration_minutes');

        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('slug', 'like', $term)
                  ->orWhereHas('company', fn($sq) => $sq->where('name', 'like', $term))
                  ->orWhereHas('instructor', fn($sq) => $sq->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }

        if ($this->companyFilter !== 'all') {
            $query->where('company_id', $this->companyFilter);
        }

        if ($this->categoryFilter !== 'all') {
            $catId = (int)$this->categoryFilter;
            $category = Category::with('subcategories')->find($catId);
            if ($category) {
                $ids = array_merge([$catId], $category->subcategories->pluck('id')->all());
                $query->whereIn('category_id', $ids);
            } else {
                $query->where('category_id', $catId);
            }
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $courses = $query->latest('id')->paginate(10);

        return view('livewire.admin.course-manager', [
            'courses' => $courses,
            'totalCourses' => $totalCourses,
            'publishedCourses' => $publishedCourses,
            'draftCourses' => $draftCourses,
            'totalHours' => $totalHours,
            'totalModules' => $totalModules,
            'totalLessons' => $totalLessons,
            'companies' => $companies,
            'categories' => $categories,
        ])->layout('layouts.admin', [
            'title' => 'Gestão Global de Cursos & Catálogo Multi-Tenant — ALVORA Super Admin',
            'breadcrumb' => 'Cursos',
        ]);
    }
}
