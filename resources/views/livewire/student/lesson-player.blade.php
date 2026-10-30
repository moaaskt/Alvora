<div class="w-full"
     x-data="{
        currentLessonPercent: 0,
        isCompleted: {{ $isCompleted ? 'true' : 'false' }},
        lastReportedPercent: 0,
        ytPlayer: null,
        ytTicker: null,
        vimeoPlayer: null,
        ytDuration: {{ (int) (($currentLesson->duration_minutes ?? 10) * 60) }},
        isPlaying: false,

        init() {
            window._alvoraPlayer = this;

            window.initYT = () => {
                if (!window._alvoraPlayer) return;
                const container = document.getElementById('alvora-yt-iframe');
                if (!container) return;
                if (window._alvoraPlayer.ytPlayer) {
                    try { window._alvoraPlayer.ytPlayer.destroy(); } catch(e) {}
                    window._alvoraPlayer.ytPlayer = null;
                }
                try {
                    const player = new window.YT.Player('alvora-yt-iframe', {
                        videoId: container.dataset.videoId,
                        playerVars: {
                            rel: 0,
                            modestbranding: 1,
                            origin: window.location.origin,
                        },
                        events: {
                            onReady: (event) => {
                                console.log('[Alvora] onReady!');
                                const d = event.target.getDuration();
                                const vm = window._alvoraPlayer;
                                if (!vm) return;
                                // Sempre substitui — garante que é o player atual
                                vm.ytPlayer = player;
                                if (d > 0) vm.ytDuration = d;
                                console.log('[Alvora] player salvo, duration:', d);
                            },
                            onStateChange: (event) => {
                                console.log('[Alvora] state:', event.data);
                                const vm = window._alvoraPlayer;
                                if (!vm) return;
                                if (event.data === 1) {
                                    vm.isPlaying = true;
                                    vm.startYTTicker();
                                } else if (event.data === 2) {
                                    vm.isPlaying = false;
                                    vm.stopYTTicker();
                                } else if (event.data === 0) {
                                    vm.isPlaying = false;
                                    vm.stopYTTicker();
                                    vm.currentLessonPercent = 100;
                                    if (!vm.isCompleted) {
                                        vm.$wire.toggleComplete();
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.warn('[Alvora YT] Erro initYT:', e);
                }
            };

            const prevYTReady = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = () => {
                if (typeof prevYTReady === 'function') prevYTReady();
                window.initYT();
            };

            if (window.YT && window.YT.Player) {
                window.initYT();
            }

            this.$nextTick(() => {
                this.initPlayer();
                if (window.YT && window.YT.Player) {
                    window.initYT();
                }
            });
        },

        initPlayer() {
            this.cleanup();
            this.currentLessonPercent = 0;
            this.lastReportedPercent = 0;

            const vimeoIframe = document.getElementById('alvora-vimeo-iframe');
            if (vimeoIframe) {
                this.setupVimeo(vimeoIframe);
                return;
            }
        },

        startYTTicker() {
            this.stopYTTicker();
            this.ytTicker = setInterval(() => {
                const player = this.ytPlayer || (window._alvoraPlayer && window._alvoraPlayer.ytPlayer);
                if (player && typeof player.getCurrentTime === 'function') {
                    try {
                        const current = player.getCurrentTime() || 0;
                        const duration = player.getDuration() || this.ytDuration || 1;
                        if (duration > 0) {
                            this.calcAndApplyProgress(current, duration);
                        }
                    } catch (e) {}
                }
            }, 250);
        },

        stopYTTicker() {
            if (this.ytTicker) {
                clearInterval(this.ytTicker);
                this.ytTicker = null;
            }
        },

        calcAndApplyProgress(current, duration) {
            const pct = Math.min(100, Math.round((current / duration) * 100));
            this.currentLessonPercent = pct;

            if (pct >= this.lastReportedPercent + 5 || (pct >= 90 && this.lastReportedPercent < 90)) {
                this.lastReportedPercent = pct;
                $wire.updateLessonProgress(Math.round(current), pct);
            }
        },

        setupVimeo(iframe) {
            if (!window.Vimeo) {
                const script = document.createElement('script');
                script.src = 'https://player.vimeo.com/api/player.js';
                script.onload = () => this.createVimeoPlayer(iframe);
                document.head.appendChild(script);
            } else {
                this.createVimeoPlayer(iframe);
            }
        },

        createVimeoPlayer(iframe) {
            try {
                this.vimeoPlayer = new Vimeo.Player(iframe);
                this.vimeoPlayer.on('timeupdate', (data) => {
                    const pct = Math.min(100, Math.round(data.percent * 100));
                    this.currentLessonPercent = pct;
                    if (pct >= this.lastReportedPercent + 5 || (pct >= 90 && this.lastReportedPercent < 90)) {
                        this.lastReportedPercent = pct;
                        $wire.updateLessonProgress(Math.round(data.seconds), pct);
                    }
                });
                this.vimeoPlayer.on('ended', () => {
                    this.currentLessonPercent = 100;
                    if (!this.isCompleted) {
                        $wire.toggleComplete();
                    }
                });
            } catch (e) {}
        },

        onTimeUpdate(e) {
            const video = e.target;
            if (video && video.duration > 0) {
                const pct = Math.min(100, Math.round((video.currentTime / video.duration) * 100));
                this.currentLessonPercent = pct;
                if (pct >= this.lastReportedPercent + 5 || (pct >= 90 && this.lastReportedPercent < 90)) {
                    this.lastReportedPercent = pct;
                    $wire.updateLessonProgress(Math.round(video.currentTime), pct);
                }
            }
        },

        onVideoEnded() {
            this.currentLessonPercent = 100;
            if (!this.isCompleted) {
                $wire.toggleComplete();
            }
        },

        cleanup() {
            this.stopYTTicker();
            if (this.ytPlayer) {
                try {
                    if (typeof this.ytPlayer.destroy === 'function') {
                        this.ytPlayer.destroy();
                    }
                } catch (e) {}
                this.ytPlayer = null;
            }
            if (this.vimeoPlayer) {
                try {
                    if (typeof this.vimeoPlayer.destroy === 'function') {
                        this.vimeoPlayer.destroy();
                    }
                } catch (e) {}
                this.vimeoPlayer = null;
            }
        }
     }">
    {{-- 1. COURSE TOP BAR (White card, rounded-2xl, non-sticky to prevent header collision) --}}
    <div class="bg-edtech-surface rounded-2xl p-4 sm:p-5 border border-edtech-border shadow-card flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        {{-- Left: Back to Dashboard Button & Course Metadata --}}
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('student.dashboard') }}"
               class="inline-flex items-center gap-1.5 h-10 px-3.5 rounded-xl border border-edtech-border bg-edtech-surface hover:bg-edtech-bg text-edtech-text text-sm font-medium transition-colors shrink-0 shadow-xs"
               title="Voltar ao Painel do Aluno">
                <x-icon name="arrow-left" class="w-4 h-4 text-edtech-muted" />
                <span>Painel</span>
            </a>

            <div class="flex flex-col min-w-0">
                <h1 class="font-semibold text-lg text-edtech-text truncate tracking-tight">
                    {{ $course->title }}
                </h1>
                <p class="text-[13px] text-edtech-muted truncate mt-0.5">
                    @if ($currentLesson)
                        {{ $currentLesson->module->title ?? 'Módulo' }} • {{ $currentLesson->title }}
                    @else
                        Nenhuma aula selecionada
                    @endif
                </p>
            </div>
        </div>

        {{-- Right: Course Progress & Pending Quiz / Certificate Action --}}
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 shrink-0 justify-between lg:justify-end">
            {{-- Progress indicator with 8px bar --}}
            <div class="flex flex-col gap-1.5 w-40 sm:w-44">
                <div class="flex items-center justify-between text-xs leading-none">
                    <span class="text-edtech-muted font-medium">Progresso</span>
                    <span class="font-semibold tabular-nums {{ $progressPercent >= 100 ? 'text-alvora-emerald' : 'text-edtech-blue' }}">
                        {{ $progressPercent }}%
                    </span>
                </div>
                <div class="w-full h-2 rounded-full bg-edtech-border overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $progressPercent >= 100 ? 'bg-alvora-emerald' : 'bg-gradient-to-r from-edtech-blue to-edtech-blue-dark' }}"
                         style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            {{-- 100% Completion Actions: Certificate Download or Pending Quizzes Alert --}}
            @if ($progressPercent >= 100)
                @if ($this->isEligibleForCertificate())
                    <a href="{{ route('student.certificate', $course->slug) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white text-sm font-semibold transition-colors shadow-xs">
                        <x-icon name="download" class="w-4 h-4 text-white" />
                        <span>Baixar Certificado</span>
                    </a>
                @else
                    <div class="flex items-center gap-2 text-xs font-medium text-amber-800 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                        <x-icon name="alert-triangle" class="w-4 h-4 text-edtech-amber shrink-0" />
                        <span>Quizzes pendentes:</span>
                        @if ($pendingQuiz)
                            <a href="{{ route('student.quiz', $pendingQuiz) }}"
                               class="underline font-semibold hover:text-amber-900 text-amber-700">
                                Fazer quiz
                            </a>
                        @else
                            <span class="font-semibold text-amber-700">Aguardando avaliação</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- 2. FLASH NOTIFICATIONS (Dismissible) --}}
    <div x-data x-init="
        @if(session('success')) AlvoraSwal.fire({ icon: 'success', title: 'Pronto!', text: '{{ session('success') }}', toast: true, position: 'top-end', timer: 2500, timerProgressBar: true, showConfirmButton: false }); @endif
        @if(session('error')) AlvoraSwal.fire({ icon: 'error', title: 'Erro', text: '{{ session('error') }}', confirmButtonText: 'Fechar' }); @endif
        @if(session('warning')) AlvoraSwal.fire({ icon: 'warning', title: 'Atenção', text: '{{ session('warning') }}', confirmButtonText: 'Entendi' }); @endif
    "></div>

    {{-- 3. TWO-COLUMN WORKSPACE (Left: Player & Tabs, Right: Curriculum Sidebar) --}}
    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
        {{-- LEFT COLUMN: Media Player, Actions & Tabs --}}
        <div class="flex-1 w-full min-w-0">
            {{-- Video / Media Area (16:9, rounded-2xl, native controls only) --}}
            <div wire:key="media-player-container-{{ $currentLesson->id ?? 0 }}"
                 x-init="$nextTick(() => { initPlayer(); if (window.initYT && window.YT && window.YT.Player) window.initYT(); })"
                 class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-card flex items-center justify-center border border-edtech-border/60">
                @if ($currentLesson && $currentLesson->video_url)
                    @php
                        $url = trim($currentLesson->video_url);
                        $isYoutube = str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
                        $isVimeo = str_contains($url, 'vimeo.com');
                        $youtubeId = null;
                        $vimeoId = null;

                        if ($isYoutube) {
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match);
                            $youtubeId = $match[1] ?? null;
                        } elseif ($isVimeo) {
                            preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $match);
                            $vimeoId = $match[3] ?? null;
                        }
                    @endphp

                    @if ($isYoutube)
                        @if ($youtubeId)
                            <div id="alvora-yt-iframe"
                                 class="w-full h-full"
                                 data-video-id="{{ $youtubeId }}"></div>
                        @else
                            <div class="text-center p-8 space-y-2 text-slate-300">
                                <x-icon name="alert-triangle" class="w-8 h-8 text-edtech-amber mx-auto" />
                                <p class="text-sm font-medium">Não foi possível carregar o vídeo desta aula.</p>
                            </div>
                        @endif
                    @elseif ($isVimeo)
                        @if ($vimeoId)
                            <iframe id="alvora-vimeo-iframe"
                                    class="w-full h-full"
                                    src="https://player.vimeo.com/video/{{ $vimeoId }}?title=0&byline=0&portrait=0"
                                    frameborder="0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    allowfullscreen></iframe>
                        @else
                            <div class="text-center p-8 space-y-2 text-slate-300">
                                <x-icon name="alert-triangle" class="w-8 h-8 text-edtech-amber mx-auto" />
                                <p class="text-sm font-medium">Não foi possível carregar o vídeo desta aula.</p>
                            </div>
                        @endif
                    @else
                        {{-- Direct HTML5 Video File (MP4, WebM) --}}
                        <video class="w-full h-full object-contain"
                               controls
                               controlsList="nodownload"
                               src="{{ $currentLesson->video_url }}"
                               @timeupdate="onTimeUpdate($event)"
                               @ended="onVideoEnded()">
                            Seu navegador não suporta a tag de vídeo.
                        </video>
                    @endif
                @elseif ($currentLesson)
                    {{-- Textual / Reading Content Fallback --}}
                    <div class="text-center p-8 space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                            <x-icon name="book-open" class="w-7 h-7 text-edtech-blue" />
                        </div>
                        <h3 class="text-lg font-semibold text-white">Conteúdo Textual e Leitura</h3>
                        <p class="text-sm text-slate-300 max-w-md mx-auto">
                            Esta aula possui conteúdo teórico ou materiais complementares para download disponíveis nas abas abaixo.
                        </p>
                    </div>
                @else
                    {{-- Course Without Lessons Fallback --}}
                    <div class="text-center p-8 space-y-2 text-slate-400">
                        <p class="text-sm">Nenhuma aula cadastrada neste curso até o momento.</p>
                    </div>
                @endif
            </div>

            {{-- Lesson Header & Actions Card --}}
            @if ($currentLesson)
                <div class="bg-edtech-surface rounded-2xl p-5 sm:p-6 mt-6 border border-edtech-border shadow-card flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div class="min-w-0">
                        <span class="text-edtech-muted font-medium text-[13px] block leading-none">
                            {{ $currentLesson->module->title ?? 'Módulo do Curso' }}
                        </span>
                        <h2 class="font-semibold text-xl sm:text-[22px] text-edtech-text mt-2 mb-2 tracking-tight">
                            {{ $currentLesson->title }}
                        </h2>
                        <div class="flex items-center gap-3 text-edtech-muted text-xs">
                            @if ($currentLesson->duration_minutes)
                                <div class="flex items-center gap-1.5">
                                    <x-icon name="clock" class="w-4 h-4 text-edtech-muted" />
                                    <span>{{ $currentLesson->duration_minutes }} min estimados</span>
                                </div>
                                <span class="text-edtech-border">•</span>
                            @endif
                            <div class="flex items-center gap-1.5">
                                <x-icon name="paperclip" class="w-4 h-4 text-edtech-muted" />
                                @php $attCount = $currentLesson->attachments->count(); @endphp
                                <span>{{ $attCount }} {{ $attCount === 1 ? 'anexo' : 'anexos' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Area --}}
                    <div class="flex items-center gap-3 shrink-0">
                        {{-- Complete Toggle Button (44px) --}}
                        @if ($isCompleted)
                            <button type="button"
                                    wire:click="toggleComplete"
                                    class="h-11 px-5 rounded-xl border border-alvora-emerald bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold text-sm flex items-center gap-2 transition-colors cursor-pointer shadow-xs">
                                <x-icon name="check-circle" class="w-4 h-4 text-alvora-emerald" />
                                <span>Concluída</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="toggleComplete"
                                    class="h-11 px-5 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm flex items-center gap-2 transition-colors cursor-pointer shadow-xs">
                                <x-icon name="check-circle" class="w-4 h-4 text-white" />
                                <span>Marcar como Concluída</span>
                            </button>
                        @endif

                        {{-- Previous / Next Icon Buttons (44px square) --}}
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    wire:click="previousLesson"
                                    @disabled(!$hasPrevious)
                                    aria-label="Aula Anterior"
                                    title="Aula Anterior"
                                    class="w-11 h-11 rounded-xl bg-edtech-surface border border-edtech-border hover:bg-edtech-border-light text-edtech-text disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-colors cursor-pointer shadow-xs">
                                <x-icon name="chevron-left" class="w-4 h-4" />
                            </button>

                            <button type="button"
                                    wire:click="nextLesson"
                                    @disabled(!$hasNext)
                                    aria-label="Próxima Aula"
                                    title="Próxima Aula"
                                    class="w-11 h-11 rounded-xl bg-edtech-surface border border-edtech-border hover:bg-edtech-border-light text-edtech-text disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-colors cursor-pointer shadow-xs">
                                <x-icon name="chevron-right" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabs Card --}}
                <div class="bg-edtech-surface rounded-2xl mt-6 border border-edtech-border shadow-card overflow-hidden">
                    {{-- Tab Headers --}}
                    <div class="px-6 pt-3 flex gap-8 border-b border-edtech-border bg-edtech-surface">
                        <button type="button"
                                wire:click="setTab('notes')"
                                class="pb-3.5 pt-1 font-semibold text-sm flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'notes' ? 'border-edtech-blue text-edtech-blue' : 'border-transparent text-edtech-muted hover:text-edtech-text' }}">
                            <x-icon name="pencil" class="w-4 h-4" />
                            <span>Minhas Anotações</span>
                        </button>

                        <button type="button"
                                wire:click="setTab('materials')"
                                class="pb-3.5 pt-1 font-semibold text-sm flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'materials' ? 'border-edtech-blue text-edtech-blue' : 'border-transparent text-edtech-muted hover:text-edtech-text' }}">
                            <x-icon name="file-text" class="w-4 h-4" />
                            <span>Materiais de Apoio ({{ $currentLesson->attachments->count() }})</span>
                        </button>

                        <button type="button"
                                wire:click="setTab('overview')"
                                class="pb-3.5 pt-1 font-semibold text-sm flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'overview' ? 'border-edtech-blue text-edtech-blue' : 'border-transparent text-edtech-muted hover:text-edtech-text' }}">
                            <x-icon name="info" class="w-4 h-4" />
                            <span>Sobre a Aula</span>
                        </button>
                    </div>

                    {{-- Tab Content --}}
                    <div class="p-6">
                        {{-- TAB 1: Minhas Anotações --}}
                        @if ($activeTab === 'notes')
                            <div x-data="{ saved: false, timer: null }"
                                 x-on:note-saved.window="saved = true; clearTimeout(timer); timer = setTimeout(() => { saved = false }, 2000);"
                                 class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 text-edtech-muted text-[13px]">
                                        <x-icon name="lock" class="w-3.5 h-3.5 text-edtech-muted" />
                                        <span>Suas anotações são particulares e salvas automaticamente</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        {{-- Server-confirmed saved indicator --}}
                                        <span x-show="saved"
                                              x-cloak
                                              x-transition.opacity.duration.200ms
                                              class="flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                            <x-icon name="check-circle" class="w-3.5 h-3.5 text-alvora-emerald" />
                                            <span>Salvo</span>
                                        </span>

                                        <button type="button"
                                                wire:click="saveNote"
                                                class="text-xs font-semibold text-edtech-blue border border-edtech-border hover:bg-edtech-border-light px-3 py-1.5 rounded-lg transition-colors cursor-pointer shadow-xs">
                                            Salvar Agora
                                        </button>
                                    </div>
                                </div>

                                <textarea wire:model.live.debounce.1000ms="noteContent"
                                          wire:change="saveNote"
                                          rows="6"
                                          placeholder="Escreva suas considerações, insights ou tópicos importantes desta aula..."
                                          class="w-full rounded-xl bg-edtech-bg border border-edtech-border focus:bg-edtech-surface focus:border-edtech-blue p-4 text-sm text-edtech-text placeholder:text-edtech-muted focus:outline-none focus:ring-2 focus:ring-edtech-blue/15 transition-all resize-y min-h-[160px] leading-relaxed"></textarea>
                            </div>
                        @endif

                        {{-- TAB 2: Materiais de Apoio --}}
                        @if ($activeTab === 'materials')
                            <div>
                                @if ($currentLesson->attachments->isEmpty())
                                    <p class="text-sm text-edtech-muted text-center py-4">
                                        Nenhum material complementar anexado a esta aula.
                                    </p>
                                @else
                                    <ul class="divide-y divide-edtech-border">
                                        @foreach ($currentLesson->attachments as $attachment)
                                            <li class="py-3 flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="p-2 rounded-lg bg-edtech-blue-light text-edtech-blue shrink-0">
                                                        <x-icon name="file-text" class="w-4 h-4" />
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-semibold text-edtech-text truncate">{{ $attachment->title }}</p>
                                                        <p class="text-xs text-edtech-muted">
                                                            {{ round($attachment->file_size_bytes / 1024, 1) }} KB • {{ strtoupper($attachment->file_type ?? 'ARQUIVO') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                   target="_blank"
                                                   download
                                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-edtech-border bg-edtech-surface hover:bg-edtech-bg text-edtech-text transition-colors flex items-center gap-1.5 shrink-0 shadow-xs">
                                                    <x-icon name="download" class="w-3.5 h-3.5 text-edtech-blue" />
                                                    <span>Baixar</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        {{-- TAB 3: Sobre a Aula --}}
                        @if ($activeTab === 'overview')
                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-edtech-text">Objetivo Pedagógico</h4>
                                <p class="text-sm text-edtech-muted leading-relaxed">
                                    {{ $currentLesson->description ?: ($course->description ?: 'Capacitação corporativa focada em retenção e aplicabilidade prática.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: Sticky Curriculum Sidebar (360px on desktop) --}}
        <aside class="w-full lg:w-[360px] shrink-0 lg:sticky lg:top-28 scroll-mt-28">
            <div class="bg-edtech-surface rounded-2xl p-5 border border-edtech-border shadow-card">
                {{-- Sidebar Header with Total Lessons Count --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-edtech-border">
                    <h3 class="font-semibold text-base text-edtech-text">Conteúdo do Curso</h3>
                    @php $totalCourseLessons = $course->lessons->count(); @endphp
                    <span class="font-medium font-mono text-xs text-edtech-muted tabular-nums">
                        {{ count($completedLessonIds) }} / {{ $totalCourseLessons }} {{ $totalCourseLessons === 1 ? 'aula' : 'aulas' }}
                    </span>
                </div>

                {{-- Modules List --}}
                <div class="mt-4 space-y-4">
                    @foreach ($course->modules->sortBy('order') as $module)
                        @php
                            $totalMod = $module->lessons->count();
                            $doneMod = $module->lessons->whereIn('id', $completedLessonIds)->count();
                            $modPercent = $totalMod > 0 ? (int) round(($doneMod / $totalMod) * 100) : 0;
                        @endphp

                        <div class="{{ !$loop->first ? 'border-t border-edtech-border pt-4' : '' }}">
                            {{-- Module Header & Progress Bar --}}
                            <div class="flex flex-col gap-1.5 mb-2.5">
                                <h4 class="text-sm font-semibold text-edtech-text leading-snug">
                                    {{ $module->title }}
                                </h4>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-1 bg-edtech-border rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $modPercent === 100 ? 'bg-alvora-emerald' : 'bg-gradient-to-r from-edtech-blue to-edtech-blue-dark' }}"
                                             style="width: {{ $modPercent }}%"></div>
                                    </div>
                                    <span class="font-mono font-medium text-xs text-edtech-muted shrink-0 tabular-nums">
                                        {{ $doneMod }}/{{ $totalMod }}
                                    </span>
                                </div>
                            </div>

                            {{-- Lessons List with Vertical Guide Line --}}
                            <div class="ml-3 pl-3 border-l border-edtech-border flex flex-col gap-1.5">
                                @foreach ($module->lessons->sortBy('order') as $lesson)
                                    @php
                                        $isCurrent = $currentLesson && $currentLesson->id === $lesson->id;
                                        $isDone = in_array($lesson->id, $completedLessonIds);
                                    @endphp

                                    <button type="button"
                                            wire:click="selectLesson({{ $lesson->id }})"
                                            class="w-full min-h-[44px] flex items-start gap-2.5 py-2.5 px-2 rounded-lg text-left transition-colors cursor-pointer group {{ $isCurrent ? 'bg-edtech-blue-light border-l-[3px] border-l-edtech-blue text-edtech-text font-semibold' : 'hover:bg-edtech-bg text-edtech-text' }}">
                                        {{-- Lesson Status Indicator --}}
                                        @if ($isDone)
                                            <x-icon name="check-circle" class="w-5 h-5 text-alvora-emerald mt-0.5 shrink-0" />
                                        @elseif ($isCurrent)
                                            <x-progress-ring :percent="$currentLessonPercent" alpine-model="currentLessonPercent" class="mt-0.5 shrink-0" />
                                        @else
                                            <svg class="w-5 h-5 mt-0.5 shrink-0 stroke-edtech-border fill-none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9.5" stroke-width="1.5"></circle>
                                            </svg>
                                        @endif

                                        {{-- Lesson Title --}}
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[13px] leading-tight block {{ $isCurrent ? 'font-semibold text-edtech-text' : ($isDone ? 'font-normal text-edtech-muted group-hover:text-edtech-text' : 'font-normal text-edtech-text') }}">
                                                {{ $lesson->title }}
                                            </span>
                                        </div>

                                        {{-- Duration --}}
                                        @if ($lesson->duration_minutes)
                                            <span class="text-edtech-muted text-xs shrink-0 pt-0.5 font-medium ml-2 min-w-[28px] text-right">
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
        </aside>
    </div>
</div>
