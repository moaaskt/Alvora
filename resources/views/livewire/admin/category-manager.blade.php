<div class="space-y-8">

    <!-- TOP HEADER COM BREADCRUMB & PESQUISA GLOBAL (Sub-header) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs font-medium text-slate-500 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Painel do Administrador</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
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
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span>Exportar CSV</span>
            </button>

            <!-- Botão Primário Adicionar Categoria -->
            <button wire:click="openCreateModal()" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/25 cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Adicionar Nova Categoria</span>
            </button>
        </div>
    </div>

    <!-- BARRA DE PESQUISA & FILTROS RÁPIDOS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Input de Busca -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
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
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                    </svg>
                                @elseif($rootCat->icon === 'paint-brush')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                    </svg>
                                @elseif($rootCat->icon === 'sparkles')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                                    </svg>
                                @elseif($rootCat->icon === 'users')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.199a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Menu de Ações Rápidas -->
                            <div class="flex items-center gap-1">
                                <button wire:click="openEditModal({{ $rootCat->id }})" 
                                        type="button" 
                                        title="Editar Categoria" 
                                        class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="openCreateModal({{ $rootCat->id }})" 
                                        type="button" 
                                        title="+ Adicionar Subcategoria" 
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $rootCat->id }})" 
                                        type="button" 
                                        title="Excluir Categoria" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
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
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                            </svg>
                                        @elseif($cat->icon === 'paint-brush')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                            </svg>
                                        @elseif($cat->icon === 'sparkles')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                            </svg>
                                        @elseif($cat->icon === 'users')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.199a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                            </svg>
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
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>

                                    <!-- Mover para Baixo -->
                                    <button wire:click="moveOrder({{ $cat->id }}, 'down')" 
                                            type="button" 
                                            title="Mover para Baixo" 
                                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Editar -->
                                    <button wire:click="openEditModal({{ $cat->id }})" 
                                            type="button" 
                                            title="Editar Categoria" 
                                            class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Adicionar Subcategoria -->
                                    <button wire:click="openCreateModal({{ $cat->id }})" 
                                            type="button" 
                                            title="Adicionar Subcategoria" 
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>

                                    <!-- Excluir -->
                                    <button wire:click="confirmDelete({{ $cat->id }})" 
                                            type="button" 
                                            title="Excluir Categoria" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
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
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
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

