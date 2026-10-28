<div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
    <!-- LEFT STICKY SIDEBAR (280px on desktop, compact stacked card below lg) -->
    <aside class="w-full lg:w-[280px] shrink-0 lg:sticky lg:top-28">
        <div class="bg-edtech-surface border-2 border-edtech-blue rounded-[20px] p-5 lg:p-6 shadow-sidebar flex flex-col">
            <!-- Student Profile Info -->
            <div class="flex flex-row lg:flex-col items-center lg:text-center gap-4 lg:gap-0 pb-4 lg:pb-6 border-b border-edtech-border">
                <div class="w-16 h-16 lg:w-[112px] lg:h-[112px] rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-2xl lg:text-[36px] lg:mb-4 shadow-[0_0_0_4px_#FFFFFF] shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'AL', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="font-semibold text-edtech-text text-[18px] lg:text-[20px] leading-tight truncate">
                        {{ auth()->user()->name }}
                    </h2>
                    @if(auth()->user()->company)
                        <p class="text-[14px] text-edtech-muted mt-1 truncate">
                            {{ auth()->user()->company->name }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav aria-label="Menu do Aluno" class="mt-3 lg:mt-4 flex flex-col">
                <!-- Item 1: Meu Aprendizado (Active) -->
                <a href="#meu-aprendizado"
                   class="flex items-center gap-3 px-3.5 h-[48px] rounded-r-xl text-[15px] font-semibold text-edtech-blue bg-edtech-blue-light border-l-[3px] border-edtech-blue transition-colors">
                    <x-icon name="book-open" class="w-5 h-5 text-edtech-blue shrink-0" />
                    <span>Meu Aprendizado</span>
                </a>

                @if ($trackEnrollments->isNotEmpty())
                    <div class="h-px bg-edtech-border my-1"></div>
                    <!-- Item 2: Minhas Trilhas (only when student has tracks) -->
                    <a href="#minhas-trilhas"
                       class="flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] font-medium text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light transition-colors">
                        <x-icon name="map" class="w-5 h-5 text-edtech-muted shrink-0" />
                        <span>Minhas Trilhas</span>
                    </a>
                @endif

                <div class="h-px bg-edtech-border my-1"></div>
                <!-- Item 3: Catálogo de Cursos -->
                <a href="#catalogo"
                   class="flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] font-medium text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light transition-colors">
                    <x-icon name="layout-grid" class="w-5 h-5 text-edtech-muted shrink-0" />
                    <span>Catálogo de Cursos</span>
                </a>

                <div class="h-px bg-edtech-border my-1"></div>
                <!-- Item 4: Sair (POST Form with @csrf) -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                            title="Sair da Plataforma"
                            class="w-full flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] font-medium text-edtech-muted hover:text-rose-600 hover:bg-rose-50/60 transition-colors cursor-pointer text-left">
                        <x-icon name="log-out" class="w-5 h-5 text-edtech-muted shrink-0" />
                        <span>Sair</span>
                    </button>
                </form>
            </nav>
        </div>
    </aside>

    <!-- RIGHT MAIN CONTENT COLUMN -->
    <section id="meu-aprendizado" class="flex-1 min-w-0 w-full flex flex-col gap-10 scroll-mt-28">
        <!-- 1. GREETING HEADING (Strictly without emoji or pill badge) -->
        <div>
            <h1 class="font-bold text-[28px] sm:text-[32px] text-edtech-text tracking-tight leading-tight">
                Olá, {{ auth()->user()->name }}
            </h1>
            <p class="text-[15px] text-edtech-navy-soft font-medium mt-2 max-w-3xl leading-relaxed">
                Acompanhe seus cursos corporativos, continue suas aulas onde parou e aprimore suas competências profissionais.
            </p>
        </div>

        <!-- FLASH MESSAGES (Dismissible) -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle" class="w-5 h-5 text-alvora-emerald shrink-0" />
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" aria-label="Fechar mensagem" class="p-1 text-emerald-700 hover:text-emerald-950 rounded-lg transition-colors cursor-pointer">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div class="p-4 rounded-2xl bg-edtech-amber-light/60 border border-edtech-amber/40 text-amber-900 text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <x-icon name="alert-triangle" class="w-5 h-5 text-edtech-amber-dark shrink-0" />
                    <span class="font-medium">{{ session('warning') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" aria-label="Fechar aviso" class="p-1 text-amber-800 hover:text-amber-950 rounded-lg transition-colors cursor-pointer">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        <!-- 2. FOUR COMPACT STAT CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stat 1: Total Matriculado -->
            <div class="bg-edtech-surface border border-edtech-border rounded-[16px] p-5 shadow-card flex flex-col">
                <div class="mb-3">
                    <x-icon name="book-open" class="w-[22px] h-[22px] text-edtech-blue" />
                </div>
                <span class="text-[14px] font-medium text-edtech-muted block">Total Matriculado</span>
                <span class="font-bold text-[36px] text-edtech-text tabular-nums block mt-1 leading-none">{{ $totalEnrolled }}</span>
                <span class="text-[13px] text-edtech-muted block mt-2">Cursos no seu plano</span>
            </div>

            <!-- Stat 2: Em Andamento -->
            <div class="bg-edtech-surface border border-edtech-border rounded-[16px] p-5 shadow-card flex flex-col">
                <div class="mb-3">
                    <x-icon name="clock" class="w-[22px] h-[22px] text-edtech-blue" />
                </div>
                <span class="text-[14px] font-medium text-edtech-muted block">Em Andamento</span>
                <span class="font-bold text-[36px] text-edtech-text tabular-nums block mt-1 leading-none">{{ $inProgressCount }}</span>
                <span class="text-[13px] text-edtech-muted block mt-2">Cursos a concluir</span>
            </div>

            <!-- Stat 3: Concluídos -->
            <div class="bg-edtech-surface border border-edtech-border rounded-[16px] p-5 shadow-card flex flex-col">
                <div class="mb-3">
                    <x-icon name="check-circle" class="w-[22px] h-[22px] text-edtech-blue" />
                </div>
                <span class="text-[14px] font-medium text-edtech-muted block">Concluídos</span>
                <span class="font-bold text-[36px] text-edtech-text tabular-nums block mt-1 leading-none">{{ $completedCount }}</span>
                <span class="text-[13px] text-edtech-muted block mt-2">Cursos finalizados</span>
            </div>

            <!-- Stat 4: Progresso Geral -->
            <div class="bg-edtech-surface border border-edtech-border rounded-[16px] p-5 shadow-card flex flex-col">
                <div class="mb-3">
                    <x-icon name="bar-chart" class="w-[22px] h-[22px] text-edtech-blue" />
                </div>
                <span class="text-[14px] font-medium text-edtech-muted block">Progresso Geral</span>
                <span class="font-bold text-[36px] text-edtech-blue tabular-nums block mt-1 leading-none">{{ $averageProgress }}%</span>
                <span class="text-[13px] text-edtech-muted block mt-2">Média de conclusão</span>
            </div>
        </div>

        <!-- 3. MINHAS TRILHAS CORPORATIVAS (Only rendered if student has tracks; no empty state) -->
        @if ($trackEnrollments->isNotEmpty())
            <div id="minhas-trilhas" class="flex flex-col gap-4 scroll-mt-28">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-xl text-edtech-text">Minhas Trilhas Corporativas</h2>
                    <span class="text-sm text-edtech-muted font-medium">{{ $trackEnrollments->count() }} trilha(s) atribuída(s)</span>
                </div>

                <div class="grid grid-cols-1 {{ $trackEnrollments->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-6">
                    @foreach ($trackEnrollments as $trackEnrollment)
                        @php $track = $trackEnrollment->track; @endphp
                        <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-6 shadow-card flex flex-col gap-5">
                            <!-- Track Header Metadata (plain colored text, no pill badges) -->
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[13px] text-edtech-muted font-medium">
                                        {{ $track->department }}
                                    </span>

                                    @if ($track->is_mandatory)
                                        <span class="text-[13px] font-semibold text-edtech-amber flex items-center gap-1">
                                            <x-icon name="alert-triangle" class="w-3.5 h-3.5 text-edtech-amber shrink-0" />
                                            Obrigatória
                                        </span>
                                    @else
                                        <span class="text-[13px] font-medium text-edtech-muted">
                                            Opcional
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-lg text-edtech-text">{{ $track->title }}</h3>
                                @if ($track->description)
                                    <p class="text-sm text-edtech-muted mt-1 line-clamp-2 leading-relaxed">
                                        {{ $track->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- Consolidated Progress Bar (10px tall with gradient) -->
                            <div>
                                <div class="flex items-center justify-between text-[13px] mb-1.5">
                                    <span class="text-edtech-muted font-medium">Progresso Consolidado da Trilha</span>
                                    <span class="font-semibold tabular-nums {{ $trackEnrollment->progress_percent >= 100 ? 'text-alvora-emerald' : 'text-edtech-text' }}">
                                        {{ $trackEnrollment->progress_percent }}%
                                    </span>
                                </div>
                                <div class="w-full h-[10px] bg-edtech-border rounded-full overflow-hidden">
                                    @if ($trackEnrollment->progress_percent >= 100)
                                        <div class="h-full bg-alvora-emerald rounded-full transition-all duration-500" style="width: 100%;"></div>
                                    @else
                                        <div class="h-full bg-gradient-to-r from-edtech-blue to-edtech-blue-dark rounded-full transition-all duration-500" style="width: {{ $trackEnrollment->progress_percent }}%;"></div>
                                    @endif
                                </div>
                            </div>

                            <!-- Courses of the track -->
                            <div class="border-t border-edtech-border pt-4">
                                <span class="text-[14px] font-semibold text-edtech-text block mb-2">
                                    Cursos da trilha ({{ $track->courses->count() }})
                                </span>
                                <div class="flex flex-col divide-y divide-edtech-border">
                                    @foreach ($track->courses as $course)
                                        @php
                                            $courseEnrollment = $enrollments->firstWhere('course_id', $course->id);
                                            $coursePercent = $courseEnrollment?->progress_percent ?? 0;
                                            $isCompleted = $courseEnrollment && $courseEnrollment->status === 'completed';
                                        @endphp
                                        <div class="py-[14px] flex items-center justify-between gap-3 text-[15px]">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="text-[13px] font-medium text-edtech-muted w-5 shrink-0 tabular-nums">{{ $loop->iteration }}.</span>
                                                <span class="text-edtech-text font-medium truncate">{{ $course->title }}</span>
                                            </div>
                                            <div class="shrink-0">
                                                @if ($isCompleted)
                                                    <span class="text-[13px] font-semibold text-alvora-emerald flex items-center gap-1">
                                                        <x-icon name="check-circle" class="w-4 h-4 text-alvora-emerald shrink-0" />
                                                        Concluído
                                                    </span>
                                                @else
                                                    <span class="text-[13px] font-semibold text-edtech-muted tabular-nums">{{ $coursePercent }}%</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. CONTINUAR APRENDENDO -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-edtech-text">Continuar Aprendendo</h2>
                <span class="text-sm text-edtech-muted font-medium">{{ $enrollments->count() }} curso(s) matriculado(s)</span>
            </div>

            @if ($enrollments->isEmpty())
                <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-8 text-center space-y-3 shadow-card">
                    <div class="w-12 h-12 rounded-2xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                        <x-icon name="book-open" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-edtech-text">Você ainda não está matriculado em nenhum curso</h3>
                    <p class="text-sm text-edtech-muted max-w-md mx-auto">
                        Navegue pelo catálogo corporativo abaixo e inscreva-se para começar seu aprendizado.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($enrollments as $enrollment)
                        @php
                            $isCourseCompleted = $enrollment->status === 'completed' || $enrollment->progress_percent >= 100;
                        @endphp
                        <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-4 shadow-card hover:shadow-card-hover motion-safe:hover:-translate-y-1.5 transition-all duration-200 ease-out flex flex-col justify-between group">
                            <div>
                                <!-- Cover Image (16:10, 12px radius, zoom 1.05 on hover) -->
                                <div class="w-full aspect-[16/10] rounded-[12px] overflow-hidden bg-edtech-border-light mb-4 border border-edtech-border/60 relative">
                                    @if ($enrollment->course->thumbnail)
                                        <img src="{{ $enrollment->course->thumbnail }}"
                                             alt="{{ $enrollment->course->title }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover motion-safe:group-hover:scale-105 transition-transform duration-200 ease-out">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-edtech-navy to-edtech-blue-dark text-white motion-safe:group-hover:scale-105 transition-transform duration-200 ease-out">
                                            <x-icon name="book-open" class="w-10 h-10 text-white/85" />
                                        </div>
                                    @endif
                                </div>

                                <!-- Title (1 line truncated) -->
                                <h3 class="font-semibold text-[18px] text-edtech-text truncate" title="{{ $enrollment->course->title }}">
                                    {{ $enrollment->course->title }}
                                </h3>

                                <!-- Description (max 2 lines) -->
                                <p class="text-[14px] text-edtech-muted mt-1.5 line-clamp-2 leading-[1.6]">
                                    {{ $enrollment->course->description ?: 'Sem descrição informada.' }}
                                </p>
                            </div>

                            <div class="mt-6 flex flex-col gap-3">
                                <!-- Progress Block (8px tall bar) -->
                                <div>
                                    <div class="flex items-center justify-between text-[13px] mb-1">
                                        @if ($isCourseCompleted)
                                            <span class="font-semibold text-alvora-emerald flex items-center gap-1">
                                                <x-icon name="check-circle" class="w-3.5 h-3.5 text-alvora-emerald shrink-0" />
                                                Concluído
                                            </span>
                                            <span class="font-semibold text-alvora-emerald tabular-nums">100%</span>
                                        @else
                                            <span class="text-edtech-muted font-medium">Progresso</span>
                                            <span class="font-semibold text-edtech-text tabular-nums">{{ $enrollment->progress_percent }}%</span>
                                        @endif
                                    </div>
                                    <div class="w-full h-[8px] bg-edtech-border rounded-full overflow-hidden">
                                        @if ($isCourseCompleted)
                                            <div class="h-full bg-alvora-emerald rounded-full transition-all duration-300" style="width: 100%;"></div>
                                        @else
                                            <div class="h-full bg-gradient-to-r from-edtech-blue to-edtech-blue-dark rounded-full transition-all duration-300" style="width: {{ $enrollment->progress_percent }}%;"></div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button (44px tall) -->
                                @if ($enrollment->status === 'completed')
                                    <button type="button"
                                            wire:click="continueCourse({{ $enrollment->course->id }})"
                                            class="w-full h-[44px] px-4 rounded-xl border border-edtech-blue text-edtech-blue font-semibold text-sm hover:bg-edtech-blue-light transition-colors cursor-pointer">
                                        Rever Aulas
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="continueCourse({{ $enrollment->course->id }})"
                                            class="w-full h-[44px] px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors cursor-pointer">
                                        Continuar Aula
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. CURSOS DISPONÍVEIS DA SUA EMPRESA (CATÁLOGO) -->
        <div id="catalogo" class="flex flex-col gap-4 scroll-mt-28">
            <!-- Section Header with Search Input -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-edtech-text">Cursos Disponíveis da Sua Empresa</h2>
                    <p class="text-sm text-edtech-muted mt-0.5">Explore novos temas e desenvolva novas habilidades</p>
                </div>

                <!-- Search input on the right -->
                <div class="w-full md:w-72 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <x-icon name="search" class="w-4 h-4 text-edtech-muted" />
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar por curso..."
                           aria-label="Buscar por curso"
                           class="w-full pl-10 pr-4 py-2 bg-edtech-surface border border-edtech-border rounded-xl text-sm text-edtech-text placeholder-edtech-muted focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue transition-colors">
                </div>
            </div>

            <!-- Catalog Cards Grid or Separate Empty States -->
            @if ($availableCourses->isEmpty())
                <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-8 text-center text-edtech-muted text-sm shadow-card">
                    @if (trim($search) !== '')
                        Nenhum curso encontrado para a sua busca.
                    @else
                        Nenhum novo curso disponível encontrado no momento.
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($availableCourses as $course)
                        <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-4 shadow-card hover:shadow-card-hover motion-safe:hover:-translate-y-1.5 transition-all duration-200 ease-out flex flex-col justify-between group">
                            <div>
                                <!-- Cover Image (16:10, 12px radius, zoom 1.05 on hover) -->
                                <div class="w-full aspect-[16/10] rounded-[12px] overflow-hidden bg-edtech-border-light mb-4 border border-edtech-border/60 relative">
                                    @if ($course->thumbnail)
                                        <img src="{{ $course->thumbnail }}"
                                             alt="{{ $course->title }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover motion-safe:group-hover:scale-105 transition-transform duration-200 ease-out">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-edtech-navy to-edtech-blue-dark text-white motion-safe:group-hover:scale-105 transition-transform duration-200 ease-out">
                                            <x-icon name="book-open" class="w-10 h-10 text-white/85" />
                                        </div>
                                    @endif
                                </div>

                                <span class="text-[13px] text-edtech-muted font-medium block mb-1 tabular-nums">
                                    {{ $course->lessons_count }} aulas
                                </span>
                                <h3 class="font-semibold text-[18px] text-edtech-text truncate" title="{{ $course->title }}">
                                    {{ $course->title }}
                                </h3>
                                <p class="text-[14px] text-edtech-muted mt-1.5 line-clamp-2 leading-[1.6]">
                                    {{ $course->description ?: 'Sem descrição detalhada cadastrada para este curso.' }}
                                </p>
                            </div>

                            <div class="mt-6">
                                <button type="button"
                                        wire:click="enroll({{ $course->id }})"
                                        class="w-full h-[44px] px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs cursor-pointer">
                                    Matricular-se Gratuitamente
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
