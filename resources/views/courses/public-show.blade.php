<x-guest-layout
    :title="$course->title . ' — Capacitação Corporativa | Alvora'"
    :description="Str::limit(strip_tags($course->description ?? 'Treinamento corporativo com acompanhamento de retenção e telemetria preditiva de IA.'), 160)"
    theme="editorial"
    :hideNav="true"
>
    @php
        $previewUrl = $previewLesson->video_url ?? null;
        $previewYoutubeId = null;
        if ($previewUrl && (str_contains($previewUrl, 'youtube.com') || str_contains($previewUrl, 'youtu.be'))) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $previewUrl, $matches);
            $previewYoutubeId = $matches[1] ?? null;
        }

        $totalMins = $course->total_duration_minutes ?? ($course->lessons_count * 15);
        $hours = floor($totalMins / 60);
        $mins = $totalMins % 60;
    @endphp

    <div x-data="{
        activeTab: 'visao-geral',
        showPreviewModal: false,
        copiedToast: false,
        openModules: [{{ $course->modules->first()->id ?? 0 }}],
        allExpanded: false,
        toggleModule(id) {
            if (this.openModules.includes(id)) {
                this.openModules = this.openModules.filter(m => m !== id);
            } else {
                this.openModules.push(id);
            }
        },
        toggleAll() {
            if (this.allExpanded) {
                this.openModules = [];
                this.allExpanded = false;
            } else {
                this.openModules = [{{ $course->modules->pluck('id')->join(',') }}];
                this.allExpanded = true;
            }
        },
        copyShareLink() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href);
                this.copiedToast = true;
                setTimeout(() => { this.copiedToast = false; }, 3000);
            }
        }
    }" class="bg-[#F8FAFC] min-h-screen flex flex-col text-slate-800">

        <!-- 1. NAVBAR OFICIAL DA LANDING PAGE -->
        <x-landing.navbar />

        <!-- 2. CLEAN EXECUTIVE HEADER (Inspirado na demo Creativeitem, limpo e sem poluição) -->
        <header class="bg-white border-b border-slate-200/80 pt-6 pb-8">
            <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-slate-500 text-xs sm:text-sm mb-4 font-medium flex-wrap">
                    <a href="{{ url('/') }}" class="hover:text-edtech-blue transition-colors">Início</a>
                    <span class="text-slate-400">/</span>
                    <a href="{{ url('/#cursos') }}" class="hover:text-edtech-blue transition-colors">Programas &amp; Cursos</a>
                    @if($course->category)
                        <span class="text-slate-400">/</span>
                        <span class="text-slate-600">{{ $course->category->name }}</span>
                    @endif
                    <span class="text-slate-400">/</span>
                    <span class="text-slate-800 truncate max-w-[280px] sm:max-w-md font-semibold">{{ $course->title }}</span>
                </nav>

                {{-- Course Title H1 --}}
                <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold text-slate-900 tracking-tight leading-tight mb-3">
                    {{ $course->title }}
                </h1>

                {{-- Course Subtitle --}}
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-3xl mb-5 font-normal">
                    {{ $course->description ? Str::limit(strip_tags($course->description), 200) : 'Capacite seu time com metodologia prática, foco em tomada de decisão e acompanhamento contínuo por inteligência artificial para maximizar a retenção e a produtividade.' }}
                </p>

                {{-- Clean Horizontal Meta Row --}}
                <div class="flex flex-wrap items-center gap-y-3 gap-x-6 text-xs sm:text-sm text-slate-600 pt-2 border-t border-slate-100">
                    {{-- Instructor --}}
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-edtech-blue-light text-edtech-blue font-bold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($course->instructor->name ?? 'Prof. Carlos Eduardo', 0, 2)) }}
                        </div>
                        <span>Criado por <strong class="text-slate-900 font-semibold">{{ $course->instructor->name ?? 'Prof. Carlos Eduardo' }}</strong></span>
                    </div>

                    {{-- Duration --}}
                    <div class="flex items-center gap-1.5">
                        <x-icon name="clock" class="w-4 h-4 text-slate-400" />
                        <span>
                            @if($hours > 0)
                                {{ $hours }} Horas de Carga
                            @else
                                {{ $totalMins }} min
                            @endif
                        </span>
                    </div>

                    {{-- Structure --}}
                    <div class="flex items-center gap-1.5">
                        <x-icon name="book-open" class="w-4 h-4 text-slate-400" />
                        <span>{{ $course->lessons_count }} aulas • {{ $course->modules_count }} módulos</span>
                    </div>

                    {{-- Language --}}
                    <div class="flex items-center gap-1.5">
                        <x-icon name="check-circle" class="w-4 h-4 text-emerald-600" />
                        <span>Português (BR)</span>
                    </div>

                    {{-- B2B Tag --}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-edtech-blue border border-blue-200">
                        Certificação Corporativa Oficial
                    </span>
                </div>
            </div>
        </header>

        <!-- 3. MAIN CANVAS (2 COLUNAS: 68% Conteúdo com Abas Interativas / 32% Card Fixo) -->
        <main class="max-w-[1360px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 lg:py-10 flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- ================= LEFT COLUMN: ABAS INTERATIVAS (lg:col-span-8) ================= -->
                <section class="lg:col-span-8 space-y-6">

                    {{-- Horizontal Tab Navigation Pills/Underline (Creativeitem Academy style) --}}
                    <div class="bg-white rounded-xl border border-slate-200/80 p-1.5 shadow-xs flex items-center gap-1 overflow-x-auto no-scrollbar">
                        <button type="button"
                                @click="activeTab = 'visao-geral'"
                                :class="activeTab === 'visao-geral'
                                    ? 'bg-edtech-blue text-white shadow-xs font-semibold'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition-all cursor-pointer">
                            <x-icon name="file-text" class="w-4 h-4" />
                            <span>Visão Geral</span>
                        </button>

                        <button type="button"
                                @click="activeTab = 'grade-curricular'"
                                :class="activeTab === 'grade-curricular'
                                    ? 'bg-edtech-blue text-white shadow-xs font-semibold'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition-all cursor-pointer">
                            <x-icon name="book-open" class="w-4 h-4" />
                            <span>Grade Curricular ({{ $course->lessons_count }})</span>
                        </button>

                        <button type="button"
                                @click="activeTab = 'instrutor'"
                                :class="activeTab === 'instrutor'
                                    ? 'bg-edtech-blue text-white shadow-xs font-semibold'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition-all cursor-pointer">
                            <x-icon name="user" class="w-4 h-4" />
                            <span>Instrutor</span>
                        </button>

                        <button type="button"
                                @click="activeTab = 'garantias'"
                                :class="activeTab === 'garantias'
                                    ? 'bg-edtech-blue text-white shadow-xs font-semibold'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm whitespace-nowrap transition-all cursor-pointer">
                            <x-icon name="shield" class="w-4 h-4" />
                            <span>Garantias B2B</span>
                        </button>
                    </div>

                    {{-- Unified Tab Content Card (Clean, light and organized) --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs min-h-[460px]">

                        <!-- ================= TAB 1: VISÃO GERAL ================= -->
                        <div x-show="activeTab === 'visao-geral'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-8">

                            <div>
                                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-3">
                                    Sobre este Treinamento Corporativo
                                </h3>
                                <div class="text-sm sm:text-base text-slate-600 leading-relaxed space-y-4 font-normal">
                                    @if($course->description)
                                        {!! nl2br(e($course->description)) !!}
                                    @else
                                        <p>
                                            Este programa executivo foi especialmente desenhado para capacitar equipes corporativas de alto desempenho. Através de uma abordagem prática baseada em casos reais de negócio, os colaboradores desenvolvem competências críticas com foco em aplicação imediata na rotina de trabalho.
                                        </p>
                                        <p>
                                            Combinando conteúdo denso em vídeo, atividades práticas e acompanhamento inteligente pelo Tutor de IA Alvora, o treinamento assegura taxas de retenção e conclusão superiores à média do mercado.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- 3 Clean Impact Metrics --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-edtech-blue flex items-center justify-center shrink-0">
                                        <x-icon name="trending-up" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <span class="text-base font-bold text-slate-900 block leading-tight">+40% Eficiência</span>
                                        <span class="text-xs text-slate-500 mt-0.5 block leading-snug">Otimização de rotinas repetitivas</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <x-icon name="shield" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <span class="text-base font-bold text-slate-900 block leading-tight">100% LGPD</span>
                                        <span class="text-xs text-slate-500 mt-0.5 block leading-snug">Conformidade e governança de dados</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                        <x-icon name="award" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <span class="text-base font-bold text-slate-900 block leading-tight">3.2x ROI</span>
                                        <span class="text-xs text-slate-500 mt-0.5 block leading-snug">Retorno estimado nos primeiros 90 dias</span>
                                    </div>
                                </div>
                            </div>

                            {{-- O que sua equipe vai aprender --}}
                            <div class="pt-6 border-t border-slate-100">
                                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">
                                    O que sua equipe vai aprender
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500 mb-4">
                                    Competências práticas desenvolvidas ao longo do treinamento:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Aplicação prática imediata das técnicas na rotina corporativa</span>
                                    </div>
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Resolução de desafios complexos com mentoria do Tutor IA 24/7</span>
                                    </div>
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Otimização de fluxos e redução de gargalos departamentais</span>
                                    </div>
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Fixação do conhecimento por meio de quizzes e feedback imediato</span>
                                    </div>
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Metodologias recomendadas por especialistas do mercado</span>
                                    </div>
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/60">
                                        <x-icon name="check" class="w-4 h-4 text-edtech-blue shrink-0 mt-0.5" />
                                        <span class="text-xs sm:text-sm text-slate-700 leading-snug">Alinhamento estratégico com metas e indicadores de desempenho (KPIs)</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Requisitos e Diretrizes --}}
                            <div class="pt-6 border-t border-slate-100">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                                    <x-icon name="clipboard-check" class="w-5 h-5 text-edtech-blue" />
                                    <span>Pré-Requisitos e Diretrizes para o Participante</span>
                                </h3>
                                <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600">
                                    <li class="flex items-start gap-2.5">
                                        <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                        <span><strong>Experiência Corporativa:</strong> Recomendado para colaboradores, líderes e gestores atuantes em projetos corporativos.</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                        <span><strong>Sem necessidade de programação:</strong> Conteúdo orientado a aplicação prática e tomadores de decisão.</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                        <span><strong>Ambiente Fornecido:</strong> Sandbox seguro e acompanhamento contínuo pelo Tutor IA 24/7 integrado.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- ================= TAB 2: GRADE CURRICULAR ================= -->
                        <div x-show="activeTab === 'grade-curricular'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-6"
                             style="display: none;">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200/80 gap-3">
                                <div>
                                    <h3 class="text-lg sm:text-xl font-bold text-slate-900">
                                        Grade Curricular do Programa
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                                        {{ $course->modules_count }} módulos estruturados • {{ $course->lessons_count }} aulas • Carga horária corporativa
                                    </p>
                                </div>
                                <button type="button"
                                        @click="toggleAll()"
                                        class="text-xs font-semibold text-edtech-blue hover:text-edtech-blue-hover flex items-center gap-1 self-start sm:self-auto cursor-pointer">
                                    <span x-text="allExpanded ? 'Recolher todos os módulos' : 'Expandir todos os módulos'"></span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="allExpanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            @if($course->modules->isNotEmpty())
                                <div class="space-y-3.5">
                                    @foreach($course->modules as $index => $module)
                                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs transition-all">
                                            {{-- Module Header Button --}}
                                            <button type="button"
                                                    @click="toggleModule({{ $module->id }})"
                                                    class="w-full px-5 py-4 bg-slate-50/80 hover:bg-slate-100/70 flex items-center justify-between text-left transition-colors cursor-pointer">
                                                <div class="flex items-center gap-3 min-w-0 pr-4">
                                                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-edtech-blue font-bold text-xs flex items-center justify-center shrink-0">
                                                        {{ sprintf('%02d', $index + 1) }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <h4 class="font-semibold text-sm sm:text-base text-slate-900 truncate leading-tight">
                                                            {{ $module->title }}
                                                        </h4>
                                                        <span class="text-xs text-slate-500 block mt-0.5">
                                                            {{ $module->lessons->count() }} {{ $module->lessons->count() === 1 ? 'aula' : 'aulas' }}
                                                            @php $modMinutes = $module->lessons->sum('duration_minutes'); @endphp
                                                            @if($modMinutes > 0)
                                                                • {{ $modMinutes }} min de imersão
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3 shrink-0">
                                                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                                         :class="openModules.includes({{ $module->id }}) ? 'rotate-180 text-edtech-blue' : ''"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </button>

                                            {{-- Module Lessons List --}}
                                            <div x-show="openModules.includes({{ $module->id }})"
                                                 x-collapse
                                                 class="divide-y divide-slate-100 px-5 py-1 bg-white">
                                                @forelse($module->lessons as $lesson)
                                                    <div class="py-3 flex items-center justify-between gap-4 text-xs sm:text-sm">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            @if($lesson->is_free_preview)
                                                                <button type="button"
                                                                        @click="showPreviewModal = true"
                                                                        class="w-6 h-6 rounded-full bg-edtech-blue text-white flex items-center justify-center shrink-0 hover:bg-edtech-blue-hover transition-colors shadow-2xs cursor-pointer"
                                                                        title="Assistir aula aberta">
                                                                    <x-icon name="play" class="w-3 h-3 text-white ml-0.5" />
                                                                </button>
                                                            @else
                                                                <x-icon name="lock" class="w-4 h-4 text-slate-400 shrink-0" />
                                                            @endif

                                                            <span class="text-slate-800 font-medium truncate">
                                                                {{ $lesson->title }}
                                                            </span>

                                                            @if($lesson->is_free_preview)
                                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                                                                    Aula Grátis
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center gap-3 shrink-0 text-slate-400 text-xs">
                                                            @if($lesson->duration_minutes)
                                                                <span class="font-mono tabular-nums">{{ $lesson->duration_minutes }}:00</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="py-3 text-xs text-slate-400 text-center">Nenhuma aula cadastrada neste módulo.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 text-slate-500 text-sm">
                                    Grade curricular em fase de diagramação curatorial.
                                </div>
                            @endif
                        </div>

                        <!-- ================= TAB 3: INSTRUTOR ================= -->
                        <div x-show="activeTab === 'instrutor'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-6"
                             style="display: none;">

                            <div>
                                <span class="text-xs uppercase tracking-wider font-bold text-edtech-blue">Corpo Docente Especializado</span>
                                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mt-1">
                                    Sobre o Instrutor Responsável
                                </h3>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start gap-6 pt-2">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[#0B1528] to-edtech-blue text-white flex items-center justify-center font-bold text-xl sm:text-2xl shrink-0 shadow-sm">
                                    @php
                                        $instructorName = $course->instructor->name ?? 'Prof. Carlos Eduardo';
                                        $initials = collect(explode(' ', $instructorName))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->join('');
                                    @endphp
                                    <span>{{ $initials ?: 'AV' }}</span>
                                </div>

                                <div class="space-y-3 flex-1">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-bold text-base sm:text-lg text-slate-900 leading-tight">
                                                {{ $instructorName }}
                                            </h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-edtech-blue border border-blue-200">
                                                <x-icon name="check-circle" class="w-3.5 h-3.5 text-edtech-blue" />
                                                Docente Verificado Alvora
                                            </span>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold text-edtech-blue mt-0.5">
                                            Especialista em Capacitação Corporativa e Transformação Digital
                                        </p>
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                                        Com mais de 15 anos liderando estratégias de tecnologia e dados, atua no desenvolvimento contínuo de líderes executivos e equipes multidisciplinares. Já mentorou mais de 12.000 profissionais em transição cultural e metodologias de alta retenção.
                                    </p>

                                    <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-slate-100 text-xs sm:text-sm">
                                        <div class="flex items-center gap-1.5 text-slate-900 font-semibold">
                                            <x-icon name="users" class="w-4 h-4 text-edtech-blue" />
                                            <span>+12.400 Alunos Capacitados</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-slate-900 font-semibold">
                                            <x-icon name="book-open" class="w-4 h-4 text-slate-500" />
                                            <span>Programas Homologados</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= TAB 4: GARANTIAS B2B ================= -->
                        <div x-show="activeTab === 'garantias'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-6"
                             style="display: none;">

                            <div>
                                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-1">
                                    Garantia Corporativa Alvora &amp; Acordos B2B
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500">
                                    Processo de contratação facilitado e estruturado para o departamento de Recursos Humanos e Compras:
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-edtech-blue flex items-center justify-center shrink-0">
                                        <x-icon name="file-text" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">Nota Fiscal Faturada B2B</h4>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Emissão imediata de Nota Fiscal Eletrônica e opção de faturamento por centro de custo via Boleto Bancário 28D.</p>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <x-icon name="check-circle" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">SLA de Retenção &amp; Engajamento</h4>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Monitoramento de evasão e relatórios de progresso em tempo real disponibilizados ao gestor da turma.</p>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                        <x-icon name="shield" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">Integração SSO Corporativo</h4>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Compatibilidade com SAML 2.0, Microsoft Azure AD e Google Workspace para login corporativo seguro.</p>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <x-icon name="award" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">Certificados com Validação QR Code</h4>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Certificação oficial individual com UUID único e página pública de verificação criptográfica.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- ================= RIGHT COLUMN: STICKY ENROLLMENT CARD (lg:col-span-4) ================= -->
                <aside class="lg:col-span-4 space-y-5">
                    <div class="lg:sticky lg:top-24 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                        {{-- Video Preview Thumbnail (16:9 ratio with play button) --}}
                        <div class="relative bg-slate-900 aspect-video group cursor-pointer overflow-hidden"
                             @click="showPreviewModal = true">
                            @if($course->thumbnail_url)
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-85">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[#0B1528] via-[#102A56] to-edtech-blue p-5 flex flex-col justify-between text-white">
                                    <span class="text-xs uppercase tracking-wider text-slate-300 font-semibold">{{ $course->category->name ?? 'Treinamento Corporativo' }}</span>
                                    <h4 class="font-bold text-base text-white line-clamp-2">{{ $course->title }}</h4>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/20 to-transparent"></div>

                            {{-- Central Play Overlay --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-white">
                                <div class="w-12 h-12 rounded-full bg-edtech-blue text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                                    <x-icon name="play" class="w-5 h-5 text-white ml-0.5" />
                                </div>
                                <span class="text-xs font-semibold text-white drop-shadow">Assistir Prévia</span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-6">
                            {{-- Price Section --}}
                            <div class="mb-5 pb-5 border-b border-slate-100">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">R$ 490</span>
                                    <span class="text-xs sm:text-sm font-medium text-slate-500">/ colaborador</span>
                                </div>
                                <div class="mt-2 p-2 rounded-lg bg-blue-50/60 border border-blue-100 flex items-center gap-2 text-xs text-slate-700 leading-snug">
                                    <x-icon name="award" class="w-4 h-4 text-edtech-blue shrink-0" />
                                    <span>Incluso sem custo adicional no <strong>Plano Enterprise Alvora</strong>.</span>
                                </div>
                            </div>

                            {{-- Key Specs List (Clean & Aligned) --}}
                            <div class="space-y-2.5 text-xs sm:text-sm text-slate-600 mb-6">
                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="flex items-center gap-2 text-slate-500">
                                        <x-icon name="video" class="w-4 h-4 text-slate-400" />
                                        <span>Total de Aulas</span>
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ $course->lessons_count }}</span>
                                </div>

                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="flex items-center gap-2 text-slate-500">
                                        <x-icon name="check-circle" class="w-4 h-4 text-slate-400" />
                                        <span>Quizzes de Fixação</span>
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ $course->quizzes_count > 0 ? $course->quizzes_count . ' quizzes' : 'Incluso' }}</span>
                                </div>

                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="flex items-center gap-2 text-slate-500">
                                        <x-icon name="trending-up" class="w-4 h-4 text-slate-400" />
                                        <span>Nível do Programa</span>
                                    </span>
                                    <span class="font-semibold text-slate-900">Corporativo / B2B</span>
                                </div>

                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <span class="flex items-center gap-2 text-slate-500">
                                        <x-icon name="clock" class="w-4 h-4 text-slate-400" />
                                        <span>Prazo de Acesso</span>
                                    </span>
                                    <span class="font-semibold text-slate-900">12 meses</span>
                                </div>

                                <div class="flex items-center justify-between py-1">
                                    <span class="flex items-center gap-2 text-slate-500">
                                        <x-icon name="award" class="w-4 h-4 text-emerald-600" />
                                        <span>Certificado Digital</span>
                                    </span>
                                    <span class="font-semibold text-emerald-700">Sim (com QR Code)</span>
                                </div>
                            </div>

                            {{-- CTA Buttons --}}
                            <div class="space-y-2.5 mb-5">
                                @if($isEnrolled)
                                    <a href="{{ route('student.player', $course->slug) }}"
                                       class="w-full h-11 bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 text-sm">
                                        <span>Continuar Assistindo no Painel</span>
                                        <x-icon name="arrow-right" class="w-4 h-4" />
                                    </a>
                                @else
                                    <a href="{{ url('/#apresentacao') }}"
                                       class="w-full h-11 bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 text-sm cursor-pointer">
                                        <span>Capacitar Minha Equipe</span>
                                        <x-icon name="users" class="w-4 h-4" />
                                    </a>

                                    <a href="{{ route('login') }}"
                                       class="w-full h-10 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold rounded-xl transition-all flex items-center justify-center gap-2 text-xs sm:text-sm">
                                        <span>Já é colaborador? Fazer Login</span>
                                        <x-icon name="log-in" class="w-4 h-4 text-slate-400" />
                                    </a>
                                @endif
                            </div>

                            {{-- Auxiliary Share Links --}}
                            <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-xs text-slate-500">
                                <button type="button"
                                        @click="copyShareLink()"
                                        class="hover:text-edtech-blue transition-colors flex items-center gap-1.5 cursor-pointer">
                                    <x-icon name="share-2" class="w-4 h-4" />
                                    <span>Compartilhar curso</span>
                                </button>
                                <a href="{{ url('/#apresentacao') }}" class="hover:text-edtech-blue transition-colors flex items-center gap-1.5">
                                    <x-icon name="help-circle" class="w-4 h-4" />
                                    <span>Dúvidas comerciais</span>
                                </a>
                            </div>

                            {{-- Toast Notification --}}
                            <div x-show="copiedToast"
                                 x-transition
                                 class="mt-2.5 p-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg text-center font-medium"
                                 style="display: none;">
                                Link copiado para a área de transferência!
                            </div>
                        </div>
                    </div>

                    {{-- Small B2B Cohort Card --}}
                    <div class="bg-white rounded-xl border border-slate-200/80 p-4.5 shadow-2xs">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-edtech-blue flex items-center justify-center shrink-0">
                                <x-icon name="building-2" class="w-4 h-4 text-edtech-blue" />
                            </div>
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900">Treinar mais de 50 colaboradores?</h4>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                                    Consulte condições especiais de faturamento e turmas fechadas com mentorias exclusivas.
                                </p>
                                <a href="{{ url('/#apresentacao') }}" class="inline-flex items-center gap-1 font-semibold text-edtech-blue text-xs mt-2 hover:underline">
                                    <span>Falar com especialista institucional</span>
                                    <x-icon name="arrow-right" class="w-3 h-3" />
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </main>

        <!-- 4. RELATED COURSES (Clean 3-Card Grid) -->
        @if($relatedCourses->isNotEmpty())
            <section class="py-12 sm:py-14 bg-white border-t border-slate-200/80">
                <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                        <div>
                            <span class="text-xs font-bold text-edtech-blue uppercase tracking-wider block mb-1">
                                Portfólio Corporativo
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                                Treinamentos Corporativos Relacionados
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                Programas recomendados para líderes e colaboradores em desenvolvimento contínuo.
                            </p>
                        </div>
                        <a href="{{ url('/#cursos') }}" class="text-xs sm:text-sm font-semibold text-edtech-blue hover:text-edtech-blue-hover inline-flex items-center gap-1.5 shrink-0">
                            <span>Ver catálogo completo</span>
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($relatedCourses as $index => $relCourse)
                            @php
                                $coverThemes = [
                                    ['category' => 'Liderança & Estratégia', 'gradient' => 'from-[#0B1528] via-[#102A56] to-[#1D4ED8]'],
                                    ['category' => 'Inteligência & Analytics', 'gradient' => 'from-[#0A192F] via-[#133E7C] to-[#2563EB]'],
                                    ['category' => 'Gestão de Pessoas & Cultura', 'gradient' => 'from-[#081734] via-[#1E3A8A] to-[#1E40AF]'],
                                ];
                                $meta = $coverThemes[$index % count($coverThemes)];
                            @endphp

                            <article class="group bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:-translate-y-1 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
                                <div>
                                    {{-- Banner Cover with Course Thumbnail or Gradient Fallback --}}
                                    <div class="h-40 overflow-hidden relative bg-slate-900">
                                        @if($relCourse->thumbnail_url)
                                            <img src="{{ $relCourse->thumbnail_url }}"
                                                 alt="{{ $relCourse->title }}"
                                                 loading="lazy"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-slate-950/30"></div>
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br {{ $meta['gradient'] }} transition-transform duration-300 group-hover:scale-105"></div>
                                        @endif

                                        <div class="absolute inset-0 p-4.5 flex flex-col justify-between text-white z-10">
                                            <div class="flex justify-between items-start">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-black/50 backdrop-blur-md text-white border border-white/20">
                                                    {{ $relCourse->category->name ?? $meta['category'] }}
                                                </span>
                                                <img src="{{ asset('images/logo-symbol.png') }}" width="18" height="14" alt="Alvora" class="h-3.5 w-auto opacity-85 shrink-0 drop-shadow">
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold text-white leading-tight line-clamp-2 drop-shadow-md">
                                                <a href="{{ route('courses.public.show', $relCourse->slug) }}" class="hover:underline">
                                                    {{ $relCourse->title }}
                                                </a>
                                            </h3>
                                        </div>
                                    </div>

                                    {{-- Body --}}
                                    <div class="p-5">
                                        @if($relCourse->instructor)
                                            <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-slate-100">
                                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs shrink-0 flex items-center justify-center">
                                                    {{ strtoupper(substr($relCourse->instructor->name, 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-semibold text-slate-800 truncate">{{ $relCourse->instructor->name }}</div>
                                                    <div class="text-[11px] text-slate-400">Docente Alvora</div>
                                                </div>
                                            </div>
                                        @endif

                                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 font-normal">
                                            {{ Str::limit(strip_tags($relCourse->description ?? 'Capacitação prática com foco em retenção e aplicabilidade corporativa.'), 120) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer Link --}}
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">
                                        {{ $relCourse->lessons_count }} aulas
                                        @if($relCourse->total_duration_minutes)
                                            • {{ $relCourse->total_duration_minutes }} min
                                        @endif
                                    </span>
                                    <a href="{{ route('courses.public.show', $relCourse->slug) }}" class="font-semibold text-edtech-blue hover:text-edtech-blue-hover flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                        <span>Ver Detalhes</span>
                                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- 5. FOOTER OFICIAL DA LANDING PAGE -->
        <x-landing.footer />

        <!-- 6. VIDEO PREVIEW MODAL (Alpine.js) -->
        <div x-show="showPreviewModal"
             x-cloak
             @keydown.escape.window="showPreviewModal = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             role="dialog"
             aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
                 @click="showPreviewModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all w-full max-w-4xl"
                     @click.away="showPreviewModal = false">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#0B1528] text-white">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-edtech-blue"></span>
                            <h3 class="font-bold text-sm sm:text-base text-white truncate max-w-lg">
                                {{ $previewLesson->title ?? 'Prévia Executiva do Treinamento' }}
                            </h3>
                        </div>
                        <button type="button"
                                @click="showPreviewModal = false"
                                class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Modal Body: Player Embed or Video --}}
                    <div class="relative aspect-video w-full bg-black">
                        @if($previewYoutubeId)
                            <template x-if="showPreviewModal">
                                <iframe class="w-full h-full"
                                        src="https://www.youtube-nocookie.com/embed/{{ $previewYoutubeId }}?autoplay=1&rel=0&modestbranding=1"
                                        title="{{ $course->title }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen></iframe>
                            </template>
                        @elseif($previewUrl)
                            <template x-if="showPreviewModal">
                                <video class="w-full h-full" controls autoplay controlsList="nodownload">
                                    <source src="{{ $previewUrl }}" type="video/mp4">
                                    Seu navegador não suporta reprodução de vídeo HTML5.
                                </video>
                            </template>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center text-white space-y-4">
                                <x-icon name="video" class="w-12 h-12 text-edtech-blue" />
                                <div>
                                    <h4 class="font-bold text-lg">Degustação Executiva</h4>
                                    <p class="text-xs sm:text-sm text-slate-400 max-w-md mt-1">
                                        As aulas completas e interativas estão disponíveis para colaboradores vinculados a empresas clientes ou através do plano institucional.
                                    </p>
                                </div>
                                <a href="{{ url('/#apresentacao') }}" class="px-5 py-2.5 bg-edtech-blue hover:bg-edtech-blue-hover text-white text-xs font-semibold rounded-xl transition-all shadow-sm">
                                    Solicitar Apresentação Completa
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-3.5 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>Treinamento Corporativo Oficial • Alvora Education</span>
                        <a href="{{ url('/#apresentacao') }}" class="font-semibold text-edtech-blue hover:text-blue-400">
                            Capacitar minha equipe &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
