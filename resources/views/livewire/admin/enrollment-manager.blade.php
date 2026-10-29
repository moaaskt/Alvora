<div class="space-y-6" x-data="{ toastMessage: '', toastType: 'info', showToast: false }" 
     x-on:toast.window="toastMessage = $event.detail.message; toastType = $event.detail.type || 'info'; showToast = true; setTimeout(() => { showToast = false; }, 4000)">

    <!-- TOAST NOTIFICATION FLUTUANTE -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-white text-sm font-medium"
         :class="{
             'bg-emerald-600': toastType === 'success',
             'bg-rose-600': toastType === 'error',
             'bg-blue-600': toastType === 'info'
         }"
         style="display: none;">
        <span class="material-symbols-outlined text-xl" x-text="toastType === 'success' ? 'check_circle' : (toastType === 'error' ? 'error' : 'info')"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- 1. SUB-HEADER COM BREADCRUMB & METAS GLOBAIS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <!-- Breadcrumbs -->
        <nav class="flex items-center text-xs font-medium text-slate-500 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Painel do Administrador</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900 font-semibold">Matrículas</span>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-blue-600 font-semibold">Histórico de Matrículas</span>
        </nav>

        <!-- Quick Meta & Badges -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-slate-600 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Catálogo Corporativo Global: <strong class="text-slate-800">{{ number_format($activeEnrollments, 0, ',', '.') }} Matrículas Ativas</strong></span>
            </div>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <span class="text-xs text-slate-500">Tenant Master: <strong class="text-slate-700">Alvora Global HQ</strong></span>
        </div>
    </div>

    <!-- 2. TÍTULO DA PÁGINA + AÇÕES PRINCIPAIS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Gestão de Matrículas e Acessos</h1>
            <p class="text-sm text-slate-500 mt-1">Auditoria global de inscrições, governança de licenças corporativas B2B e concessão manual de acessos.</p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <!-- Botão Exportar CSV -->
            <button wire:click="exportCsv" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs cursor-pointer">
                <span class="material-symbols-outlined text-lg text-slate-500">download</span>
                <span>Exportar Relatório</span>
            </button>

            <!-- Botão Primário Matricular Aluno -->
            <button wire:click="openCreateModal" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/25 cursor-pointer">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>Matricular Aluno</span>
            </button>
        </div>
    </div>

    <!-- 3. KPI CARDS (4 COLUNAS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Geral -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Geral de Matrículas</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">how_to_reg</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($totalEnrollments, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="inline-flex items-center text-emerald-600 font-bold">
                    <span class="material-symbols-outlined text-sm mr-0.5">arrow_upward</span>
                    +{{ $monthEnrollments }}
                </span>
                <span>neste mês corrente</span>
            </div>
        </div>

        <!-- Card 2: Matrículas Ativas -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Matrículas Ativas</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">verified_user</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($activeEnrollments, 0, ',', '.') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $totalEnrollments > 0 ? round(($activeEnrollments / $totalEnrollments) * 100, 1) : 100 }}% ativação
                </span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
                <span>Engajamento em conformidade</span>
            </div>
        </div>

        <!-- Card 3: Acessos Vitalícios -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Acessos Vitalícios</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">all_inclusive</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($lifetimeEnrollments, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 font-medium">
                    ({{ $totalEnrollments > 0 ? round(($lifetimeEnrollments / $totalEnrollments) * 100, 1) : 0 }}% do total)
                </span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="material-symbols-outlined text-sm text-purple-600">corporate_fare</span>
                <span>Contratos corporativos perenes</span>
            </div>
        </div>

        <!-- Card 4: Novas no Mês -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Novas Matrículas no Mês</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">trending_up</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-extrabold text-blue-600">+{{ $monthEnrollments }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Mês Atual
                </span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Taxa contínua de onboarding</span>
            </div>
        </div>
    </div>

    <!-- 4. TOOLBAR & FILTROS CORPORATIVOS -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <!-- Input de Busca -->
        <div class="relative flex-1 min-w-[280px]">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <span class="material-symbols-outlined text-lg">search</span>
            </div>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por aluno (nome, e-mail) ou título do curso..." 
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" />
        </div>

        <!-- Grupo de Filtros -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filtro Empresa (Multi-tenant) -->
            <div class="relative">
                <select wire:model.live="companyFilter" 
                        class="h-10 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer appearance-none">
                    <option value="all">Todas as Empresas (Multi-Tenant)</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">expand_more</span>
            </div>

            <!-- Filtro Período -->
            <div class="relative">
                <select wire:model.live="periodFilter" 
                        class="h-10 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer appearance-none">
                    <option value="all">Todo o Período</option>
                    <option value="30days">Últimos 30 dias</option>
                    <option value="quarter">Este Trimestre</option>
                    <option value="year">Ano Atual</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">expand_more</span>
            </div>

            <!-- Filtro Status -->
            <div class="relative">
                <select wire:model.live="statusFilter" 
                        class="h-10 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer appearance-none">
                    <option value="all">Status: Todos</option>
                    <option value="active">Ativo</option>
                    <option value="lifetime">Acesso Vitalício</option>
                    <option value="expired">Expirado</option>
                    <option value="dropped">Revogado</option>
                    <option value="completed">Concluído</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">expand_more</span>
            </div>

            <!-- Reset Filters -->
            <button wire:click="resetFilters" 
                    type="button" 
                    title="Limpar Filtros"
                    class="h-10 w-10 border border-slate-200 hover:bg-slate-100 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-lg">filter_alt_off</span>
            </button>
        </div>
    </div>

    <!-- 5. TABELA DE AUDITORIA E HISTÓRICO DE MATRÍCULAS -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Aluno & Identificação</th>
                        <th class="py-3 px-4">Curso Matriculado</th>
                        <th class="py-3 px-4">Empresa (Tenant)</th>
                        <th class="py-3 px-4">Data da Matrícula</th>
                        <th class="py-3 px-4">Validade / Licença</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-800">
                    @forelse($enrollments as $enrollment)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- # ID -->
                            <td class="py-3.5 px-4 text-center font-mono text-xs text-slate-400">
                                {{ sprintf('%02d', $enrollment->id) }}
                            </td>

                            <!-- Aluno & Identificação -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                        {{ strtoupper(substr($enrollment->user?->name ?? 'A', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 leading-tight truncate">
                                            {{ $enrollment->user?->name ?? 'Usuário Desconhecido' }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5 truncate">
                                            {{ $enrollment->user?->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Curso Matriculado -->
                            <td class="py-3.5 px-4">
                                <div>
                                    <div class="font-semibold text-slate-900 leading-tight">
                                        {{ $enrollment->course?->title ?? 'Curso Removido' }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                            <span class="material-symbols-outlined text-[12px]">folder</span>
                                            {{ $enrollment->course?->category?->name ?? 'Geral' }}
                                        </span>
                                        @if($enrollment->progress_percent > 0)
                                            <span class="text-[11px] font-semibold text-blue-600">
                                                {{ $enrollment->progress_percent }}% concluído
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Empresa (Tenant) -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    {{ $enrollment->company?->name ?? 'Global HQ' }}
                                </span>
                            </td>

                            <!-- Data da Matrícula -->
                            <td class="py-3.5 px-4">
                                <span class="text-xs text-slate-700 block font-semibold">
                                    {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('d M Y') : '-' }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('H:i \B\R\T') : '-' }}
                                </span>
                            </td>

                            <!-- Validade / Licença -->
                            <td class="py-3.5 px-4">
                                @if($enrollment->isLifetime())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="material-symbols-outlined text-xs">all_inclusive</span>
                                        Acesso Vitalício
                                    </span>
                                @elseif($enrollment->isExpired())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="material-symbols-outlined text-xs">history_toggle_off</span>
                                        Expirou em {{ $enrollment->expires_at->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        Expira em {{ $enrollment->expires_at->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                @if($enrollment->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Ativo
                                    </span>
                                @elseif($enrollment->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Concluído
                                    </span>
                                @elseif($enrollment->status === 'dropped')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Revogado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Ações -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($enrollment->status === 'active')
                                        <button wire:click="confirmRevoke({{ $enrollment->id }})" 
                                                type="button"
                                                class="p-1.5 hover:bg-rose-50 rounded-lg text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" 
                                                title="Revogar Acesso do Aluno">
                                            <span class="material-symbols-outlined text-lg">person_remove</span>
                                        </button>
                                    @endif

                                    <button wire:confirm="Tem certeza que deseja excluir permanentemente o registro desta matrícula?"
                                            wire:click="deleteEnrollmentPermanently({{ $enrollment->id }})" 
                                            type="button"
                                            class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-700 transition-colors cursor-pointer" 
                                            title="Excluir Registro">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <span class="material-symbols-outlined text-2xl">how_to_reg</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Nenhuma matrícula encontrada</h3>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm">
                                        Não encontramos registros de matrícula com os filtros atuais. Experimente ajustar a busca ou adicionar uma matrícula manualmente.
                                    </p>
                                    <button wire:click="openCreateModal" 
                                            type="button"
                                            class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-base">add</span>
                                        <span>Matricular Novo Aluno</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rodapé da Tabela: Paginação Livewire -->
        @if($enrollments->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-white">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>

    <!-- ================================================================= -->
    <!-- MODAL: MATRICULAR ALUNO MANUALMENTE -->
    <!-- ================================================================= -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="relative w-full max-w-2xl bg-white border border-slate-200/90 rounded-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between p-6 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <span class="material-symbols-outlined text-xl">person_add</span>
                            </div>
                            <h2 class="text-lg md:text-xl font-bold text-slate-900 tracking-tight">
                                Matricular Aluno Manualmente
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Conceda acesso a cursos do catálogo corporativo com trilha de auditoria e governança de licenças.
                        </p>
                    </div>
                    <button wire:click="closeCreateModal" 
                            type="button"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                <!-- Modal Form Body -->
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Campo 1: Selecionar Aluno -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Selecionar Aluno Corporativo <span class="text-rose-600">*</span>
                        </label>

                        @if($selectedStudent)
                            <div class="flex items-center justify-between p-3 bg-blue-50/50 border border-blue-200 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        {{ strtoupper(substr($selectedStudent->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $selectedStudent->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">{{ $selectedStudent->email }} • {{ $selectedStudent->company?->name ?? 'Global HQ' }}</p>
                                    </div>
                                </div>
                                <button wire:click="$set('selectedUserId', null)" 
                                        type="button" 
                                        class="text-xs font-bold text-blue-600 hover:underline px-2 py-1 cursor-pointer">
                                    Alterar
                                </button>
                            </div>
                        @else
                            <!-- Busca com autocomplete de alunos -->
                            <div class="space-y-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <span class="material-symbols-outlined text-lg">search</span>
                                    </div>
                                    <input wire:model.live.debounce.250ms="studentSearch" 
                                           type="text" 
                                           placeholder="Digite nome ou e-mail do colaborador para pesquisar..." 
                                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" />
                                </div>

                                <!-- Lista de sugestões de alunos -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden max-h-48 overflow-y-auto divide-y divide-slate-100 bg-white">
                                    @forelse($availableStudents as $student)
                                        <button wire:click="selectStudent({{ $student->id }})" 
                                                type="button" 
                                                class="w-full text-left p-2.5 hover:bg-slate-50 flex items-center justify-between transition-colors cursor-pointer">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <span class="text-xs font-semibold text-slate-900 block">{{ $student->name }}</span>
                                                    <span class="text-[11px] text-slate-500 font-mono">{{ $student->email }}</span>
                                                </div>
                                            </div>
                                            <span class="text-[11px] text-slate-500 px-2 py-0.5 rounded bg-slate-100 font-medium">
                                                {{ $student->company?->name ?? 'Global HQ' }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-xs text-slate-500">
                                            Nenhum estudante encontrado com esse termo.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        @error('selectedUserId')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campo 2: Selecionar Curso -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Selecionar Curso do Catálogo <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <select wire:model="selectedCourseId" 
                                    class="w-full h-11 pl-3 pr-9 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer appearance-none">
                                <option value="">-- Escolha um treinamento corporativo --</option>
                                @foreach($availableCourses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">expand_more</span>
                        </div>
                        @error('selectedCourseId')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campo 3: Vigência e Tipo de Acesso -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-purple-600 text-2xl">workspace_premium</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Acesso Vitalício Corporativo</p>
                                    <p class="text-xs text-slate-500">Sem expiração definida para este colaborador.</p>
                                </div>
                            </div>
                            
                            <!-- Toggle Switch Ativo -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model.live="isLifetime" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Data Limite de Expiração se NÃO vitalício -->
                        @if(!$isLifetime)
                            <div class="pt-3 border-t border-slate-200">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Data de Expiração do Acesso <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model="expiresAt" 
                                       type="date" 
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       class="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" />
                                @error('expiresAt')
                                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Campo 4: Notificação do Aluno -->
                    <div class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-xl">
                        <input wire:model="notifyStudent" 
                               id="notifyStudentCheckbox" 
                               type="checkbox" 
                               class="mt-1 h-4 w-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer" />
                        <label for="notifyStudentCheckbox" class="cursor-pointer">
                            <span class="block text-xs font-bold text-slate-900">Disparar e-mail de confirmação ao colaborador</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">O colaborador receberá notificação oficial de matrícula corporativa.</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer Action Buttons -->
                <div class="p-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                    <button wire:click="closeCreateModal" 
                            type="button" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button wire:click="enrollStudent" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="enrollStudent" class="material-symbols-outlined text-lg">check</span>
                        <span wire:loading wire:target="enrollStudent" class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                        <span>Confirmar Matrícula</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- MODAL: CONFIRMAR REVOGAÇÃO DE ACESSO -->
    <!-- ================================================================= -->
    @if($showRevokeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="relative w-full max-w-md bg-white border border-slate-200/90 rounded-2xl shadow-2xl overflow-hidden p-6 space-y-4 animate-in zoom-in-95 duration-200">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Revogar Acesso do Aluno</h3>
                        <p class="text-xs text-slate-500">Esta ação suspenderá imediatamente o acesso ao curso.</p>
                    </div>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    Tem certeza de que deseja revogar esta matrícula? O colaborador deixará de ter acesso ao conteúdo e às aulas do curso. O histórico poderá ser reativado a qualquer momento.
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="cancelRevoke" 
                            type="button" 
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button wire:click="revokeEnrollment" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 transition-colors shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-base">person_remove</span>
                        <span>Confirmar Revogação</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

