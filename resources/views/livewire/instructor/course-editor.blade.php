<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header Superior com Navegação & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('instructor.courses.index') }}" class="text-xs text-slate-400 hover:text-white transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Voltar para Meus Cursos</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    {{ $courseId ? $title : 'Criar Novo Treinamento' }}
                </h1>
                @if($courseId)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                        @if($status === 'published') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                        @elseif($status === 'draft') bg-amber-500/20 text-amber-300 border border-amber-500/30
                        @else bg-slate-800 text-slate-400 border border-slate-700 @endif">
                        {{ $status === 'published' ? 'Publicado' : ($status === 'draft' ? 'Rascunho' : 'Arquivado') }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        Novo Curso
                    </span>
                @endif
            </div>
        </div>

        @if($courseId)
            <div class="flex items-center gap-2">
                <a href="{{ route('instructor.courses.curriculum', $courseId) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-indigo-300 bg-indigo-500/10 border border-indigo-500/30 hover:bg-indigo-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Ir para Grade de Aulas &rarr;</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Barra de 4 Abas (Academy LMS Pattern) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-1.5 bg-slate-900/90 border border-slate-800 rounded-2xl text-xs font-semibold shadow-xl">
        <!-- Aba 1: Informações Básicas -->
        <button type="button" wire:click="setTab('basic')"
                class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl transition-all {{ $activeTab === 'basic' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span class="truncate">1. Informações Básicas</span>
        </button>

        <!-- Aba 2: Grade Curricular -->
        @if($courseId)
            <a href="{{ route('instructor.courses.curriculum', $courseId) }}"
               class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="truncate">2. Grade Curricular</span>
                @if($course && $course->lessons_count > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-500/20 text-indigo-300 font-mono">{{ $course->lessons_count }}</span>
                @endif
            </a>
        @else
            <div class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-slate-600 cursor-not-allowed" title="Preencha o básico e salve para liberar a grade">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="truncate">2. Grade Curricular</span>
            </div>
        @endif

        <!-- Aba 3: Avaliações & Quizzes -->
        @if($courseId)
            <a href="{{ route('instructor.courses.curriculum', $courseId) }}#quizzes-do-curso"
               class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="truncate">3. Quizzes & Avaliações</span>
                @if($course && $course->quizzes_count > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-500/20 text-indigo-300 font-mono">{{ $course->quizzes_count }}</span>
                @endif
            </a>
        @else
            <div class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-slate-600 cursor-not-allowed" title="Liberado após criar o curso">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="truncate">3. Quizzes & Avaliações</span>
            </div>
        @endif

        <!-- Aba 4: Mídia & Publicação -->
        @if($courseId)
            <button type="button" wire:click="setTab('publish')"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl transition-all {{ $activeTab === 'publish' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <span class="truncate">4. Mídia & Publicação</span>
            </button>
        @else
            <div class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-slate-600 cursor-not-allowed" title="Liberado após criar o curso">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="truncate">4. Mídia & Publicação</span>
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- CONTEÚDO DA ABA 1: Informações Básicas     -->
    <!-- ========================================== -->
    @if($activeTab === 'basic')
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    Etapa 1: Informações Fundamentais do Treinamento
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Defina o tema central, a autoria do instrutor e os objetivos de aprendizagem para os alunos.</p>
            </div>

            <form wire:submit="save" class="space-y-6">
                <!-- Título do Curso -->
                <div>
                    <label for="course_title" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Título do Treinamento Corporativo *
                    </label>
                    <input type="text" id="course_title" wire:model.live.debounce.300ms="title" 
                           placeholder="Ex: Arquitetura de Microsserviços e Observabilidade em Nuvem"
                           class="w-full bg-slate-950/70 border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('title')
                        <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instrutor Responsável -->
                <div>
                    <label for="course_instructor" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Professor / Instrutor Titular *
                    </label>
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

                <!-- Descrição e Objetivos Pedagógicos -->
                <div>
                    <label for="course_description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Descrição e Objetivos de Aprendizagem
                    </label>
                    <textarea id="course_description" wire:model="description" rows="5" 
                              placeholder="Explique o que os alunos vão dominar após concluir este curso, público-alvo recomendado e competências profissionais desenvolvidas..."
                              class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Este texto será exibido na página de apresentação do curso aos alunos.</p>
                </div>

                <!-- Botões de Ação da Aba 1 -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-800">
                    <a href="{{ route('instructor.courses.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800/80 transition-colors w-full sm:w-auto text-center">
                        Cancelar
                    </a>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        @if($courseId)
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                                <span wire:loading.remove>Salvar Informações Básicas</span>
                                <span wire:loading>Salvando...</span>
                            </button>

                            <a href="{{ route('instructor.courses.curriculum', $courseId) }}" 
                               class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                                <span>Avançar para Grade Curricular</span>
                                <span>&rarr;</span>
                            </a>
                        @else
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="px-6 py-3 rounded-xl font-bold text-xs sm:text-sm text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                                <span wire:loading.remove>Criar Curso e Montar Grade Curricular &rarr;</span>
                                <span wire:loading>Criando Curso...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- CONTEÚDO DA ABA 4: Mídia & Publicação      -->
    <!-- ========================================== -->
    @if($activeTab === 'publish' && $courseId)
        <div class="space-y-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Card 1: Identidade Visual / Capa -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-5">
                    <div class="border-b border-slate-800 pb-3">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            Identidade Visual & Capa do Treinamento
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Defina a imagem de apresentação que os alunos verão no catálogo e na vitrine.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div class="space-y-3">
                            <div>
                                <label for="course_thumbnail" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    URL da Imagem de Capa (Banner 16:9)
                                </label>
                                <input type="url" id="course_thumbnail" wire:model.live.debounce.300ms="thumbnail" 
                                       placeholder="https://images.unsplash.com/photo-... ou URL da imagem"
                                       class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-[11px] text-slate-500 mt-1">Recomendado: 1280x720px (formato widescreen 16:9 em JPG ou PNG).</p>
                            </div>

                            @if($thumbnail)
                                <button type="button" wire:click="$set('thumbnail', '')" 
                                        class="text-xs text-rose-400 hover:text-rose-300 transition-colors flex items-center gap-1">
                                    <span>Remover imagem</span>
                                </button>
                            @endif
                        </div>

                        <!-- Pré-visualização ao Vivo -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 mb-2">Pré-visualização do Card do Aluno:</span>
                            <div class="rounded-xl overflow-hidden border border-slate-800 bg-slate-950 shadow-lg">
                                <div class="aspect-video relative bg-gradient-to-tr from-slate-900 via-indigo-950/60 to-slate-900 flex items-center justify-center overflow-hidden">
                                    @if($thumbnail)
                                        <img src="{{ $thumbnail }}" alt="{{ $title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center p-4">
                                            <span class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">
                                                {{ substr($title ?: 'Curso', 0, 2) }}
                                            </span>
                                            <span class="block text-[10px] text-slate-500 uppercase tracking-widest mt-1 font-semibold">Sem Imagem</span>
                                        </div>
                                    @endif
                                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[9px] font-bold 
                                        @if($status === 'published') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                        @else bg-amber-500/20 text-amber-300 border border-amber-500/30 @endif">
                                        {{ $status === 'published' ? 'Publicado' : 'Rascunho' }}
                                    </span>
                                </div>
                                <div class="p-3.5 space-y-1">
                                    <h4 class="text-xs font-bold text-white line-clamp-1">{{ $title ?: 'Título do Curso' }}</h4>
                                    <p class="text-[11px] text-slate-400 line-clamp-1">{{ $description ?: 'Descrição do curso...' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: URL Amigável / SEO -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
                    <div class="border-b border-slate-800 pb-3">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            Link de Acesso dos Alunos (Slug)
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Endereço direto pelo qual os alunos acessarão as aulas e módulos.</p>
                    </div>

                    <div>
                        <label for="course_slug" class="block text-xs font-semibold text-slate-300 mb-1.5">Slug da URL *</label>
                        <div class="flex items-center rounded-xl bg-slate-950/70 border @error('slug') border-rose-500 @else border-slate-700 @enderror overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                            <span class="px-3.5 py-2.5 text-xs text-slate-500 bg-slate-900 border-r border-slate-800 font-mono select-none">
                                /aluno/cursos/
                            </span>
                            <input type="text" id="course_slug" wire:model="slug" placeholder="meu-curso-corporativo"
                                   class="w-full bg-transparent px-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none font-mono">
                        </div>
                        @error('slug')
                            <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Card 3: Checklist de Prontidão -->
                @if($course)
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
                        <div class="border-b border-slate-800 pb-3">
                            <h2 class="text-base font-bold text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Checklist de Prontidão para Lançamento
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Verifique se o treinamento está pronto para os alunos começarem a aprender.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Item 1: Metadados -->
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                    ✓
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-white block">Dados Básicos</span>
                                    <span class="text-[11px] text-slate-400">Título e descrição preenchidos</span>
                                </div>
                            </div>

                            <!-- Item 2: Grade Curricular -->
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $course->lessons_count > 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }} flex items-center justify-center font-bold">
                                    {{ $course->lessons_count > 0 ? '✓' : '!' }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-white block">Conteúdo das Aulas</span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ $course->modules_count }} módulos &bull; {{ $course->lessons_count }} aulas
                                    </span>
                                </div>
                            </div>

                            <!-- Item 3: Capa -->
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $thumbnail ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }} flex items-center justify-center font-bold">
                                    {{ $thumbnail ? '✓' : '!' }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-white block">Identidade Visual</span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ $thumbnail ? 'Capa configurada' : 'Sem banner (recomendado)' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card 4: Seletor de Status & Lançamento -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
                    <div class="border-b border-slate-800 pb-3">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Status de Publicação
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Alterne entre rascunho privado e publicação aberta na plataforma.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Rascunho -->
                        <label class="p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between gap-3 {{ $status === 'draft' ? 'bg-amber-500/10 border-amber-500/50 text-white shadow-md' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    Rascunho
                                </span>
                                <input type="radio" wire:model.live="status" value="draft" class="text-amber-500 focus:ring-amber-500">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Visível exclusivamente para você (professor/instrutor). Alunos não conseguem visualizar nem iniciar.
                            </p>
                        </label>

                        <!-- Publicado -->
                        <label class="p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between gap-3 {{ $status === 'published' ? 'bg-emerald-500/10 border-emerald-500/50 text-white shadow-md' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    Publicado
                                </span>
                                <input type="radio" wire:model.live="status" value="published" class="text-emerald-500 focus:ring-emerald-500">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Disponível imediatamente no catálogo. Alunos matriculados podem assistir às aulas e responder aos quizzes.
                            </p>
                        </label>

                        <!-- Arquivado -->
                        <label class="p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between gap-3 {{ $status === 'archived' ? 'bg-slate-800 border-slate-600 text-white shadow-md' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                    Arquivado
                                </span>
                                <input type="radio" wire:model.live="status" value="archived" class="text-slate-500 focus:ring-slate-500">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Treinamento desativado temporariamente. Oculto para novas matrículas na plataforma.
                            </p>
                        </label>
                    </div>
                </div>

                <!-- Botões de Ação da Aba 4 -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                        <span wire:loading.remove>Salvar Configurações de Publicação</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
