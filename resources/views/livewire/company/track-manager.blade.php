<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Trilhas de Aprendizado Corporativas</h1>
            <p class="text-sm text-slate-400 mt-1">
                Agrupe cursos em sequências orientadas por departamento e acompanhe o progresso de capacitação da equipe.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" type="button"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Nova Trilha</span>
            </button>
        </div>
    </div>

    <!-- Barra de Filtro / Busca -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por título ou departamento..."
                   class="w-full bg-slate-900/80 border border-slate-800 rounded-xl px-4 py-2.5 pl-10 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="text-xs text-slate-400">
            Total de trilhas: <strong class="text-white">{{ $tracks->count() }}</strong>
        </div>
    </div>

    <!-- Grid de Trilhas -->
    @if($tracks->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/80 space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Nenhuma trilha encontrada</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">
                    Crie sua primeira trilha corporativa combinando múltiplos cursos com foco em integração de colaboradores ou capacitação técnica.
                </p>
            </div>
            <button wire:click="openCreateModal" type="button" class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                <span>Criar primeira trilha agora</span> &rarr;
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tracks as $track)
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all flex flex-col justify-between shadow-xl">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            @if($track->department)
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                    {{ $track->department }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-400">
                                    Geral
                                </span>
                            @endif

                            @if($track->is_mandatory)
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                    Obrigatória
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-400">
                                    Opcional
                                </span>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-white tracking-tight">{{ $track->title }}</h3>
                            @if($track->description)
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $track->description }}</p>
                            @endif
                        </div>

                        <!-- Cursos da Trilha -->
                        <div class="pt-2">
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Cursos Associados ({{ $track->courses->count() }}):</span>
                            <div class="mt-2 space-y-1.5 max-h-32 overflow-y-auto">
                                @forelse($track->courses as $course)
                                    <div class="flex items-center gap-2 text-xs text-slate-300 bg-slate-950/60 p-2 rounded-lg border border-slate-800/80">
                                        <span class="w-4 h-4 rounded-full bg-indigo-600/30 text-indigo-400 flex items-center justify-center text-[10px] font-bold">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="truncate">{{ $course->title }}</span>
                                    </div>
                                @empty
                                    <p class="text-[11px] text-slate-500 italic">Nenhum curso adicionado ainda.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé do Card com Métricas e Ações -->
                    <div class="pt-5 mt-4 border-t border-slate-800/80 flex items-center justify-between text-xs">
                        <div class="text-slate-400">
                            <span class="font-bold text-white">{{ $track->enrollments->count() }}</span> colaboradores
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="editTrack({{ $track->id }})" type="button"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors">
                                Editar
                            </button>
                            <button wire:click="deleteTrack({{ $track->id }})"
                                    wire:confirm="Tem certeza de que deseja excluir esta trilha corporativa?"
                                    type="button"
                                    class="px-2.5 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 text-xs font-semibold border border-rose-800/40 transition-colors">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal de Criação / Edição de Trilha -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-white">
                            {{ $editingTrackId ? 'Editar Trilha Corporativa' : 'Criar Nova Trilha Corporativa' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Defina a sequência temática de cursos e associe aos colaboradores.
                        </p>
                    </div>
                    <button wire:click="$set('showModal', false)" type="button" class="text-slate-400 hover:text-white text-sm">
                        ✕
                    </button>
                </div>

                <form wire:submit="saveTrack" class="space-y-5">
                    <!-- Título -->
                    <div>
                        <label for="title" class="block text-xs font-semibold text-slate-300 mb-1.5">Título da Trilha *</label>
                        <input type="text" id="title" wire:model="title" placeholder="Ex: Onboarding de Engenharia de Software"
                               class="w-full bg-slate-950 border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">Descrição / Metas de Aprendizado</label>
                        <textarea id="description" wire:model="description" rows="3" placeholder="Descreva os objetivos pedagógicos desta trilha..."
                                  class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Departamento e Obrigatoriedade -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="department" class="block text-xs font-semibold text-slate-300 mb-1.5">Departamento ou Área</label>
                            <input type="text" id="department" wire:model="department" placeholder="Ex: Vendas, RH, TI"
                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                <input type="checkbox" wire:model="is_mandatory" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                <span class="font-semibold text-white">Trilha de Cumprimento Obrigatório</span>
                            </label>
                        </div>
                    </div>

                    <!-- Seleção e Ordenação de Cursos -->
                    <div class="space-y-2 pt-2">
                        <label class="block text-xs font-semibold text-slate-300">
                            Cursos da Trilha (Selecione na sequência pedagógica desejada)
                        </label>

                        @if($availableCourses->isEmpty())
                            <p class="text-xs text-amber-400 bg-amber-500/10 p-3 rounded-xl border border-amber-500/20">
                                Não há cursos publicados disponíveis. Publique ao menos um curso para associá-lo a uma trilha.
                            </p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-slate-950/60 rounded-xl border border-slate-800">
                                @foreach($availableCourses as $course)
                                    @php $isSelected = in_array($course->id, $selectedCourses); @endphp
                                    <div wire:click="toggleCourse({{ $course->id }})"
                                         class="p-2.5 rounded-lg border text-xs cursor-pointer flex items-center justify-between transition-colors {{ $isSelected ? 'bg-indigo-600/20 border-indigo-500/50 text-white' : 'bg-slate-900 border-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                                        <div class="flex items-center gap-2 truncate">
                                            <div class="w-4 h-4 rounded border flex items-center justify-center {{ $isSelected ? 'bg-indigo-600 border-indigo-500 text-white' : 'border-slate-700 bg-slate-950' }}">
                                                @if($isSelected) ✓ @endif
                                            </div>
                                            <span class="truncate">{{ $course->title }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Ordem dos Cursos Selecionados -->
                            @if(count($selectedCourses) > 0)
                                <div class="mt-3 p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-2">
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        Sequência dos Cursos (Use as setas para reordenar):
                                    </div>
                                    <div class="space-y-1.5">
                                        @foreach($selectedCourses as $idx => $cId)
                                            @php $c = $availableCourses->firstWhere('id', $cId); @endphp
                                            @if($c)
                                                <div class="flex items-center justify-between bg-slate-900 px-3 py-1.5 rounded-lg text-xs text-white border border-slate-800">
                                                    <span class="font-medium"><strong>#{{ $idx + 1 }}</strong> {{ $c->title }}</span>
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" wire:click="moveCourseUp({{ $idx }})" class="p-1 hover:text-indigo-400 text-slate-400" title="Mover para cima">▲</button>
                                                        <button type="button" wire:click="moveCourseDown({{ $idx }})" class="p-1 hover:text-indigo-400 text-slate-400" title="Mover para baixo">▼</button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Atribuição de Colaboradores -->
                    <div class="space-y-2 pt-2 border-t border-slate-800/80">
                        <label class="block text-xs font-semibold text-slate-300">
                            Atribuição de Colaboradores à Trilha
                        </label>

                        <div class="flex items-center gap-2 mb-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                <input type="checkbox" wire:model.live="assignAllEmployees" class="rounded bg-slate-950 border-slate-700 text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                                <span class="font-medium text-emerald-400">Atribuir a todos os colaboradores da empresa</span>
                            </label>
                        </div>

                        @if(! $assignAllEmployees && $availableEmployees->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-950/60 rounded-xl border border-slate-800">
                                @foreach($availableEmployees as $emp)
                                    @php $isUserSelected = in_array($emp->id, $selectedUsers); @endphp
                                    <div wire:click="toggleUser({{ $emp->id }})"
                                         class="p-2 rounded-lg border text-xs cursor-pointer flex items-center justify-between transition-colors {{ $isUserSelected ? 'bg-emerald-600/20 border-emerald-500/50 text-white' : 'bg-slate-900 border-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                                        <div class="flex items-center gap-2 truncate">
                                            <div class="w-4 h-4 rounded border flex items-center justify-center {{ $isUserSelected ? 'bg-emerald-600 border-emerald-500 text-white' : 'border-slate-700 bg-slate-950' }}">
                                                @if($isUserSelected) ✓ @endif
                                            </div>
                                            <span class="truncate">{{ $emp->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button wire:click="$set('showModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-md shadow-indigo-600/30 flex items-center gap-2 cursor-pointer">
                            <span wire:loading.remove>Salvar Trilha</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
