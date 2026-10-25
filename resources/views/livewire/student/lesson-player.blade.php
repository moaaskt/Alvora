<div class="min-h-screen bg-[#090D16] text-slate-200">
    {{-- Topbar do Player com Progresso do Curso --}}
    <header class="bg-gray-900/80 backdrop-blur-md border-b border-white/10 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 py-3.5">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a
                    href="{{ route('student.dashboard') }}"
                    class="p-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white transition border border-white/5 flex items-center gap-1.5 text-xs font-semibold"
                    title="Voltar ao Painel do Aluno"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Painel</span>
                </a>

                <div>
                    <h1 class="text-sm sm:text-base font-bold text-white line-clamp-1">
                        {{ $course->title }}
                    </h1>
                    <p class="text-xs text-gray-400">
                        @if ($currentLesson)
                            {{ $currentLesson->module->title ?? 'Módulo' }} &bull; {{ $currentLesson->title }}
                        @else
                            Nenhuma aula selecionada
                        @endif
                    </p>
                </div>
            </div>

            {{-- Progresso Geral no Curso --}}
            <div class="flex items-center gap-4">
                <div class="w-36 sm:w-48 text-right">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Progresso</span>
                        <span class="font-bold text-emerald-400">{{ $progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-1.5 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>

                @if ($progressPercent >= 100)
                    @if ($this->isEligibleForCertificate())
                        <a
                            href="{{ route('student.certificate', $course->slug) }}"
                            target="_blank"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30 transition shadow-lg shadow-emerald-500/20"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Baixar Certificado 🎓
                        </a>
                    @else
                        <span
                            title="Você precisa ser aprovado em todos os quizzes do curso para liberar o certificado"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-amber-500/10 text-amber-300/80 border border-amber-500/30 cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Quizzes Pendentes 🎓
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </header>

    {{-- Notificações / Flash --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs sm:text-sm flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-300">✕</button>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs sm:text-sm flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-amber-400/60 hover:text-amber-300">✕</button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs sm:text-sm flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-400/60 hover:text-rose-300">✕</button>
            </div>
        </div>
    @endif

    {{-- Grid Principal: Player à Esquerda, Grade de Aulas à Direita --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Coluna 1: Player de Vídeo e Abas de Apoio (8 colunas) --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Container do Vídeo --}}
                <div class="aspect-video bg-black rounded-2xl overflow-hidden border border-white/10 shadow-2xl relative flex items-center justify-center">
                    @if ($currentLesson && $currentLesson->video_url)
                        @php
                            $url = $currentLesson->video_url;
                            $isYoutube = str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
                            $isVimeo = str_contains($url, 'vimeo.com');

                            if ($isYoutube) {
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match);
                                $youtubeId = $match[1] ?? null;
                            } elseif ($isVimeo) {
                                preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $match);
                                $vimeoId = $match[3] ?? null;
                            }
                        @endphp

                        @if ($isYoutube && isset($youtubeId))
                            <iframe
                                class="w-full h-full"
                                src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1"
                                title="{{ $currentLesson->title }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        @elseif ($isVimeo && isset($vimeoId))
                            <iframe
                                class="w-full h-full"
                                src="https://player.vimeo.com/video/{{ $vimeoId }}?title=0&byline=0&portrait=0"
                                frameborder="0"
                                allow="autoplay; fullscreen; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @else
                            {{-- HTML5 Video Direct File (MP4, WebM) --}}
                            <video
                                class="w-full h-full object-contain"
                                controls
                                controlsList="nodownload"
                                src="{{ $currentLesson->video_url }}"
                            >
                                Seu navegador não suporta a tag de vídeo.
                            </video>
                        @endif
                    @elseif ($currentLesson)
                        <div class="text-center p-8 space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 mx-auto flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Conteúdo Textual e Leitura</h3>
                            <p class="text-sm text-gray-400 max-w-md mx-auto">
                                Esta aula possui conteúdo teórico ou materiais complementares para download disponíveis nas abas abaixo.
                            </p>
                        </div>
                    @else
                        <div class="text-center p-8 space-y-2 text-gray-400">
                            <p class="text-sm">Nenhuma aula cadastrada neste curso até o momento.</p>
                        </div>
                    @endif
                </div>

                {{-- Barra de Ações da Aula (Conclusão e Próxima/Anterior) --}}
                @if ($currentLesson)
                    <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-medium text-indigo-400 uppercase tracking-wider">
                                {{ $currentLesson->module->title ?? 'Módulo do Curso' }}
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold text-white mt-0.5">
                                {{ $currentLesson->title }}
                            </h2>
                            <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                                @if ($currentLesson->duration_minutes)
                                    <span>⏱ {{ $currentLesson->duration_minutes }} min estimados</span>
                                    <span>&bull;</span>
                                @endif
                                <span>{{ $currentLesson->attachments->count() }} anexo(s)</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Botão Concluir Aula --}}
                            <button
                                wire:click="toggleComplete"
                                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2
                                {{ $isCompleted
                                    ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30'
                                    : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30' }}"
                            >
                                @if ($isCompleted)
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Concluída ✓
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Marcar como Concluída
                                @endif
                            </button>

                            {{-- Botão Aula Anterior --}}
                            <button
                                wire:click="previousLesson"
                                @disabled(!$hasPrevious)
                                class="p-2.5 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition border border-white/5"
                                title="Aula Anterior"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            {{-- Botão Próxima Aula --}}
                            <button
                                wire:click="nextLesson"
                                @disabled(!$hasNext)
                                class="p-2.5 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition border border-white/5"
                                title="Próxima Aula"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Abas: Anotações Pessoais, Materiais e Visão Geral --}}
                    <div class="bg-gray-900/60 border border-white/5 rounded-2xl overflow-hidden">
                        {{-- Barra de Abas --}}
                        <div class="flex border-b border-white/5 px-6 pt-2">
                            <button
                                wire:click="setTab('notes')"
                                class="py-3 px-4 text-xs sm:text-sm font-semibold transition border-b-2 flex items-center gap-2
                                {{ $activeTab === 'notes' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Minhas Anotações
                            </button>

                            <button
                                wire:click="setTab('materials')"
                                class="py-3 px-4 text-xs sm:text-sm font-semibold transition border-b-2 flex items-center gap-2
                                {{ $activeTab === 'materials' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Materiais de Apoio ({{ $currentLesson->attachments->count() }})
                            </button>

                            <button
                                wire:click="setTab('overview')"
                                class="py-3 px-4 text-xs sm:text-sm font-semibold transition border-b-2 flex items-center gap-2
                                {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sobre a Aula
                            </button>
                        </div>

                        {{-- Conteúdo da Aba 1: Anotações Pessoais --}}
                        @if ($activeTab === 'notes')
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Suas anotações são particulares e salvas automaticamente
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="saveNote"
                                        class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition text-xs border border-white/5"
                                    >
                                        Salvar Agora
                                    </button>
                                </div>

                                <textarea
                                    wire:model.live.debounce.1000ms="noteContent"
                                    wire:change="saveNote"
                                    rows="6"
                                    placeholder="Escreva suas considerações, insights ou tópicos importantes desta aula..."
                                    class="w-full bg-gray-950/60 border border-white/10 rounded-xl p-4 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition leading-relaxed"
                                ></textarea>
                            </div>
                        @endif

                        {{-- Conteúdo da Aba 2: Materiais Complementares --}}
                        @if ($activeTab === 'materials')
                            <div class="p-6">
                                @if ($currentLesson->attachments->isEmpty())
                                    <p class="text-sm text-gray-400 text-center py-4">
                                        Nenhum material complementar anexado a esta aula.
                                    </p>
                                @else
                                    <ul class="divide-y divide-white/5">
                                        @foreach ($currentLesson->attachments as $attachment)
                                            <li class="py-3 flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <p class="text-sm font-semibold text-white">{{ $attachment->title }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            {{ round($attachment->file_size_bytes / 1024, 1) }} KB &bull; {{ strtoupper($attachment->file_type ?? 'ARQUIVO') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <a
                                                    href="{{ asset('storage/' . $attachment->file_path) }}"
                                                    target="_blank"
                                                    download
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 hover:bg-gray-700 text-white transition border border-white/5 flex items-center gap-1.5"
                                                >
                                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Baixar
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        {{-- Conteúdo da Aba 3: Sobre a Aula --}}
                        @if ($activeTab === 'overview')
                            <div class="p-6 space-y-3">
                                <h4 class="text-sm font-bold text-white">Objetivo Pedagógico</h4>
                                <p class="text-sm text-gray-300 leading-relaxed">
                                    {{ $course->description ?? 'Capacitação corporativa focada em retenção e aplicabilidade prática.' }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Coluna 2: Grade Curricular Lateral (4 colunas) --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-white flex items-center justify-between">
                        <span>Conteúdo do Curso</span>
                        <span class="text-xs font-normal text-gray-400">
                            {{ count($completedLessonIds) }} / {{ $course->lessons->count() }} aulas
                        </span>
                    </h3>

                    {{-- Lista de Módulos e Aulas --}}
                    <div class="mt-4 space-y-4">
                        @foreach ($course->modules->sortBy('order') as $module)
                            <div class="space-y-2">
                                <div class="px-2 py-1 text-xs font-semibold text-gray-400 bg-gray-950/40 rounded-lg flex items-center justify-between">
                                    <span>{{ $module->title }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $module->lessons->count() }} aulas</span>
                                </div>

                                <div class="space-y-1">
                                    @foreach ($module->lessons->sortBy('order') as $lesson)
                                        @php
                                            $isCurrent = $currentLesson && $currentLesson->id === $lesson->id;
                                            $isDone = in_array($lesson->id, $completedLessonIds);
                                        @endphp

                                        <button
                                            type="button"
                                            wire:click="selectLesson({{ $lesson->id }})"
                                            class="w-full text-left p-3 rounded-xl transition flex items-center justify-between gap-3 text-xs
                                            {{ $isCurrent
                                                ? 'bg-indigo-600/20 border border-indigo-500/40 text-white font-semibold'
                                                : 'hover:bg-gray-800/60 text-gray-300 border border-transparent' }}"
                                        >
                                            <div class="flex items-center gap-2.5 line-clamp-1">
                                                @if ($isDone)
                                                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 text-[10px]">
                                                        ✓
                                                    </span>
                                                @elseif ($isCurrent)
                                                    <span class="w-4 h-4 rounded-full bg-indigo-500 text-white flex items-center justify-center flex-shrink-0 text-[9px] animate-pulse">
                                                        ▶
                                                    </span>
                                                @else
                                                    <span class="w-4 h-4 rounded-full border border-gray-600 flex items-center justify-center flex-shrink-0"></span>
                                                @endif

                                                <span class="truncate">{{ $lesson->title }}</span>
                                            </div>

                                            @if ($lesson->duration_minutes)
                                                <span class="text-[11px] text-gray-500 flex-shrink-0">
                                                    {{ $lesson->duration_minutes }}m
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
