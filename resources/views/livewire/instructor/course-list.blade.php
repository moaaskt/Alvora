<div class="px-space-xl py-space-lg flex flex-col gap-5 max-w-[1600px] mx-auto w-full">
    <!-- HEADER DA PÁGINA -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
        <div class="flex flex-col gap-1">
            <h1 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight">Meus Cursos</h1>
            <p class="font-body-md text-body-md text-slate-500">
                Catálogo de Treinamentos corporativos: publique novos programas e acompanhe métricas de retenção por curso.
            </p>
        </div>
        <div class="flex items-center gap-space-sm shrink-0">
            <a href="{{ route('instructor.courses.create') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-slate-700 hover:bg-slate-50 transition-colors text-label-md font-label-md border border-slate-200 shadow-sm">
                <svg class="w-4 h-4 text-blue-600 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                </svg>
                <span>Assistente de IA</span>
            </a>
            <a href="{{ route('instructor.courses.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all duration-150 text-label-md font-semibold shadow-sm">
                <svg class="w-4 h-4 shrink-0 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Criar Novo Curso</span>
            </a>
        </div>
    </header>

    <!-- CARD CONTAINER DA TABELA PADRÃO STITCH -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <!-- BARRA SUPERIOR DA TABELA: ABAS DE STATUS E CAMPO PROCURAR -->
        <div class="px-5 py-4 border-b border-slate-200/80 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-white">
            <!-- Abas / Filtro Rápido de Status -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex bg-slate-100 p-1 rounded-lg border border-slate-200/60">
                    <button wire:click="$set('statusFilter', 'all')" 
                            type="button" 
                            class="px-3 py-1 rounded-md text-xs transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-800 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        Todos <span class="ml-1 {{ $statusFilter === 'all' ? 'text-blue-600 font-semibold' : 'text-slate-400 font-normal' }}">{{ $totalCount }}</span>
                    </button>
                    <button wire:click="$set('statusFilter', 'published')" 
                            type="button" 
                            class="px-3 py-1 rounded-md text-xs transition-colors {{ $statusFilter === 'published' ? 'bg-white text-slate-800 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        Publicados <span class="ml-1 {{ $statusFilter === 'published' ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">{{ $publishedCount }}</span>
                    </button>
                    <button wire:click="$set('statusFilter', 'draft')" 
                            type="button" 
                            class="px-3 py-1 rounded-md text-xs transition-colors {{ $statusFilter === 'draft' ? 'bg-white text-slate-800 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        Rascunhos <span class="ml-1 {{ $statusFilter === 'draft' ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">{{ $draftCount }}</span>
                    </button>
                </div>
            </div>

            <!-- Top Right: "Procurar: [Input field]" fiel ao Stitch -->
            <div class="flex items-center gap-2 justify-end">
                <label class="text-xs font-medium text-slate-600 shrink-0" for="table-search">Procurar:</label>
                <div class="relative w-full sm:w-64">
                    <input wire:model.live.debounce.300ms="search" 
                           class="w-full h-8 pl-3 pr-3 rounded-md bg-white border border-slate-300 text-slate-800 placeholder:text-slate-400 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all" 
                           id="table-search" 
                           placeholder="Título ou descrição..." 
                           type="text">
                    <div wire:loading wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-3.5 w-3.5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABELA DE DADOS -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[960px]">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200 text-xs font-semibold text-slate-600 select-none">
                        <!-- Col 1: # -->
                        <th class="py-3 px-4 w-12 text-slate-500 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>#</span>
                            </div>
                        </th>
                        <!-- Col 2: Título -->
                        <th class="py-3 px-4 min-w-[300px] text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Título do Treinamento</span>
                            </div>
                        </th>
                        <!-- Col 3: Categoria -->
                        <th class="py-3 px-4 text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Categoria</span>
                            </div>
                        </th>
                        <!-- Col 4: Lição e seção -->
                        <th class="py-3 px-4 text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Estrutura</span>
                            </div>
                        </th>
                        <!-- Col 5: Aluno matriculado -->
                        <th class="py-3 px-4 text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Matrículas</span>
                            </div>
                        </th>
                        <!-- Col 6: Status -->
                        <th class="py-3 px-4 text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Status</span>
                            </div>
                        </th>
                        <!-- Col 7: Preço / Modalidade -->
                        <th class="py-3 px-4 text-slate-600 font-semibold" scope="col">
                            <div class="flex items-center gap-1.5">
                                <span>Modalidade</span>
                            </div>
                        </th>
                        <!-- Col 8: Ações -->
                        <th class="py-3 px-4 text-center text-slate-600 font-semibold w-28" scope="col">
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Ações</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($courses as $index => $course)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <!-- # ID -->
                            <td class="py-3.5 px-4 text-slate-500 font-medium">
                                {{ $courses->firstItem() + $index }}
                            </td>

                            <!-- TÍTULO & INSTRUTOR (CLICÁVEL PARA ATUALIZAR/EDITAR) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('instructor.courses.edit', $course) }}" class="relative w-16 h-10 rounded-md overflow-hidden shrink-0 border border-slate-200 shadow-2xs bg-slate-100 block group-hover:ring-2 group-hover:ring-blue-500/30 transition-all">
                                        @if($course->thumbnail_url)
                                            <img class="w-full h-full object-cover" src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-xs uppercase">
                                                {{ substr($course->title, 0, 2) }}
                                            </div>
                                        @endif
                                    </a>
                                    <div class="flex flex-col gap-0.5">
                                        <a href="{{ route('instructor.courses.edit', $course) }}" 
                                           class="text-blue-600 hover:text-blue-700 font-semibold text-sm leading-snug line-clamp-1 transition-colors"
                                           title="Clique para editar: {{ $course->title }}">
                                            {{ $course->title }}
                                        </a>
                                        <span class="text-slate-400 text-xs font-normal">
                                            Instrutor: {{ $course->instructor?->name ?? 'Não atribuído' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- CATEGORIA -->
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded bg-slate-100 border border-slate-200/80 text-slate-600 text-xs font-medium">
                                    Treinamento Corporativo
                                </span>
                            </td>

                            <!-- LIÇÃO E SEÇÃO -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-0.5 text-slate-500 font-medium">
                                    <span>Seções: {{ $course->modules_count }}</span>
                                    <span>Lições: {{ $course->lessons_count }}</span>
                                </div>
                            </td>

                            <!-- ALUNOS MATRICULADOS -->
                            <td class="py-3.5 px-4 text-slate-600 font-normal">
                                <span class="font-medium text-slate-800">{{ $course->enrollments_count }}</span> {{ $course->enrollments_count === 1 ? 'Matrícula' : 'Matrículas' }}
                            </td>

                            <!-- STATUS -->
                            <td class="py-3.5 px-4">
                                @if($course->isPublished())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        Ativo
                                    </span>
                                @elseif($course->isDraft())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                        Rascunho
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        Arquivado
                                    </span>
                                @endif
                            </td>

                            <!-- MODALIDADE / PREÇO -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col items-start gap-0.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold text-xs border border-slate-200/60">
                                        Incluso
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-normal">( Vitalício )</span>
                                </div>
                            </td>

                            <!-- AÇÕES COM DROPDOWN INTERATIVO NO ESTILO STITCH -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            @click.outside="open = false" 
                                            class="w-8 h-8 mx-auto rounded-full border border-blue-300 text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors shadow-2xs" 
                                            title="Opções" 
                                            type="button">
                                        <svg class="w-4 h-4 stroke-[2]" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="6" r="1.5"></circle>
                                            <circle cx="12" cy="12" r="1.5"></circle>
                                            <circle cx="12" cy="18" r="1.5"></circle>
                                        </svg>
                                    </button>

                                    <!-- Menu Dropdown -->
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100" 
                                         x-transition:enter-start="transform opacity-0 scale-95" 
                                         x-transition:enter-end="transform opacity-100 scale-100" 
                                         x-transition:leave="transition ease-in duration-75" 
                                         x-transition:leave-start="transform opacity-100 scale-100" 
                                         x-transition:leave-end="transform opacity-0 scale-95" 
                                         class="absolute right-0 z-50 mt-1 w-44 origin-top-right rounded-xl bg-white p-1 text-xs shadow-xl ring-1 ring-black/5 focus:outline-none border border-slate-200" 
                                         style="display: none;">
                                        <a href="{{ route('instructor.courses.edit', $course) }}" 
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors font-medium">
                                            <svg class="w-4 h-4 text-blue-600 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                            </svg>
                                            <span>Editar Curso</span>
                                        </a>

                                        <a href="{{ route('instructor.courses.curriculum', $course) }}" 
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors font-medium">
                                            <svg class="w-4 h-4 text-emerald-600 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"></path>
                                            </svg>
                                            <span>Grade de Aulas</span>
                                        </a>

                                        <button wire:click="togglePublish({{ $course->id }})" 
                                                @click="open = false"
                                                type="button" 
                                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors font-medium text-left">
                                            @if($course->isPublished())
                                                <svg class="w-4 h-4 text-amber-500 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                                </svg>
                                                <span>Pausar (Rascunho)</span>
                                            @else
                                                <svg class="w-4 h-4 text-emerald-600 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                                </svg>
                                                <span>Publicar Curso</span>
                                            @endif
                                        </button>

                                        <div class="my-1 border-t border-slate-100"></div>

                                        <button x-on:click="open = false; AlvoraSwal.fire({ icon: 'warning', title: 'Tem certeza?', text: 'Tem certeza de que deseja remover o curso \'{{ $course->title }}\'? Todos os módulos e lições serão excluídos.', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Sim, excluir', cancelButtonText: 'Cancelar' }).then(r => { if (r.isConfirmed) $wire.deleteCourse({{ $course->id }}) })" 
                                                type="button" 
                                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors font-medium text-left">
                                            <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                            </svg>
                                            <span>Excluir</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-6 h-6 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <h3 class="text-sm font-semibold text-slate-800">Nenhum treinamento encontrado</h3>
                                        <p class="text-xs text-slate-400">Tente ajustar seus termos de pesquisa ou filtros de status.</p>
                                    </div>
                                    <a href="{{ route('instructor.courses.create') }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-medium text-xs hover:bg-blue-700 transition-colors">
                                        + Cadastrar Novo Curso
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- BARRA DE PAGINAÇÃO / RODAPÉ DA TABELA (ESTILO STITCH) -->
        <footer class="px-5 py-3.5 bg-slate-50/50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <span class="text-slate-500">
                    Mostrando <span class="font-semibold text-slate-800">{{ $courses->firstItem() ?? 0 }} a {{ $courses->lastItem() ?? 0 }}</span> de <span class="font-semibold text-slate-800">{{ $courses->total() }}</span> cursos
                </span>
            </div>

            <!-- Paginação Livewire -->
            <div>
                {{ $courses->links() }}
            </div>
        </footer>
    </div>
</div>
