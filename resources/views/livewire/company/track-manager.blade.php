<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- SUB-NAV / BREADCRUMBS -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Dashboard</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Home</span>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">Trilhas de Aprendizado</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 font-mono">
                Total: {{ $tracks->count() }} {{ $tracks->count() === 1 ? 'Trilha' : 'Trilhas' }}
            </span>
        </div>
    </div>

    <!-- HEADER DA PÁGINA (ESTILO STITCH) -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div class="flex flex-col gap-1 max-w-3xl">
            <h1 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight">
                Trilhas de Aprendizado Corporativas
            </h1>
            <p class="text-xs text-slate-500">
                Agrupe cursos em jornadas corporativas sequenciais por departamento e acompanhe o progresso de capacitação dos colaboradores.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openCreateModal" 
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Nova Trilha</span>
            </button>
        </div>
    </header>

    <!-- BARRA DE FILTROS & BUSCA NO PADRÃO STITCH -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="relative w-full sm:w-80">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Buscar por título ou departamento..."
                   class="w-full h-9 pl-9 pr-4 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span>Organização: <strong class="text-slate-800 font-semibold">{{ auth()->user()->company?->name ?? 'Acme Corp' }}</strong></span>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL: CARDS DE TRILHAS OU EMPTY STATE DO STITCH -->
    @if($tracks->isEmpty())
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-10 sm:p-14 text-center max-w-3xl w-full mx-auto relative overflow-hidden">
            <div class="relative flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 shadow-sm">
                    <svg class="w-8 h-8 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-label-sm text-label-sm bg-slate-100 text-slate-600 mb-3 font-medium">
                    Total de trilhas corporativas: 0
                </span>
                <h2 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight mb-2">
                    Nenhuma trilha encontrada
                </h2>
                <p class="font-body-md text-body-md text-slate-500 max-w-xl mb-6 leading-relaxed text-xs sm:text-sm">
                    Sua organização (<strong class="text-slate-800 font-semibold">{{ auth()->user()->company?->name ?? 'Acme Corporation Brasil' }}</strong>) ainda não possui trilhas de aprendizado cadastradas. Trilhas conectam cursos em jornadas sequenciais de desenvolvimento para seus colaboradores e aceleram o onboarding de novos talentos.
                </p>

                <button wire:click="openCreateModal" 
                        type="button" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-xs shadow hover:bg-blue-700 transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    <span>Criar Primeira Trilha Agora</span>
                </button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tracks as $track)
                <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 transition-all p-6 shadow-sm flex flex-col justify-between gap-5 group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            @if($track->department)
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ $track->department }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    Corporativo Geral
                                </span>
                            @endif

                            @if($track->is_mandatory)
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    Obrigatória
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    Opcional
                                </span>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                {{ $track->title }}
                            </h3>
                            @if($track->description)
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $track->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Cursos da Trilha -->
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-2">
                                Cursos da Jornada ({{ $track->courses->count() }}):
                            </span>
                            <div class="space-y-1.5 max-h-36 overflow-y-auto">
                                @forelse($track->courses as $course)
                                    <div class="flex items-center gap-2 text-xs text-slate-700 bg-slate-50 p-2 rounded-lg border border-slate-200/70">
                                        <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold shrink-0">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="truncate font-medium">{{ $course->title }}</span>
                                    </div>
                                @empty
                                    <p class="text-[11px] text-slate-400 italic">Nenhum curso adicionado ainda.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Colaboradores Matriculados -->
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $track->enrollments->count() }} colaboradores vinculados</span>
                        </div>
                    </div>

                    <!-- Botões de Ação do Card -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <button wire:click="editTrack({{ $track->id }})" 
                                type="button" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                            </svg>
                            <span>Editar Trilha</span>
                        </button>

                        <button wire:click="deleteTrack({{ $track->id }})" 
                                wire:confirm="Tem certeza de que deseja remover a trilha '{{ $track->title }}'?"
                                type="button" 
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                title="Excluir Trilha">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL: NOVA / EDITAR TRILHA (PADRÃO STITCH) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl p-6 relative my-8">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        {{ $editingTrackId ? 'Editar Trilha Corporativa' : 'Criar Nova Trilha de Aprendizado' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-base">✕</button>
                </div>

                <form wire:submit="saveTrack" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título da Trilha *</label>
                        <input type="text" wire:model="title" placeholder="Ex: Onboarding de Engenharia de Software"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border @error('title') border-rose-500 @else border-slate-200 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Departamento / Área</label>
                            <input type="text" wire:model="department" placeholder="Ex: Tecnologia, Vendas, RH"
                                   class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        </div>

                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_mandatory" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-semibold text-slate-700">Trilha Obrigatória para os Inscritos</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição do Objetivo</label>
                        <textarea wire:model="description" rows="2" placeholder="Explique os objetivos pedagógicos desta jornada..."
                                  class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs"></textarea>
                    </div>

                    <!-- Seleção de Cursos da Trilha -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Selecione os Cursos que Compõem a Trilha:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-44 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                            @forelse($availableCourses as $course)
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 text-xs cursor-pointer hover:bg-blue-50/50 transition-colors">
                                    <input type="checkbox" 
                                           wire:click="toggleCourse({{ $course->id }})"
                                           {{ in_array($course->id, $selectedCourses) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="truncate font-medium text-slate-800">{{ $course->title }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-slate-400 p-2 col-span-2">Nenhum curso publicado disponível.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Atribuição de Colaboradores -->
                    <div class="pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-slate-700">Atribuir Colaboradores:</label>
                            <label class="flex items-center gap-1.5 text-xs text-blue-600 cursor-pointer">
                                <input type="checkbox" wire:model.live="assignAllEmployees" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Inscrever todos os colaboradores</span>
                            </label>
                        </div>

                        @if(!$assignAllEmployees)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-36 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                                @forelse($availableEmployees as $emp)
                                    <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 text-xs cursor-pointer hover:bg-blue-50/50 transition-colors">
                                        <input type="checkbox" 
                                               wire:model="selectedUsers"
                                               value="{{ $emp->id }}"
                                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="truncate text-slate-800 font-medium">{{ $emp->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-400 p-2 col-span-2">Nenhum colaborador encontrado.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button wire:click="$set('showModal', false)" type="button" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm">
                            Salvar Trilha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
