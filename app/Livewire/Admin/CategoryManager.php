<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryManager extends Component
{
    use WithPagination;

    // Filtros de busca e visualização
    public string $search = '';
    public string $statusFilter = 'todos'; // 'todos' | 'ativo' | 'inativo'
    public string $structureFilter = 'todas'; // 'todas' | 'principais' | 'sub'

    // Controle dos Modais
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // Estado para Exclusão
    public ?int $deletingCategoryId = null;
    public string $deletingCategoryName = '';
    public int $deletingSubcategoriesCount = 0;
    public int $deletingCoursesCount = 0;

    // Campos do Formulário
    public ?int $editingId = null;
    public ?int $parent_id = null;
    public string $name = '';
    public string $slug = '';
    public string $icon = '';
    public string $description = '';
    public bool $is_active = true;
    public int $order = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'todos'],
        'structureFilter' => ['except' => 'todas'],
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:120',
            'slug' => 'required|string|min:2|max:140|unique:categories,slug,' . ($this->editingId ?? 'NULL') . ',id',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ];
    }

    protected array $messages = [
        'name.required' => 'O nome da categoria é obrigatório.',
        'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
        'slug.required' => 'O slug é obrigatório.',
        'slug.unique' => 'Já existe uma categoria cadastrada com este slug.',
        'parent_id.exists' => 'A categoria pai selecionada é inválida.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStructureFilter(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        if (empty($this->editingId) || empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function openCreateModal(?int $parentId = null): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->parent_id = $parentId;
        $this->name = '';
        $this->slug = '';
        $this->icon = $parentId ? '' : 'folder';
        $this->description = '';
        $this->is_active = true;

        if ($parentId) {
            $maxOrder = Category::where('parent_id', $parentId)->max('order') ?? 0;
            $this->order = $maxOrder + 1;
        } else {
            $maxOrder = Category::whereNull('parent_id')->max('order') ?? 0;
            $this->order = $maxOrder + 1;
        }

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;
        $this->parent_id = $category->parent_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->icon = $category->icon ?? '';
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->order = $category->order ?? 0;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        // Se slug estiver vazio, gera a partir do nome
        if (empty($this->slug) && !empty($this->name)) {
            $this->slug = Str::slug($this->name);
        }

        $validated = $this->validate();

        // Evita que uma categoria seja pai de si mesma
        if ($this->editingId && (int)$this->parent_id === (int)$this->editingId) {
            $this->addError('parent_id', 'Uma categoria não pode ser subcategoria de si mesma.');
            return;
        }

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $category->update([
                'parent_id' => $validated['parent_id'] ?: null,
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'icon' => $validated['icon'] ?: null,
                'description' => $validated['description'] ?: null,
                'is_active' => $this->is_active,
                'order' => $validated['order'] ?? 0,
            ]);

            $this->dispatch('toast', message: "Categoria '{$category->name}' atualizada com sucesso!", type: 'success');
        } else {
            $category = Category::create([
                'parent_id' => $validated['parent_id'] ?: null,
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'icon' => $validated['icon'] ?: null,
                'description' => $validated['description'] ?: null,
                'is_active' => $this->is_active,
                'order' => $validated['order'] ?? 0,
            ]);

            $this->dispatch('toast', message: "Categoria '{$category->name}' criada com sucesso!", type: 'success');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $statusStr = $category->is_active ? 'ativada' : 'desativada';
        $this->dispatch('toast', message: "Categoria '{$category->name}' foi {$statusStr} com sucesso.", type: 'info');
    }

    public function moveOrder(int $id, string $direction): void
    {
        $category = Category::findOrFail($id);
        
        // Pega os irmãos do mesmo nível
        $query = Category::where('parent_id', $category->parent_id)->orderBy('order')->orderBy('name');
        $siblings = $query->get();

        $currentIndex = $siblings->search(fn($c) => $c->id === $category->id);
        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($targetIndex >= 0 && $targetIndex < $siblings->count()) {
            $swapCategory = $siblings->get($targetIndex);

            // Troca a ordem entre os dois
            $tempOrder = $category->order;
            $category->order = $swapCategory->order;
            $swapCategory->order = $tempOrder;

            // Se as ordens forem iguais, ajusta
            if ($category->order === $swapCategory->order) {
                if ($direction === 'up') {
                    $category->order = max(0, $swapCategory->order - 1);
                } else {
                    $category->order = $swapCategory->order + 1;
                }
            }

            $category->save();
            $swapCategory->save();

            $this->dispatch('toast', message: 'Ordem de exibição atualizada com sucesso.', type: 'info');
        }
    }

    public function confirmDelete(int $id): void
    {
        $category = Category::withCount(['subcategories', 'courses'])->findOrFail($id);
        $this->deletingCategoryId = $category->id;
        $this->deletingCategoryName = $category->name;
        $this->deletingSubcategoriesCount = $category->subcategories_count;
        $this->deletingCoursesCount = $category->courses_count;

        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingCategoryId = null;
        $this->deletingCategoryName = '';
        $this->deletingSubcategoriesCount = 0;
        $this->deletingCoursesCount = 0;
    }

    public function deleteCategory(): void
    {
        if (!$this->deletingCategoryId) {
            return;
        }

        $category = Category::findOrFail($this->deletingCategoryId);
        $name = $category->name;

        // Desassocia cursos vinculados para evitar que fiquem órfãos ou quebrados
        Course::withoutGlobalScopes()
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        $this->dispatch('toast', message: "Categoria '{$name}' excluída com sucesso.", type: 'info');
        $this->cancelDelete();
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alvora_categorias_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM para abrir perfeitamente no Excel
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Nível',
                'Categoria Pai',
                'Nome da Categoria',
                'Slug',
                'Ícone',
                'Cursos Vinculados',
                'Status',
                'Ordem',
                'Criado em',
            ], ';');

            $categories = Category::with(['parent', 'courses', 'subcategories'])
                ->orderBy('parent_id')
                ->orderBy('order')
                ->get();

            foreach ($categories as $cat) {
                fputcsv($handle, [
                    $cat->id,
                    $cat->isRoot() ? 'Categoria Principal' : 'Subcategoria',
                    $cat->parent?->name ?? 'Nenhuma (Raiz)',
                    $cat->name,
                    $cat->slug,
                    $cat->icon ?? '-',
                    $cat->isRoot() ? $cat->total_courses_count : $cat->courses->count(),
                    $cat->is_active ? 'Ativo' : 'Inativo',
                    $cat->order,
                    $cat->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render()
    {
        // Categorias Principais (para os cards em Grid do topo e para o select de pai)
        $rootCategories = Category::with(['subcategories' => fn($q) => $q->orderBy('order')])
            ->withCount('courses')
            ->root()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Consulta filtrada para a Tabela Hierárquica
        $tableQuery = Category::with(['subcategories' => fn($q) => $q->orderBy('order'), 'parent'])
            ->withCount(['courses', 'subcategories']);

        // Filtro de busca
        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $tableQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('slug', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhereHas('subcategories', function ($sq) use ($term) {
                      $sq->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                  });
            });
        }

        // Filtro de status
        if ($this->statusFilter === 'ativo') {
            $tableQuery->where('is_active', true);
        } elseif ($this->statusFilter === 'inativo') {
            $tableQuery->where('is_active', false);
        }

        // Filtro de estrutura
        if ($this->structureFilter === 'principais') {
            $tableQuery->root();
        } elseif ($this->structureFilter === 'sub') {
            $tableQuery->whereNotNull('parent_id');
        } else {
            // No modo padrão 'todas', a tabela exibe as principais como linhas e as subcategorias como chips
            $tableQuery->root();
        }

        $categoriesList = $tableQuery->orderBy('order')->orderBy('name')->paginate(10);

        // Contadores gerais para os badges do header
        $totalRoot = Category::root()->count();
        $totalSub = Category::whereNotNull('parent_id')->count();
        $totalActiveSub = Category::whereNotNull('parent_id')->active()->count();

        return view('livewire.admin.category-manager', [
            'rootCategories' => $rootCategories,
            'categoriesList' => $categoriesList,
            'totalRoot' => $totalRoot,
            'totalSub' => $totalSub,
            'totalActiveSub' => $totalActiveSub,
        ])->layout('layouts.admin', [
            'title' => 'Gestão de Categorias e Subcategorias — ALVORA Super Admin',
            'breadcrumb' => 'Categorias',
        ]);
    }
}
