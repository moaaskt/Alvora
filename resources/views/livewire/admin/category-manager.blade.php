<div class="space-y-8">

    <!-- TOP HEADER COM BREADCRUMB & PESQUISA GLOBAL (Sub-header) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs font-medium text-slate-500 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Home</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-300" />
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Painel do Administrador</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-300" />
            <span class="text-slate-900 font-semibold">Categorias</span>
        </nav>

        <!-- Quick Meta & Badges -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-slate-600 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Catálogo Corporativo Global: <strong class="text-slate-800">{{ $totalActiveSub }} Subcategorias Ativas</strong></span>
            </div>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <span class="text-xs text-slate-500">Tenant Master: <strong class="text-slate-700">Alvora Global HQ</strong></span>
        </div>
    </div>

    <!-- TÍTULO DA PÁGINA + AÇÕES PRINCIPAIS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Categorias e Subcategorias de Cursos</h1>
            <p class="text-sm text-slate-500 mt-1">Gerencie as categorias principais e suas respectivas subcategorias para organização do catálogo de cursos.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Botão Exportar -->
            <button wire:click="exportCsv" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs cursor-pointer">
                <x-icon name="upload" class="w-4 h-4 text-slate-500" />
                <span>Exportar CSV</span>
            </button>

            <!-- Botão Primário Adicionar Categoria -->
            <button wire:click="openCreateModal()" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/25 cursor-pointer">
                <x-icon name="plus-lg" class="w-4 h-4 text-white" />
                <span>Adicionar Nova Categoria</span>
            </button>
        </div>
    </div>

    <!-- BARRA DE PESQUISA & FILTROS RÁPIDOS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Input de Busca -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <x-icon name="search" class="w-4 h-4 text-slate-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por categoria, subcategoria ou slug..." 
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" />
        </div>

        <!-- Filtros Rápidos -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500">Status:</label>
                <select wire:model.live="statusFilter" 
                        class="text-xs font-medium bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                    <option value="todos">Todos os status</option>
                    <option value="ativo">Apenas Ativos</option>
                    <option value="inativo">Apenas Inativos</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500">Estrutura:</label>
                <select wire:model.live="structureFilter" 
                        class="text-xs font-medium bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                    <option value="todas">Todas as categorias</option>
                    <option value="principais">Apenas Principais</option>
                    <option value="sub">Apenas Subcategorias</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SEÇÃO 1: CARDS EM GRID COM AS CATEGORIAS PRINCIPAIS -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Categorias Principais</h2>
                <p class="text-xs text-slate-500">Visão panorâmica dos eixos pedagógicos corporativos</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                {{ $rootCategories->count() }} categorias raiz cadastradas
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse($rootCategories as $index => $rootCat)
                @php
                    $colors = [
                        0 => ['bg' => 'bg-blue-50', 'border' => 'border-blue-100', 'text' => 'text-blue-600', 'hover' => 'group-hover:text-blue-600'],
                        1 => ['bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'text' => 'text-purple-600', 'hover' => 'group-hover:text-purple-600'],
                        2 => ['bg' => 'bg-amber-50', 'border' => 'border-amber-100', 'text' => 'text-amber-600', 'hover' => 'group-hover:text-amber-600'],
                        3 => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'text' => 'text-emerald-600', 'hover' => 'group-hover:text-emerald-600'],
                    ];
                    $palette = $colors[$index % 4];
                @endphp
                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-2xs hover:shadow-md hover:border-blue-200 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <div class="w-12 h-12 rounded-xl {{ $palette['bg'] }} border {{ $palette['border'] }} flex items-center justify-center {{ $palette['text'] }} group-hover:scale-105 transition-transform shadow-2xs">
                                @if($rootCat->icon === 'code')
                                    <x-icon name="code-slash" class="w-6 h-6" />
                                @elseif($rootCat->icon === 'paint-brush')
                                    <x-icon name="brush-fill" class="w-6 h-6" />
                                @elseif($rootCat->icon === 'sparkles')
                                    <x-icon name="stars" class="w-6 h-6" />
                                @elseif($rootCat->icon === 'users')
                                    <x-icon name="people-fill" class="w-6 h-6" />
                                @else
                                    <x-icon name="journal-bookmark-fill" class="w-6 h-6" />
                                @endif
                            </div>

                            <!-- Menu de Ações Rápidas -->
                            <div class="flex items-center gap-1">
                                <button wire:click="openEditModal({{ $rootCat->id }})" 
                                        type="button" 
                                        title="Editar Categoria" 
                                        class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                                    <x-icon name="pencil-square" class="w-4 h-4" />
                                </button>
                                <button wire:click="openCreateModal({{ $rootCat->id }})" 
                                        type="button" 
                                        title="+ Adicionar Subcategoria" 
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors cursor-pointer">
                                    <x-icon name="plus-lg" class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $rootCat->id }})" 
                                        type="button" 
                                        title="Excluir Categoria" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                                    <x-icon name="trash3-fill" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Título com subcategorias entre parênteses: ex: Web Development (4) -->
                        <div class="mt-4">
                            <h3 class="font-bold text-slate-900 text-base leading-snug {{ $palette['hover'] }} transition-colors">
                                {{ $rootCat->name }} 
                                <span class="{{ $palette['text'] }} font-semibold text-sm">
                                    ({{ $rootCat->subcategories->count() }})
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                {{ $rootCat->description ?: 'Organização dos programas de capacitação e treinamentos desta trilha corporativa.' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $rootCat->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                            <span class="text-xs font-medium text-slate-600">
                                {{ $rootCat->total_courses_count }} {{ $rootCat->total_courses_count === 1 ? 'Curso vinculado' : 'Cursos vinculados' }}
                            </span>
                        </div>
                        <span class="text-[11px] font-mono font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                            /{{ $rootCat->slug }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-slate-200">
                    <p class="text-sm text-slate-500">Nenhuma categoria raiz cadastrada no momento.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- SEÇÃO 2: TABELA COMPLETA DE CATEGORIAS E SUBCATEGORIAS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-6 py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Mapeamento Hierárquico Completo</h2>
                <p class="text-xs text-slate-500">Lista detalhada de categorias pai com suas subcategorias associadas, contagem de cursos e status</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="flex items-center gap-1 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ $totalRoot }} Categorias Raiz
                </span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-1 font-medium">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> {{ $totalSub }} Subcategorias Vinculadas
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5 w-14">#</th>
                        <th class="py-3.5 px-5 min-w-[220px]">Ícone & Categoria Principal</th>
                        <th class="py-3.5 px-5 min-w-[340px]">Subcategorias Vinculadas (Chips)</th>
                        <th class="py-3.5 px-5 text-center w-28">Cursos Ativos</th>
                        <th class="py-3.5 px-5 w-28 text-center">Status</th>
                        <th class="py-3.5 px-5 w-44 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($categoriesList as $index => $cat)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <!-- Sequencial -->
                            <td class="py-4 px-5 font-mono text-xs text-slate-400 font-semibold">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Ícone e Categoria Principal -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                        @if($cat->icon === 'code')
                                            <x-icon name="code-slash" class="w-5 h-5" />
                                        @elseif($cat->icon === 'paint-brush')
                                            <x-icon name="brush-fill" class="w-5 h-5" />
                                        @elseif($cat->icon === 'sparkles')
                                            <x-icon name="stars" class="w-5 h-5" />
                                        @elseif($cat->icon === 'users')
                                            <x-icon name="people-fill" class="w-5 h-5" />
                                        @else
                                            <x-icon name="journal-bookmark-fill" class="w-5 h-5" />
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                            {{ $cat->name }}
                                        </span>
                                        @if(!$cat->isRoot() && $cat->parent)
                                            <span class="block text-[11px] text-slate-400">
                                                Subcategoria de: <strong class="text-slate-600">{{ $cat->parent->name }}</strong>
                                            </span>
                                        @else
                                            <span class="block text-xs font-mono text-slate-400">slug: {{ $cat->slug }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Subcategorias vinculadas (Chips) -->
                            <td class="py-4 px-5">
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    @forelse($cat->subcategories as $sub)
                                        <span wire:click="openEditModal({{ $sub->id }})" 
                                              title="Clique para editar {{ $sub->name }}" 
                                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 cursor-pointer transition-colors">
                                            <span>{{ $sub->name }}</span>
                                            <span class="text-[10px] text-slate-500 font-bold">({{ $sub->courses_count ?? $sub->courses()->count() }})</span>
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Nenhuma subcategoria vinculada</span>
                                    @endforelse

                                    <!-- Botão de Inserção Rápida de Subcategoria -->
                                    <button wire:click="openCreateModal({{ $cat->id }})" 
                                            type="button" 
                                            title="Adicionar Subcategoria a {{ $cat->name }}" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-700 text-xs font-bold transition-colors cursor-pointer">
                                        +
                                    </button>
                                </div>
                            </td>

                            <!-- Cursos Ativos -->
                            <td class="py-4 px-5 text-center">
                                <span class="font-bold text-slate-800 text-sm">
                                    {{ $cat->isRoot() ? $cat->total_courses_count : $cat->courses_count }}
                                </span>
                                <span class="block text-[11px] text-slate-400">treinamentos</span>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-5 text-center">
                                <button wire:click="toggleStatus({{ $cat->id }})" 
                                        type="button" 
                                        title="Clique para alternar status" 
                                        class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }} transition-colors">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $cat->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ $cat->is_active ? 'Ativo' : 'Inativo' }}</span>
                                </button>
                            </td>

                            <!-- Ações -->
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Mover para Cima -->
                                    <button wire:click="moveOrder({{ $cat->id }}, 'up')" 
                                            type="button" 
                                            title="Mover para Cima" 
                                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                                        <x-icon name="chevron-up" class="w-4 h-4" />
                                    </button>

                                    <!-- Mover para Baixo -->
                                    <button wire:click="moveOrder({{ $cat->id }}, 'down')" 
                                            type="button" 
                                            title="Mover para Baixo" 
                                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                                        <x-icon name="chevron-down" class="w-4 h-4" />
                                    </button>

                                    <!-- Editar -->
                                    <button wire:click="openEditModal({{ $cat->id }})" 
                                            type="button" 
                                            title="Editar Categoria" 
                                            class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors cursor-pointer">
                                        <x-icon name="pencil-square" class="w-4 h-4" />
                                    </button>

                                    <!-- Adicionar Subcategoria -->
                                    <button wire:click="openCreateModal({{ $cat->id }})" 
                                            type="button" 
                                            title="Adicionar Subcategoria" 
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors cursor-pointer">
                                        <x-icon name="plus-lg" class="w-4 h-4" />
                                    </button>

                                    <!-- Excluir -->
                                    <button wire:click="confirmDelete({{ $cat->id }})" 
                                            type="button" 
                                            title="Excluir Categoria" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                                        <x-icon name="trash3-fill" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Nenhuma categoria encontrada com os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rodapé da Tabela com Paginação & Metadados -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Mostrando <strong class="text-slate-800">{{ $categoriesList->firstItem() ?? 0 }}–{{ $categoriesList->lastItem() ?? 0 }}</strong> de <strong class="text-slate-800">{{ $categoriesList->total() }} categorias</strong>
            </div>
            <div>
                {{ $categoriesList->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- MODAL INTERATIVO DE CADASTRO/EDIÇÃO DE CATEGORIA / SUBCAT  -->
    <!-- ========================================================== -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden flex flex-col">
                
                <!-- Cabeçalho do Modal -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h3 class="text-base font-bold text-slate-900">
                                {{ $editingId ? 'Editar Categoria' : 'Cadastrar Nova Categoria ou Subcategoria' }}
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Defina os parâmetros taxonômicos para organizar cursos corporativos.</p>
                    </div>
                    <button wire:click="closeModal" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                        <x-icon name="x-lg" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Formulário do Modal -->
                <form wire:submit="saveCategory" class="p-6 space-y-4">
                    
                    <!-- Categoria Pai -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Categoria Pai <span class="text-slate-400 font-normal">(Opcional: Deixe em branco para Categoria Principal)</span>
                        </label>
                        <select wire:model.live="parent_id" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                            <option value="">-- Nenhuma (Criar como Categoria Principal Raiz) --</option>
                            @foreach($rootCategories as $rc)
                                @if(!$editingId || $editingId !== $rc->id)
                                    <option value="{{ $rc->id }}">{{ $rc->name }} (Categoria Principal)</option>
                                @endif
                            @endforeach
                        </select>
                        @error('parent_id') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Selecione uma categoria caso esteja cadastrando uma nova subcategoria vinculada.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nome da Categoria -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nome da Categoria / Subcategoria <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model.live.debounce.300ms="name" 
                                   type="text" 
                                   placeholder="Ex: Arquitetura Cloud & DevOps" 
                                   class="w-full bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" />
                            @error('name') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Slug da URL <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model="slug" 
                                   type="text" 
                                   placeholder="Ex: arquitetura-cloud-devops" 
                                   class="w-full bg-slate-50 border @error('slug') border-rose-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" />
                            @error('slug') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Ícone -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Ícone Representativo <span class="text-slate-400 font-normal">(Recomendado para categorias principais)</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <select wire:model="icon" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                                <option value="">Sem ícone (Padrão)</option>
                                <option value="code">Código / Desenvolvimento (code)</option>
                                <option value="paint-brush">Design & Artes (paint-brush)</option>
                                <option value="sparkles">Inteligência Artificial (sparkles)</option>
                                <option value="users">Gestão & Pessoas (users)</option>
                                <option value="book-open">Educação & Livros (book-open)</option>
                                <option value="chart-bar">Analytics & Métricas (chart-bar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Descrição Pedagógica / Resumo
                        </label>
                        <textarea wire:model="description" 
                                  rows="3" 
                                  placeholder="Breve descrição dos tópicos abordados nesta categoria para orientar instrutores e alunos corporativos..." 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"></textarea>
                    </div>

                    <!-- Status e Visibilidade -->
                    <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="is_active" 
                                   type="checkbox" 
                                   class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer" />
                            <span class="text-xs font-medium text-slate-700">Categoria Ativa no Catálogo Público</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Visível imediatamente após salvar</span>
                    </div>

                    <!-- Ações do Modal -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button wire:click="closeModal" 
                                type="button" 
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-sm shadow-blue-500/20 transition-all cursor-pointer">
                            {{ $editingId ? 'Salvar Alterações' : 'Salvar Categoria' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO                           -->
    <!-- ========================================================== -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl p-6 flex flex-col gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <x-icon name="exclamation-triangle-fill" class="w-6 h-6 text-rose-600" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900">Excluir Categoria?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Tem certeza de que deseja excluir a categoria <strong class="text-slate-800">{{ $deletingCategoryName }}</strong>?
                    </p>
                    @if($deletingSubcategoriesCount > 0)
                        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            <strong>Atenção:</strong> Esta categoria raiz possui <strong>{{ $deletingSubcategoriesCount }} subcategorias</strong> que também serão removidas ou desvinculadas.
                        </div>
                    @endif
                    @if($deletingCoursesCount > 0)
                        <div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                            Existem <strong>{{ $deletingCoursesCount }} cursos</strong> associados a esta categoria. Eles permanecerão no sistema, mas terão a categoria redefinida para vazia.
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="cancelDelete" 
                            type="button" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button wire:click="deleteCategory" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm shadow-rose-500/20 transition-all cursor-pointer">
                        Confirmar Exclusão
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

