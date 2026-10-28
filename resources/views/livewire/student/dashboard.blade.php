<div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
    <!-- LEFT SIDEBAR -->
    <x-student.sidebar active="dashboard" />

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

        <!-- 2. FOUR COMPACT STAT CARDS (Computed over ALL enrollments) -->
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

        <!-- 3. MINHAS TRILHAS CORPORATIVAS (Shown only if student has tracks, at most 2 compact cards) -->
        @if ($trackEnrollments->isNotEmpty())
            <div id="minhas-trilhas" class="flex flex-col gap-4 scroll-mt-28">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-xl text-edtech-text">Minhas Trilhas Corporativas</h2>
                    <a href="{{ route('student.tracks') }}"
                       class="inline-flex items-center gap-1 text-sm font-semibold text-edtech-blue hover:text-edtech-blue-hover transition-colors">
                        <span>Ver todas as trilhas</span>
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </a>
                </div>

                <div class="grid grid-cols-1 {{ $trackEnrollments->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-6">
                    @foreach ($trackEnrollments->take(2) as $trackEnrollment)
                        @php
                            $track = $trackEnrollment->track;
                            $publishedCourses = $track ? $track->publishedCourses : collect();
                            $totalTrackCourses = $publishedCourses->count();
                            $completedTrackCourses = $publishedCourses->filter(function ($c) use ($enrollments) {
                                $enr = $enrollments->firstWhere('course_id', $c->id);
                                return $enr && ($enr->status === 'completed' || $enr->progress_percent >= 100);
                            })->count();
                            $isCompleted = ($trackEnrollment->progress_percent >= 100 || $trackEnrollment->status === 'completed');
                        @endphp
                        <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-5 shadow-card flex flex-col justify-between gap-4">
                            <div>
                                <!-- Top row: Department + Priority -->
                                <div class="flex items-center justify-between text-[13px] mb-2">
                                    <span class="text-edtech-muted font-medium">
                                        {{ $track->department ?: 'Geral' }}
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

                                <h3 class="font-bold text-lg text-edtech-text leading-snug">
                                    {{ $track->title }}
                                </h3>
                            </div>

                            <!-- Progress info: {done} de {total} cursos concluídos & 10px progress bar -->
                            <div class="flex flex-col gap-1.5 mt-2">
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="text-edtech-muted font-medium">
                                        {{ $completedTrackCourses }} de {{ $totalTrackCourses }} {{ $totalTrackCourses === 1 ? 'curso concluído' : 'cursos concluídos' }}
                                    </span>
                                    <span class="font-semibold tabular-nums {{ $isCompleted ? 'text-alvora-emerald' : 'text-edtech-text' }}">
                                        {{ $trackEnrollment->progress_percent }}%
                                    </span>
                                </div>
                                <div class="w-full h-[10px] bg-edtech-border rounded-full overflow-hidden">
                                    @if ($isCompleted)
                                        <div class="h-full bg-alvora-emerald rounded-full transition-all duration-500" style="width: 100%;"></div>
                                    @else
                                        <div class="h-full bg-gradient-to-r from-edtech-blue to-edtech-blue-dark rounded-full transition-all duration-500" style="width: {{ $trackEnrollment->progress_percent }}%;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. CONTINUAR APRENDENDO (At most 3 non-completed courses) -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-edtech-text">Continuar Aprendendo</h2>
                <a href="{{ route('student.catalog') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-edtech-blue hover:text-edtech-blue-hover transition-colors">
                    <span>Ver todos os meus cursos</span>
                    <x-icon name="chevron-right" class="w-4 h-4" />
                </a>
            </div>

            @if ($inProgressEnrollments->isEmpty())
                <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-8 text-center space-y-3 shadow-card">
                    <div class="w-12 h-12 rounded-2xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                        <x-icon name="book-open" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-edtech-text">Nenhum curso em andamento no momento.</h3>
                    <p class="text-sm text-edtech-muted max-w-md mx-auto">
                        Inicie novos aprendizados navegando pelo catálogo de treinamentos corporativos.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('student.catalog') }}"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-edtech-blue hover:text-edtech-blue-hover transition-colors">
                            <span>Explorar o catálogo</span>
                            <x-icon name="chevron-right" class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                    @foreach ($inProgressEnrollments as $enrollment)
                        <x-student.course-card :course="$enrollment->course" :enrollment="$enrollment" />
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. WIDE INVITATION CARD (Replaces the old catalog section) -->
        <div class="w-full bg-edtech-surface rounded-2xl border border-edtech-border p-6 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-edtech-blue-light text-edtech-blue flex items-center justify-center shrink-0">
                    <x-icon name="book-open" class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="font-semibold text-lg text-edtech-text leading-tight">
                        Explore o catálogo da sua empresa
                    </h2>
                    <p class="text-sm text-edtech-muted mt-0.5">
                        {{ $availableCoursesCount }} {{ $availableCoursesCount === 1 ? 'curso disponível' : 'cursos disponíveis' }} para você
                    </p>
                </div>
            </div>

            <a href="{{ route('student.catalog') }}"
               class="h-11 px-6 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm flex items-center justify-center transition-colors shadow-xs shrink-0 cursor-pointer">
                Explorar catálogo
            </a>
        </div>
    </section>
</div>
