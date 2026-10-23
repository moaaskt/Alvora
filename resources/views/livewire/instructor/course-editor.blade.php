<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="space-y-4 pb-6 border-b border-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('instructor.courses.index') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                        &larr; Voltar para catálogo
                    </a>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    {{ $courseId ? 'Editar Curso' : 'Criar Novo Curso Corporativo' }}
                </h1>
            </div>

            @if($courseId)
                <a href="{{ route('instructor.courses.curriculum', $courseId) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold text-indigo-300 bg-indigo-500/10 border border-indigo-500/30 hover:bg-indigo-500/20 transition-colors">
                    Gerenciar Grade de Aulas &rarr;
                </a>
            @endif
        </div>

        @if($courseId)
            <!-- Course Contextual Navigation Tabs (Opção A) -->
            <div class="flex items-center gap-2 border-t border-slate-800/80 pt-3 text-xs">
                <span class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Configurações do Curso
                </span>
                <a href="{{ route('instructor.courses.curriculum', $courseId) }}" class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Grade Curricular & Aulas
                </a>
            </div>
        @endif
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
        <form wire:submit="save" class="space-y-6">
            <!-- Título do Curso -->
            <div>
                <label for="course_title" class="block text-xs font-semibold text-slate-300 mb-1.5">Título do Treinamento *</label>
                <input type="text" id="course_title" wire:model.live.debounce.300ms="title" placeholder="Ex: Segurança da Informação & Compliance LGPD"
                       class="w-full bg-slate-950/70 border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('title')
                    <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="course_slug" class="block text-xs font-semibold text-slate-300 mb-1.5">Slug da URL *</label>
                    <input type="text" id="course_slug" wire:model="slug" placeholder="seguranca-da-informacao-lgpd"
                           class="w-full bg-slate-950/70 border @error('slug') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                    @error('slug')
                        <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="course_status" class="block text-xs font-semibold text-slate-300 mb-1.5">Status de Publicação</label>
                    <select id="course_status" wire:model="status"
                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="draft">Rascunho (Visível apenas para instrutores/RH)</option>
                        <option value="published">Publicado (Disponível no catálogo dos alunos)</option>
                        <option value="archived">Arquivado</option>
                    </select>
                </div>
            </div>

            <!-- Instrutor Responsável -->
            <div>
                <label for="course_instructor" class="block text-xs font-semibold text-slate-300 mb-1.5">Instrutor Responsável *</label>
                <select id="course_instructor" wire:model="instructor_id"
                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($instructors as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->email }})</option>
                    @endforeach
                </select>
                @error('instructor_id')
                    <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descrição Completa -->
            <div>
                <label for="course_description" class="block text-xs font-semibold text-slate-300 mb-1.5">Descrição e Objetivos de Aprendizagem</label>
                <textarea id="course_description" wire:model="description" rows="4" 
                          placeholder="Descreva o escopo do curso, público-alvo e competências desenvolvidas..."
                          class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Thumbnail URL -->
            <div>
                <label for="course_thumbnail" class="block text-xs font-semibold text-slate-300 mb-1.5">URL da Imagem de Capa (Opcional)</label>
                <input type="url" id="course_thumbnail" wire:model="thumbnail" placeholder="https://exemplo.com/capa-curso.jpg"
                       class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @if($thumbnail)
                    <div class="mt-3 w-40 h-24 rounded-lg overflow-hidden border border-slate-700">
                        <img src="{{ $thumbnail }}" alt="Prévia" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('instructor.courses.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                    <span wire:loading.remove>Salvar Informações do Curso</span>
                    <span wire:loading>Salvando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
