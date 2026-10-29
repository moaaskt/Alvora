<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- SUB-NAV / BREADCRUMBS & STATUS TOP BAR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.index') : route('instructor.courses.index')) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Voltar aos Cursos</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Home</span>
            <span class="text-slate-300">/</span>
            <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}" class="text-xs text-slate-500 hover:text-blue-600 font-medium transition-colors">
                {{ Str::limit($course->title, 25) }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">Grade Curricular</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-slate-100 text-slate-600 border border-slate-200/80">
                ID: #{{ $course->id }}
            </span>
            @if($course->isPublished())
                <span class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
                    Publicado
                </span>
            @else
                <span class="inline-flex items-center rounded-md bg-amber-500 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
                    Rascunho
                </span>
            @endif
        </div>
    </div>

    <!-- HEADER DA PÁGINA COM AÇÕES -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div class="flex flex-col gap-1 max-w-3xl">
            <h1 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight">
                Grade Curricular: {{ $course->title }}
            </h1>
            <p class="text-xs text-slate-500">
                Estruture os módulos temáticos, aulas gravadas e materiais complementares do programa corporativo.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('instructor.courses.quizzes.create', $course) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-slate-700 hover:bg-slate-50 transition-colors text-xs font-semibold border border-slate-200 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"></path>
                </svg>
                <span>Nova Avaliação</span>
            </a>

            <button type="button" 
                    wire:click="$toggle('showNewModuleModal')" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Adicionar Módulo</span>
            </button>
        </div>
    </header>

    <!-- NAVEGAÇÃO DE ABAS NO PADRÃO STITCH -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto select-none">
        <!-- Aba 1: Informações Básicas -->
        <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}?tab=basic" 
           class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition-colors shrink-0">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">1</span>
            <span>Informações Básicas</span>
        </a>

        <!-- Aba 2: Grade Curricular (ATIVA) -->
        <div class="flex items-center gap-2 px-4 py-3 text-sm font-semibold text-blue-600 border-b-2 border-blue-600 shrink-0">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold">2</span>
            <span>Grade Curricular</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200/60 font-mono">
                {{ $course->modules->count() }} Módulos / {{ $course->lessons->count() }} Aulas
            </span>
        </div>

        <!-- Aba 3: Avaliações & Quizzes -->
        <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}?tab=quizzes" 
           class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition-colors shrink-0">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">3</span>
            <span>Avaliações & Quizzes</span>
            @if($course->quizzes->count() > 0)
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60 font-mono">
                    {{ $course->quizzes->count() }} Quizzes
                </span>
            @endif
        </a>

        <!-- Aba 4: Mídia & Publicação -->
        <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}?tab=publish" 
           class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition-colors shrink-0">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">4</span>
            <span>Mídia & Publicação</span>
            @if($course->isPublished())
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-600 text-white shadow-sm">
                    Publicado
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500 text-white shadow-sm">
                    Rascunho
                </span>
            @endif
        </a>
    </div>

    <!-- GERADOR DE GRADE COM IA (OPENROUTER CASCATA) -->
    <div>
        @livewire('instructor.curriculum-ai-generator', ['courseId' => $course->id])
    </div>

    <!-- MODAL: NOVO MÓDULO (ESTILO STITCH) -->
    @if($showNewModuleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Criar Novo Módulo Temático
                    </h3>
                    <button wire:click="$set('showNewModuleModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-base">✕</button>
                </div>

                <form wire:submit="addModule" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Módulo *</label>
                        <input type="text" wire:model="newModuleTitle" placeholder="Ex: Módulo 1: Fundamentos e Arquitetura"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border @error('newModuleTitle') border-rose-500 @else border-slate-200 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        @error('newModuleTitle') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição Breve (Opcional)</label>
                        <textarea wire:model="newModuleDescription" rows="2" placeholder="O que o aluno aprenderá neste módulo temático..."
                                  class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button wire:click="$set('showNewModuleModal', false)" type="button" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm">
                            Salvar Módulo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: NOVA AULA (ESTILO STITCH) -->
    @if($showNewLessonModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Adicionar Aula ao Módulo
                    </h3>
                    <button wire:click="$set('showNewLessonModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-base">✕</button>
                </div>

                <form wire:submit="addLesson" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título da Aula *</label>
                        <input type="text" wire:model="newLessonTitle" placeholder="Ex: Aula 1.1: Introdução aos Padrões SOLID"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border @error('newLessonTitle') border-rose-500 @else border-slate-200 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        @error('newLessonTitle') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">URL do Vídeo</label>
                            <input type="url" wire:model="newLessonVideoUrl" placeholder="https://..."
                                   class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                            @error('newLessonVideoUrl') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Duração (minutos) *</label>
                            <input type="number" wire:model="newLessonDuration" min="1" max="600"
                                   class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição / Roteiro da Aula</label>
                        <textarea wire:model="newLessonDescription" rows="2" placeholder="Resumo dos tópicos ensinados nesta lição..."
                                  class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isPreview" wire:model="newLessonIsPreview" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="isPreview" class="text-xs text-slate-600 cursor-pointer">Aula liberada para prévia gratuita (Preview)</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button wire:click="$set('showNewLessonModal', false)" type="button" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm">
                            Salvar Aula
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: ANEXO DE MATERIAL COMPLEMENTAR -->
    @if($showAttachmentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Anexar Material Complementar
                    </h3>
                    <button wire:click="$set('showAttachmentModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-base">✕</button>
                </div>

                <form wire:submit="addAttachment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Material *</label>
                        <input type="text" wire:model="attachmentTitle" placeholder="Ex: Slides em PDF da Apresentação"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border @error('attachmentTitle') border-rose-500 @else border-slate-200 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        @error('attachmentTitle') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Link para Download / Documento *</label>
                        <input type="url" wire:model="attachmentUrl" placeholder="https://..."
                               class="w-full text-xs px-3.5 py-2 rounded-xl bg-white border @error('attachmentUrl') border-rose-500 @else border-slate-200 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        @error('attachmentUrl') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button wire:click="$set('showAttachmentModal', false)" type="button" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm">
                            Anexar Arquivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- LISTAGEM DE MÓDULOS E AULAS NO PADRÃO STITCH -->
    @if($course->modules->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <div class="w-14 h-14 mx-auto rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-7 h-7 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-900">Nenhum módulo cadastrado neste treinamento</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Crie o primeiro módulo temático manualmente ou utilize o Gerador Curricular Inteligente acima.
                </p>
            </div>
            <button wire:click="$set('showNewModuleModal', true)" type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm">
                + Criar Primeiro Módulo
            </button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($course->modules as $module)
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <!-- Barra de Título do Módulo -->
                    <div class="p-4 sm:p-5 bg-slate-50/70 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $module->title }}</h3>
                                @if($module->description)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $module->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 self-end sm:self-auto">
                            <!-- Reordenar Módulo -->
                            <button wire:click="moveModule({{ $module->id }}, 'up')" type="button" 
                                    class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs shadow-2xs" title="Mover para cima">
                                ▲
                            </button>
                            <button wire:click="moveModule({{ $module->id }}, 'down')" type="button" 
                                    class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs shadow-2xs" title="Mover para baixo">
                                ▼
                            </button>

                            <!-- Adicionar Aula -->
                            <button wire:click="openNewLessonModal({{ $module->id }})" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors shadow-2xs">
                                <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                                </svg>
                                <span>Adicionar Aula</span>
                            </button>

                            <!-- Excluir Módulo -->
                            <button wire:click="deleteModule({{ $module->id }})" 
                                    wire:confirm="Tem certeza de que deseja excluir o módulo '{{ $module->title }}' e todas as suas lições?"
                                    type="button" 
                                    class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-rose-50 text-rose-500 text-xs shadow-2xs" title="Remover Módulo">
                                <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lista de Aulas do Módulo -->
                    <div class="p-4 space-y-2">
                        @if($module->lessons->isEmpty())
                            <p class="text-xs text-slate-400 py-3 italic text-center">Nenhuma aula cadastrada neste módulo ainda.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($module->lessons as $lesson)
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:bg-slate-100/50 transition-colors flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-slate-400 font-mono font-medium">{{ $loop->iteration }}.</span>
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-semibold text-slate-800">{{ $lesson->title }}</span>
                                                    @if($lesson->is_free_preview)
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200/60">Preview</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 text-[11px] text-slate-500">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        {{ $lesson->formattedDuration() }}
                                                    </span>
                                                    @if($lesson->video_url)
                                                        <span class="text-slate-400 truncate max-w-[220px]">Vídeo: {{ $lesson->video_url }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Anexos & Ações da Aula -->
                                        <div class="flex items-center gap-1.5">
                                            @foreach($lesson->attachments as $att)
                                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-white border border-slate-200 text-[11px] text-blue-600 shadow-2xs">
                                                    <a href="{{ $att->file_path }}" target="_blank" class="hover:underline flex items-center gap-1" title="Visualizar anexo">
                                                        📎 {{ Str::limit($att->title, 14) }}
                                                    </a>
                                                    <button wire:click="deleteAttachment({{ $att->id }})" class="text-slate-400 hover:text-rose-500 font-bold ml-1" title="Excluir anexo">✕</button>
                                                </div>
                                            @endforeach

                                            <button wire:click="openAttachmentModal({{ $lesson->id }})" type="button"
                                                    class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs shadow-2xs" title="Anexar material complementar">
                                                📎
                                            </button>

                                            <!-- Reordenar Aula -->
                                            <button wire:click="moveLesson({{ $lesson->id }}, 'up')" type="button" 
                                                    class="p-1 rounded-md bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-[10px] shadow-2xs" title="Subir aula">
                                                ▲
                                            </button>
                                            <button wire:click="moveLesson({{ $lesson->id }}, 'down')" type="button" 
                                                    class="p-1 rounded-md bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-[10px] shadow-2xs" title="Descer aula">
                                                ▼
                                            </button>

                                            <!-- Excluir Aula -->
                                            <button wire:click="deleteLesson({{ $lesson->id }})" 
                                                    wire:confirm="Excluir a aula '{{ $lesson->title }}'?"
                                                    type="button" 
                                                    class="p-1 text-slate-400 hover:text-rose-500 text-xs">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- NAVEGAÇÃO DE RODAPÉ -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-6 border-t border-slate-200/80">
        <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}?tab=basic" 
           class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 shadow-2xs transition-colors flex items-center gap-1.5 w-full sm:w-auto justify-center">
            <span>&larr;</span>
            <span>Voltar para Informações Básicas</span>
        </a>

        <a href="{{ (auth()->user()?->isAlvoraAdmin() ? route('admin.courses.edit', $course) : route('instructor.courses.edit', $course)) }}?tab=quizzes" 
           class="px-5 py-2 rounded-xl font-semibold text-xs text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <span>Avançar para Quizzes & Avaliações</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
