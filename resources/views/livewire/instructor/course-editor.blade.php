<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- SUB-NAV / BREADCRUMBS & STATUS TOP BAR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('instructor.courses.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                <span>Voltar aos Cursos</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Home</span>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Cursos</span>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">{{ $courseId ? 'Atualizar Curso' : 'Novo Curso' }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($courseId)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-slate-100 text-slate-600 border border-slate-200/80">
                    ID: #{{ $courseId }}
                </span>
                @if($status === 'published')
                    <span class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700">
                        Publicado
                    </span>
                @elseif($status === 'draft')
                    <span class="inline-flex items-center rounded-md bg-amber-500 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600">
                        Rascunho
                    </span>
                @else
                    <span class="inline-flex items-center rounded-md bg-slate-600 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
                        Arquivado
                    </span>
                @endif
            @else
                <span class="inline-flex items-center rounded-md bg-blue-600 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
                    Novo Cadastro
                </span>
            @endif
        </div>
    </div>

    <!-- FORM HEADER COM AÇÕES NO CANTO DIREITO -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div class="flex flex-col gap-1 max-w-3xl">
            <h1 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight">
                {{ $courseId ? 'Atualizar: ' . ($title ?: 'Curso') : 'Criar Novo Treinamento Corporativo' }}
            </h1>
            <p class="text-xs text-slate-500">
                {{ $courseId ? 'Atualize as informações gerais, grade curricular e avaliações deste programa corporativo.' : 'Preencha os dados essenciais para cadastrar um novo programa de capacitação no catálogo da empresa.' }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('instructor.courses.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors text-xs font-semibold">
                Descartar Alterações
            </a>

            @if($courseId)
                <a href="{{ route('instructor.courses.curriculum', $courseId) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-slate-700 hover:bg-slate-50 transition-colors text-xs font-semibold border border-slate-200 shadow-2xs">
                    <x-icon name="collection-play" class="w-4 h-4 text-slate-500 shrink-0" />
                    <span>Grade Curricular</span>
                </a>
            @endif

            <button type="button" 
                    wire:click="save" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                    <x-icon name="check-lg" class="w-4 h-4 shrink-0" />
                    <span>{{ $courseId ? 'Salvar Alterações' : 'Criar Curso' }}</span>
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                    <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Salvando...</span>
                </span>
            </button>
        </div>
    </header>

    <!-- NAVEGAÇÃO DE ABAS NO PADRÃO STITCH -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto select-none">
        <!-- Aba 1: Informações Básicas -->
        <button type="button" 
                wire:click="setTab('basic')" 
                class="flex items-center gap-2 px-4 py-3 text-sm shrink-0 transition-colors {{ $activeTab === 'basic' ? 'font-semibold text-blue-600 border-b-2 border-blue-600' : 'font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent' }}">
            <span class="flex items-center justify-center w-5 h-5 rounded-full {{ $activeTab === 'basic' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500' }} text-xs font-bold">1</span>
            <span>Informações Básicas</span>
        </button>

        <!-- Aba 2: Grade Curricular -->
        @if($courseId)
            <a href="{{ route('instructor.courses.curriculum', $courseId) }}" 
               class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition-colors shrink-0">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">2</span>
                <span>Grade Curricular</span>
                @if($course)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60">
                        {{ $course->modules_count }} Módulos / {{ $course->lessons_count }} Aulas
                    </span>
                @endif
            </a>
        @else
            <div class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-400 border-b-2 border-transparent shrink-0 opacity-60 cursor-not-allowed" title="Disponível após criar o curso">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-400 text-xs font-bold">2</span>
                <span>Grade Curricular</span>
            </div>
        @endif

        <!-- Aba 3: Avaliações & Quizzes -->
        @if($courseId)
            <button type="button" 
                    wire:click="setTab('quizzes')" 
                    class="flex items-center gap-2 px-4 py-3 text-sm shrink-0 transition-colors {{ $activeTab === 'quizzes' ? 'font-semibold text-blue-600 border-b-2 border-blue-600' : 'font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent' }}">
                <span class="flex items-center justify-center w-5 h-5 rounded-full {{ $activeTab === 'quizzes' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500' }} text-xs font-bold">3</span>
                <span>Avaliações & Quizzes</span>
                @if($course && $course->quizzes_count > 0)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60">
                        {{ $course->quizzes_count }} Quizzes
                    </span>
                @endif
            </button>
        @else
            <div class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-400 border-b-2 border-transparent shrink-0 opacity-60 cursor-not-allowed" title="Disponível após criar o curso">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-400 text-xs font-bold">3</span>
                <span>Avaliações & Quizzes</span>
            </div>
        @endif

        <!-- Aba 4: Mídia & Publicação -->
        @if($courseId)
            <button type="button" 
                    wire:click="setTab('publish')" 
                    class="flex items-center gap-2 px-4 py-3 text-sm shrink-0 transition-colors {{ $activeTab === 'publish' ? 'font-semibold text-blue-600 border-b-2 border-blue-600' : 'font-medium text-slate-500 hover:text-slate-800 border-b-2 border-transparent' }}">
                <span class="flex items-center justify-center w-5 h-5 rounded-full {{ $activeTab === 'publish' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500' }} text-xs font-bold">4</span>
                <span>Mídia & Publicação</span>
                @if($status === 'published')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-600 text-white shadow-sm">
                        Publicado
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500 text-white shadow-sm">
                        Rascunho
                    </span>
                @endif
            </button>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- ABA 1: INFORMAÇÕES BÁSICAS (LAYOUT 2 COLUNAS DO STITCH) -->
    <!-- ======================================================== -->
    @if($activeTab === 'basic')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- COLUNA ESQUERDA: DADOS GERAIS (8 COLUNAS) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- CARD 1: FORMULÁRIO GERAL -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                <x-icon name="file-earmark-text" class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800">Dados Gerais do Treinamento</h2>
                                <p class="text-xs text-slate-400">Informações fundamentais visíveis no catálogo corporativo</p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400">Campos obrigatórios com *</span>
                    </div>

                    <!-- Título do Curso -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-700" for="course-title">
                            Título do Curso *
                        </label>
                        <input id="course-title" 
                               wire:model.live.debounce.300ms="title" 
                               type="text" 
                               placeholder="Ex: Desenvolvimento de temas WordPress com Bootstrap"
                               class="w-full h-10 px-3.5 rounded-xl bg-white border @error('title') border-rose-500 @else border-slate-200 @enderror text-slate-800 text-sm font-medium focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs transition-all">
                        @error('title')
                            <span class="text-rose-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Grid: Categoria Principal & Instrutor Responsável -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700" for="course-category">
                                Categoria do Treinamento
                            </label>
                            <div class="relative">
                                <select id="course-category" 
                                        wire:model="category_id"
                                        class="w-full h-10 pl-3.5 pr-8 rounded-xl bg-white border @error('category_id') border-rose-500 @else border-slate-200 @enderror text-slate-800 text-xs font-medium focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer shadow-2xs">
                                    <option value="">Selecione uma categoria...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" class="font-bold">{{ $cat->name }} (Principal)</option>
                                        @foreach($cat->subcategories as $sub)
                                            <option value="{{ $sub->id }}">&nbsp;&nbsp;↳ {{ $sub->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <x-icon name="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            </div>
                            @error('category_id')
                                <span class="text-rose-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700" for="course-instructor">
                                Instrutor Responsável *
                            </label>
                            <div class="relative">
                                <select id="course-instructor" 
                                        wire:model="instructor_id"
                                        class="w-full h-10 pl-3.5 pr-8 rounded-xl bg-white border @error('instructor_id') border-rose-500 @else border-slate-200 @enderror text-slate-800 text-xs font-medium focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer shadow-2xs">
                                    <option value="">Selecione o instrutor titular...</option>
                                    @foreach($instructors as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->email }})</option>
                                    @endforeach
                                </select>
                                <x-icon name="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            </div>
                            @error('instructor_id')
                                <span class="text-rose-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Descrição Completa com Estilo Stitch -->
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700" for="course-description">
                                Descrição Completa e Objetivos Pedagógicos
                            </label>
                            <span class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-medium">
                                <x-icon name="stars" class="w-3.5 h-3.5 text-blue-600" />
                                <span>Assistente Alvora IA Habilitado</span>
                            </span>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs bg-white">
                            <div class="flex items-center gap-1 px-3 py-2 bg-slate-50 border-b border-slate-200 text-slate-600 flex-wrap">
                                <span class="p-1 rounded font-bold text-xs select-none">B</span>
                                <span class="p-1 rounded italic text-xs select-none">I</span>
                                <span class="p-1 rounded underline text-xs select-none">U</span>
                                <span class="w-px h-4 bg-slate-200 mx-1"></span>
                                <span class="p-1 rounded text-xs select-none flex items-center">
                                    <x-icon name="list-ul" class="w-3.5 h-3.5" />
                                </span>
                                <span class="p-1 rounded text-xs select-none flex items-center">
                                    <x-icon name="link-45deg" class="w-3.5 h-3.5" />
                                </span>
                                <span class="w-px h-4 bg-slate-200 mx-1"></span>
                                <span class="text-[11px] text-slate-400 ml-auto font-mono">Texto & Markdown Suportado</span>
                            </div>
                            <textarea id="course-description" 
                                      wire:model="description" 
                                      rows="5" 
                                      placeholder="Descreva a ementa do treinamento, competências desenvolvidas e metas de aprendizagem para os colaboradores..."
                                      class="w-full p-4 text-xs font-normal text-slate-800 leading-relaxed focus:outline-none resize-y min-h-[140px]"></textarea>
                        </div>
                    </div>

                    <!-- URL da Capa / Thumbnail (Campo Real do Eloquent) -->
                    <div class="flex flex-col gap-1.5 pt-2 border-t border-slate-100">
                        <label class="text-xs font-semibold text-slate-700" for="course-thumbnail">
                            URL da Imagem de Capa (Banner 16:9)
                        </label>
                        <input id="course-thumbnail" 
                               wire:model.live.debounce.300ms="thumbnail" 
                               type="url" 
                               placeholder="https://exemplo.com/imagem-do-curso.jpg"
                               class="w-full h-10 px-3.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs transition-all">
                        <span class="text-[11px] text-slate-400">Recomendado formato horizontal widescreen (ex: 1280x720).</span>
                    </div>
                </div>

                <!-- CARD 2: OBJETIVOS & COMPETÊNCIAS PEDAGÓGICAS (ESTILO STITCH) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                <x-icon name="check2-circle" class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800">Objetivos de Aprendizagem & Competências</h2>
                                <p class="text-xs text-slate-400">Critérios validados e monitorados pelo Score de Retenção de IA</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">1</div>
                                <span class="text-xs font-medium text-slate-700">Domínio técnico e aplicação prática nas demandas corporativas da empresa</span>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold">Monitorado</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">2</div>
                                <span class="text-xs font-medium text-slate-700">Participação ativa nas aulas e fixação contínua do conteúdo através de avaliações</span>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold">Monitorado</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">3</div>
                                <span class="text-xs font-medium text-slate-700">Geração e emissão automática de certificado após cumprimento integral da ementa</span>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold">Automático</span>
                        </div>
                    </div>
                </div>

                <!-- ALERTAS DE VALIDAÇÃO & BARRA DE AÇÃO INFERIOR -->
                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex flex-col gap-1 shadow-xs">
                        <span class="font-bold flex items-center gap-1.5 text-rose-700">
                            <x-icon name="exclamation-triangle" class="w-4 h-4 shrink-0" />
                            Não foi possível salvar o treinamento. Verifique as pendências:
                        </span>
                        <ul class="list-disc list-inside space-y-0.5 mt-1 text-rose-700 font-medium">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <span class="text-xs text-slate-500">
                        {{ $courseId ? 'Todas as alterações são salvas e auditadas no sistema.' : 'Após criar, você será direcionado para adicionar módulos e aulas.' }}
                    </span>
                    <button type="button" 
                            wire:click="save" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer disabled:opacity-50 shrink-0">
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                            <x-icon name="check-lg" class="w-4 h-4 shrink-0" />
                            <span>{{ $courseId ? 'Salvar Alterações' : 'Criar Curso' }}</span>
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Salvando...</span>
                        </span>
                    </button>
                </div>
            </div>

            <!-- COLUNA DIREITA: RESUMO, SAÚDE & PARÂMETROS (4 COLUNAS) -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <!-- CARD RESUMO & SAÚDE DO CURSO -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-4">
                    <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2.5">
                        Resumo Rápido & Saúde do Curso
                    </h2>

                    <!-- Thumbnail Preview (Compact h-36) -->
                    <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-2xs group h-36 bg-slate-100 flex items-center justify-center">
                        @if($thumbnail)
                            <img alt="Capa do Curso" class="w-full h-full object-cover" src="{{ $thumbnail }}">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="#course-thumbnail" class="px-3 py-1.5 rounded-lg bg-white/90 text-slate-800 text-xs font-semibold shadow hover:bg-white transition-colors">
                                    Alterar Capa
                                </a>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 gap-1">
                                <x-icon name="image" class="w-8 h-8 text-slate-400" />
                                <span class="text-xs font-semibold">Sem Imagem de Capa</span>
                            </div>
                        @endif
                    </div>

                    <!-- 4 Métricas em Grid 2x2 -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-400 font-medium block">Matrículas Ativas</span>
                            <span class="text-base font-bold text-slate-800">{{ $course?->enrollments_count ?? 0 }} {{ ($course?->enrollments_count ?? 0) === 1 ? 'Aluno' : 'Alunos' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-400 font-medium block">Total de Aulas</span>
                            <span class="text-base font-bold text-slate-800">{{ $course?->lessons_count ?? 0 }} Lições</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-400 font-medium block">Módulos</span>
                            <span class="text-base font-bold text-slate-800">{{ $course?->modules_count ?? 0 }} Seções</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-400 font-medium block">Quizzes Ativos</span>
                            <span class="text-base font-bold text-slate-800">{{ $course?->quizzes_count ?? 0 }} Provas</span>
                        </div>
                    </div>

                    <!-- Box IA Copilot Insights do Stitch -->
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/80 flex flex-col gap-2">
                        <div class="flex items-center gap-1.5 text-blue-700 text-xs font-semibold">
                            <x-icon name="stars" class="w-4 h-4 text-blue-600 shrink-0" />
                            <span>Alvora Copilot Insights</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            O cálculo preditivo do Score de Evasão analisa a frequência com que os colaboradores acessam as lições deste curso para acionar suporte preventivo.
                        </p>
                    </div>
                </div>

                <!-- CARD PARÂMETROS DE ACESSO & PUBLICAÇÃO -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-4">
                    <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2.5">
                        Parâmetros de Acesso & Publicação
                    </h2>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Modalidade</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 font-semibold text-slate-700 border border-slate-200/60">
                                Corporativo Ilimitado
                            </span>
                        </div>

                        <!-- Seletor de Status -->
                        <div class="flex flex-col gap-1.5 pt-2 border-t border-slate-100">
                            <label class="text-xs font-semibold text-slate-700">Status de Disponibilidade</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="p-2.5 rounded-xl border cursor-pointer transition-all flex items-center justify-between {{ $status === 'published' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                                    <span class="text-xs">Publicado</span>
                                    <input type="radio" wire:model.live="status" value="published" class="text-emerald-600 focus:ring-emerald-500">
                                </label>

                                <label class="p-2.5 rounded-xl border cursor-pointer transition-all flex items-center justify-between {{ $status === 'draft' ? 'bg-amber-50 border-amber-300 text-amber-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                                    <span class="text-xs">Rascunho</span>
                                    <input type="radio" wire:model.live="status" value="draft" class="text-amber-600 focus:ring-amber-500">
                                </label>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-slate-700">Certificado Automático</span>
                                <span class="text-[11px] text-slate-400">Emitir ao atingir 100% das aulas</span>
                            </div>
                            <div class="w-9 h-5 rounded-full bg-blue-600 flex items-center justify-end px-0.5">
                                <div class="w-4 h-4 rounded-full bg-white shadow-2xs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- ABA 3: AVALIAÇÕES & QUIZZES (SE O CURSO ESTIVER SALVO)  -->
    <!-- ======================================================== -->
    @if($activeTab === 'quizzes' && $courseId)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Avaliações e Quizzes de Retenção
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Testes de fixação aplicados aos colaboradores para medir aproveitamento e calibrar o Score de Risco de Evasão.
                    </p>
                </div>

                <a href="{{ route('instructor.courses.quizzes.create', $courseId) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 text-white font-semibold text-xs hover:bg-blue-700 transition-colors shadow-sm self-start sm:self-auto">
                    <x-icon name="plus-lg" class="w-4 h-4" />
                    <span>Nova Avaliação</span>
                </a>
            </div>

            @if(!$course || $course->quizzes->isEmpty())
                <div class="p-12 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <x-icon name="journal-text" class="w-6 h-6 text-blue-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Nenhuma avaliação cadastrada</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Crie quizzes ao final de cada módulo para certificar a retenção dos colaboradores.
                    </p>
                    <a href="{{ route('instructor.courses.quizzes.create', $courseId) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-medium text-xs hover:bg-blue-700 transition-colors">
                        + Criar Primeira Avaliação
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($course->quizzes as $quiz)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 transition-colors flex flex-col justify-between gap-3 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">{{ $quiz->title }}</h3>
                                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                                        <span>{{ $quiz->questions->count() }} questões</span>
                                        @if($quiz->module)
                                            <span>&bull;</span>
                                            <span>Módulo: {{ $quiz->module->title }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                    Corte: {{ $quiz->passing_score }}%
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-slate-200/70">
                                <span class="text-[11px] font-mono text-slate-400">ID #{{ $quiz->id }}</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('instructor.quizzes.edit', $quiz) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                                        Editar Questões
                                    </a>
                                    <button x-on:click="AlvoraSwal.fire({ icon: 'warning', title: 'Tem certeza?', text: 'Tem certeza de que deseja excluir a avaliação \'{{ $quiz->title }}\'?', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Sim, excluir', cancelButtonText: 'Cancelar' }).then(r => { if (r.isConfirmed) $wire.deleteQuiz({{ $quiz->id }}) })" 
                                            type="button" 
                                            class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors">
                                        <x-icon name="trash3" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- ABA 4: MÍDIA & PUBLICAÇÃO (SE O CURSO ESTIVER SALVO)     -->
    <!-- ======================================================== -->
    @if($activeTab === 'publish' && $courseId)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Configurações de Publicação & URL Amigável
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Ajuste o identificador permanente (slug) do curso e o status de catálogo.
                </p>
            </div>

            <!-- Slug da URL -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-700" for="course-slug">
                    Identificador de URL (Slug Permanente)
                </label>
                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 overflow-hidden focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                    <span class="px-3.5 py-2.5 text-xs text-slate-400 bg-slate-100 border-r border-slate-200 font-mono select-none">
                        /aluno/cursos/
                    </span>
                    <input id="course-slug" 
                           wire:model="slug" 
                           type="text" 
                           class="w-full bg-transparent px-3 py-2 text-xs text-slate-800 font-mono focus:outline-none">
                </div>
            </div>

            <!-- Botão Salvar -->
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" 
                        wire:click="save" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white font-semibold text-xs hover:bg-blue-700 transition-all shadow-sm">
                    Salvar Publicação
                </button>
            </div>
        </div>
    @endif
</div>
