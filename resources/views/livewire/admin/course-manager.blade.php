<div class="space-y-8">

    <!-- TOPBAR COM BREADCRUMB & METADADOS GLOBAIS -->
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
            <span class="text-slate-900 font-semibold">Cursos</span>
        </nav>

        <!-- Quick Meta & Badges -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-slate-600 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Catálogo Corporativo Global: <strong class="text-slate-800">{{ $totalCourses }} Cursos Multi-Tenant</strong></span>
            </div>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <span class="text-xs text-slate-500">Tenant Master: <strong class="text-slate-700">Alvora Global HQ</strong></span>
        </div>
    </div>

    <!-- HEADER PRINCIPAL DA PÁGINA + AÇÕES -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Gestão Global de Cursos</h1>
            <p class="text-sm text-slate-500 mt-1">
                Gerencie o catálogo master corporativo, parametrização multi-tenant, autorias e governança pedagógica entre empresas clientes.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <!-- Botão Secundário: Exportar Relatório -->
            <button wire:click="exportCsv" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span>Exportar Relatório</span>
            </button>

            <!-- Botão Primário: Adicionar Novo Curso -->
            <a href="{{ route('admin.courses.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/25">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Adicionar Novo Curso</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI CARDS RESUMO EXECUTIVO (STITCH PATTERN) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total de Cursos Globais -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total de Cursos Globais</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $totalCourses }}</div>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    <span>Acervo corporativo centralizado</span>
                </p>
            </div>
        </div>

        <!-- Card 2: Cursos Publicados -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cursos Publicados</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $publishedCourses }}</span>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                        {{ $totalCourses > 0 ? round(($publishedCourses / $totalCourses) * 100) : 0 }}% ativos
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Disponíveis no catálogo dos alunos</p>
            </div>
        </div>

        <!-- Card 3: Em Rascunho / Revisão -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Em Revisão / Rascunhos</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $draftCourses }}</span>
                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                        Rascunhos
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Pendentes de publicação pedagógica</p>
            </div>
        </div>

        <!-- Card 4: Horas de Capacitação -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Horas de Capacitação</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $totalHours }}h</div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $totalModules }} módulos • {{ $totalLessons }} aulas no acervo
                </p>
            </div>
        </div>
    </div>

    <!-- BARRA DE BUSCA E FILTROS RÁPIDOS MULTI-TENANT -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Input de Busca -->
                <div class="relative flex items-center">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           placeholder="Buscar por curso, slug ou instrutor..." 
                           class="w-full h-10 pl-10 pr-3 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" />
                </div>

                <!-- Seletor de Tenant/Empresa -->
                <div class="relative">
                    <select wire:model.live="companyFilter" 
                            class="w-full h-10 px-3 pr-8 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                        <option value="all">Todas as Empresas (Multi-Tenant)</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Seletor de Categoria -->
                <div class="relative">
                    <select wire:model.live="categoryFilter" 
                            class="w-full h-10 px-3 pr-8 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                        <option value="all">Todas as Categorias</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Seletor de Status -->
                <div class="relative">
                    <select wire:model.live="statusFilter" 
                            class="w-full h-10 px-3 pr-8 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
                        <option value="all">Status: Todos</option>
                        <option value="published">Apenas Publicados</option>
                        <option value="draft">Em Rascunho</option>
                        <option value="archived">Arquivados</option>
                    </select>
                </div>
            </div>

            <!-- Botão Limpar Filtros -->
            <div class="flex items-center justify-between lg:justify-end gap-3 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <span class="text-xs text-slate-500 whitespace-nowrap">
                    Mostrando <strong class="text-slate-800">{{ $courses->count() }}</strong> de <strong class="text-slate-800">{{ $courses->total() }}</strong>
                </span>
                <button wire:click="resetFilters" 
                        type="button" 
                        title="Limpar Filtros" 
                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75L14.25 12m0 0l2.25 2.25M14.25 12l2.25-2.25M14.25 12l-2.25 2.25m-6.75 3h12a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0017.25 4.5H7.5a2.25 2.25 0 00-1.688.766L2.25 12l3.562 6.734A2.25 2.25 0 007.5 19.5z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- TABELA CORPORATIVA ROBUSTA MULTI-TENANT -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 min-w-[280px]">Curso & Slug</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Categoria & Trilha</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Tenant Corporativo</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Instrutor Titular</th>
                        <th class="py-3.5 px-4 min-w-[140px]">Conteúdo</th>
                        <th class="py-3.5 px-4 min-w-[120px] text-center">Matrículas</th>
                        <th class="py-3.5 px-4 min-w-[110px] text-center">Status</th>
                        <th class="py-3.5 px-4 w-32 text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($courses as $index => $c)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <!-- Sequencial -->
                            <td class="py-4 px-4 text-center font-mono text-xs text-slate-400 font-semibold">
                                #{{ str_pad($c->id, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Curso & Slug -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden text-slate-400 font-bold text-xs">
                                        @if($c->thumbnail)
                                            <img src="{{ $c->thumbnail }}" alt="{{ $c->title }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('admin.courses.edit', $c->id) }}" class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors truncate">
                                            {{ $c->title }}
                                        </a>
                                        <span class="font-mono text-[11px] text-slate-400 truncate">/{{ $c->slug }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Categoria -->
                            <td class="py-4 px-4">
                                @if($c->category)
                                    <div class="flex flex-col gap-0.5">
                                        <span class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $c->category->name }}
                                        </span>
                                        @if($c->category->parent)
                                            <span class="text-[10px] text-slate-400">↳ {{ $c->category->parent->name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sem Categoria</span>
                                @endif
                            </td>

                            <!-- Tenant Corporativo -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-slate-200">
                                        {{ substr($c->company?->name ?? 'HQ', 0, 2) }}
                                    </div>
                                    <span class="font-medium text-xs text-slate-800 truncate">
                                        {{ $c->company?->name ?? 'Alvora Global HQ' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Instrutor Titular -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($c->instructor?->name ?? 'IN', 0, 2) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-medium text-xs text-slate-800 truncate">{{ $c->instructor?->name ?? 'Instrutor Alvora' }}</span>
                                        <span class="text-[10px] text-slate-400 truncate">{{ $c->instructor?->email ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Conteúdo -->
                            <td class="py-4 px-4">
                                <div class="text-xs">
                                    <p class="font-semibold text-slate-800">{{ $c->modules_count }} mód. • {{ $c->lessons_count }} aulas</p>
                                    <p class="text-[11px] text-slate-400">{{ round(($c->total_duration_minutes ?? 0) / 60, 1) }}h totais</p>
                                </div>
                            </td>

                            <!-- Matrículas -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1 font-semibold text-xs text-slate-700 bg-slate-50 border border-slate-200/70 px-2.5 py-1 rounded-lg">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    {{ $c->enrollments_count }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center">
                                <button wire:click="togglePublish({{ $c->id }})" 
                                        type="button" 
                                        title="Clique para alternar publicação" 
                                        class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $c->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($c->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $c->status === 'published' ? 'bg-emerald-500' : ($c->status === 'draft' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                    <span>{{ $c->status === 'published' ? 'Publicado' : ($c->status === 'draft' ? 'Rascunho' : 'Arquivado') }}</span>
                                </button>
                            </td>

                            <!-- Ações -->
                            <td class="py-4 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Editar -->
                                    <a href="{{ route('admin.courses.edit', $c->id) }}" 
                                       title="Editar Curso" 
                                       class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>

                                    <!-- Ver Grade Curricular -->
                                    <a href="{{ route('admin.courses.curriculum', $c->id) }}" 
                                       title="Ver Grade Curricular" 
                                       class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                        </svg>
                                    </a>

                                    <!-- Excluir -->
                                    <button x-on:click="AlvoraSwal.fire({ icon: 'warning', title: 'Tem certeza?', text: 'Tem certeza de que deseja remover este curso do catálogo?', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Sim, excluir', cancelButtonText: 'Cancelar' }).then(r => { if (r.isConfirmed) $wire.deleteCourse({{ $c->id }}) })"
                                            type="button" 
                                            title="Excluir Curso" 
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
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                Nenhum curso encontrado com os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rodapé da Tabela com Paginação & Metadados -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Mostrando <strong class="text-slate-800">{{ $courses->firstItem() ?? 0 }}–{{ $courses->lastItem() ?? 0 }}</strong> de <strong class="text-slate-800">{{ $courses->total() }} cursos corporativos</strong>
            </div>
            <div>
                {{ $courses->links() }}
            </div>
        </div>
    </div>

</div>
