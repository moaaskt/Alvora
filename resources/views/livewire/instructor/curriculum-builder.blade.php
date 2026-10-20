<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('instructor.courses.index') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    &larr; Catálogo
                </a>
                <span class="text-slate-600">&bull;</span>
                <a href="{{ route('instructor.courses.edit', $course) }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    Configurações do Curso
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    {{ $course->title }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                    @if($course->isPublished()) bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @else bg-amber-500/20 text-amber-300 border border-amber-500/30 @endif">
                    {{ $course->isPublished() ? 'Publicado' : 'Rascunho' }}
                </span>
            </div>
        </div>

        <button wire:click="$toggle('showNewModuleModal')" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 shadow-md shadow-indigo-600/30 transition-all hover:scale-[1.02]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Adicionar Módulo</span>
        </button>
    </div>

    <!-- Gerador de Grade com IA (OpenRouter Fallback) -->
    <div class="mb-6">
        @livewire('instructor.curriculum-ai-generator', ['courseId' => $course->id])
    </div>

    <!-- Modal Novo Módulo -->
    @if($showNewModuleModal)
        <div class="p-6 rounded-2xl bg-slate-900 border border-indigo-500/40 shadow-2xl relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white">Criar Novo Módulo Temático</h3>
                <button wire:click="$set('showNewModuleModal', false)" type="button" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="addModule" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Título do Módulo *</label>
                    <input type="text" wire:model="newModuleTitle" placeholder="Ex: Módulo 1: Fundamentos e Boas Práticas"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('newModuleTitle') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Descrição Breve (Opcional)</label>
                    <textarea wire:model="newModuleDescription" rows="2" placeholder="O que o aluno aprenderá neste módulo..."
                              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showNewModuleModal', false)" type="button" class="px-3.5 py-2 rounded-xl text-xs bg-slate-800 text-slate-300 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">Salvar Módulo</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Modal Nova Aula -->
    @if($showNewLessonModal)
        <div class="p-6 rounded-2xl bg-slate-900 border border-indigo-500/40 shadow-2xl relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white">Adicionar Aula ao Módulo</h3>
                <button wire:click="$set('showNewLessonModal', false)" type="button" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="addLesson" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Título da Aula *</label>
                    <input type="text" wire:model="newLessonTitle" placeholder="Ex: Aula 1.1: Introdução ao Conceito"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('newLessonTitle') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">URL do Vídeo (YouTube, Vimeo, MP4)</label>
                        <input type="url" wire:model="newLessonVideoUrl" placeholder="https://..."
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('newLessonVideoUrl') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Duração Estimada (minutos) *</label>
                        <input type="number" wire:model="newLessonDuration" min="1" max="600"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Descrição / Roteiro da Aula</label>
                    <textarea wire:model="newLessonDescription" rows="2" placeholder="Resumo do conteúdo abordado na gravação..."
                              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="isPreview" wire:model="newLessonIsPreview" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500">
                    <label for="isPreview" class="text-xs text-slate-300 cursor-pointer">Aula aberta para prévia gratuita (Preview)</label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showNewLessonModal', false)" type="button" class="px-3.5 py-2 rounded-xl text-xs bg-slate-800 text-slate-300 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">Salvar Aula</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Modal Anexo -->
    @if($showAttachmentModal)
        <div class="p-6 rounded-2xl bg-slate-900 border border-indigo-500/40 shadow-2xl relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white">Anexar Material Complementar</h3>
                <button wire:click="$set('showAttachmentModal', false)" type="button" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="addAttachment" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Título do Material *</label>
                    <input type="text" wire:model="attachmentTitle" placeholder="Ex: Slide em PDF da Apresentação"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('attachmentTitle') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Link para Download / Documento *</label>
                    <input type="url" wire:model="attachmentUrl" placeholder="https://..."
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('attachmentUrl') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showAttachmentModal', false)" type="button" class="px-3.5 py-2 rounded-xl text-xs bg-slate-800 text-slate-300 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">Anexar Arquivo</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Módulos & Aulas Container -->
    @if($course->modules->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">Nenhum módulo cadastrado neste curso</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">
                Crie o primeiro módulo temático para começar a estruturar as aulas e conteúdos.
            </p>
            <button wire:click="$set('showNewModuleModal', true)" type="button"
                    class="inline-flex items-center text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                Criar o primeiro módulo &rarr;
            </button>
        </div>
    @else
        <div class="space-y-6">
            @foreach($course->modules as $module)
                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
                    <!-- Module Bar -->
                    <div class="p-4 sm:p-5 bg-slate-950/50 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 flex items-center justify-center font-bold text-xs">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-white">{{ $module->title }}</h3>
                                @if($module->description)
                                    <p class="text-[11px] text-slate-400">{{ $module->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <!-- Move Up / Down -->
                            <button wire:click="moveModule({{ $module->id }}, 'up')" type="button" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300" title="Subir módulo">
                                ▲
                            </button>
                            <button wire:click="moveModule({{ $module->id }}, 'down')" type="button" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300" title="Descer módulo">
                                ▼
                            </button>

                            <!-- Add Lesson Button -->
                            <button wire:click="openNewLessonModal({{ $module->id }})" type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Adicionar Aula</span>
                            </button>

                            <!-- Delete Module -->
                            <button wire:click="deleteModule({{ $module->id }})" 
                                    wire:confirm="Excluir o módulo '{{ $module->title }}' e todas as suas aulas?"
                                    type="button" 
                                    class="p-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lessons List -->
                    <div class="p-4 space-y-3">
                        @if($module->lessons->isEmpty())
                            <p class="text-xs text-slate-500 py-2 italic text-center">Nenhuma aula cadastrada neste módulo.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($module->lessons as $lesson)
                                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-slate-500 font-mono">{{ $loop->iteration }}.</span>
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-semibold text-white">{{ $lesson->title }}</span>
                                                    @if($lesson->is_free_preview)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Preview</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 text-[11px] text-slate-400">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ $lesson->formattedDuration() }}
                                                    </span>
                                                    @if($lesson->video_url)
                                                        <span class="text-slate-500 truncate max-w-[200px]">URL: {{ $lesson->video_url }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attachments & Actions -->
                                        <div class="flex items-center gap-2">
                                            <!-- List Existing Attachments -->
                                            @foreach($lesson->attachments as $att)
                                                <a href="{{ $att->file_path }}" target="_blank" class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-indigo-300 flex items-center gap-1" title="Abrir anexo">
                                                    📎 {{ Str::limit($att->title, 12) }}
                                                </a>
                                                <button wire:click="deleteAttachment({{ $att->id }})" class="text-rose-400 hover:text-rose-300 text-[10px]" title="Excluir anexo">✕</button>
                                            @endforeach

                                            <button wire:click="openAttachmentModal({{ $lesson->id }})" type="button"
                                                    class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs" title="Anexar material">
                                                📎
                                            </button>

                                            <!-- Reorder lesson -->
                                            <button wire:click="moveLesson({{ $lesson->id }}, 'up')" type="button" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px]" title="Subir aula">
                                                ▲
                                            </button>
                                            <button wire:click="moveLesson({{ $lesson->id }}, 'down')" type="button" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px]" title="Descer aula">
                                                ▼
                                            </button>

                                            <!-- Delete lesson -->
                                            <button wire:click="deleteLesson({{ $lesson->id }})" 
                                                    wire:confirm="Excluir a aula '{{ $lesson->title }}'?"
                                                    type="button" class="p-1 text-rose-400 hover:text-rose-300 text-xs">
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
</div>
