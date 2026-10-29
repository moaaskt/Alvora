<div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
    <!-- LEFT SIDEBAR -->
    <x-student.sidebar active="tracks" />

    <!-- RIGHT MAIN CONTENT -->
    <main class="flex-1 min-w-0 w-full flex flex-col gap-6">
        <!-- 1. Page Header -->
        <div>
            <h1 class="font-bold text-[28px] sm:text-[32px] text-edtech-text tracking-tight leading-tight">
                Minhas Trilhas
            </h1>
            <p class="text-[15px] text-edtech-navy-soft font-medium mt-1.5 leading-relaxed">
                Acompanhe os programas de capacitação corporativa atribuídos ao seu plano de desenvolvimento.
            </p>
        </div>

        @if (!$hasAnyTracks)
            <!-- Empty State: Zero Tracks Assigned -->
            <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-10 text-center space-y-4 shadow-card">
                <div class="w-14 h-14 rounded-2xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                    <x-icon name="map" class="w-7 h-7" />
                </div>
                <div class="max-w-md mx-auto">
                    <h2 class="text-lg font-semibold text-edtech-text">Nenhuma trilha atribuída à sua conta no momento.</h2>
                    <p class="text-sm text-edtech-muted mt-1 leading-relaxed">
                        Sua organização ainda não vinculou seu perfil a uma trilha corporativa de desenvolvimento. Os cursos individuais continuam disponíveis no catálogo.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('student.catalog') }}"
                       class="inline-flex items-center gap-2 px-5 h-11 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs">
                        <x-icon name="layout-grid" class="w-4 h-4" />
                        <span>Explorar o catálogo</span>
                    </a>
                </div>
            </div>
        @else
            <!-- 2. Filter & Controls Bar (Overlapping Card) -->
            <div class="bg-edtech-surface rounded-2xl border border-edtech-border p-4 shadow-card flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-4 flex-1">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-[260px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-edtech-muted">
                            <x-icon name="search" class="w-4 h-4" />
                        </div>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Buscar trilha..."
                               aria-label="Buscar trilha"
                               class="w-full h-10 pl-9 pr-3.5 rounded-xl border border-edtech-border bg-edtech-surface text-sm text-edtech-text placeholder-edtech-muted focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue transition-colors">
                    </div>

                    <!-- Underline Status Tabs -->
                    <div class="flex items-center gap-4 border-b border-edtech-border sm:border-b-0 pb-2 sm:pb-0 overflow-x-auto no-scrollbar">
                        <button type="button"
                                wire:click="$set('status', 'all')"
                                class="relative pb-2 text-sm transition-colors whitespace-nowrap cursor-pointer {{ $status === 'all' ? 'font-semibold text-edtech-blue' : 'font-medium text-edtech-muted hover:text-edtech-text' }}">
                            Todas
                            @if ($status === 'all')
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-edtech-blue rounded-full"></div>
                            @endif
                        </button>

                        <button type="button"
                                wire:click="$set('status', 'in_progress')"
                                class="relative pb-2 text-sm transition-colors whitespace-nowrap cursor-pointer {{ $status === 'in_progress' ? 'font-semibold text-edtech-blue' : 'font-medium text-edtech-muted hover:text-edtech-text' }}">
                            Em andamento
                            @if ($status === 'in_progress')
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-edtech-blue rounded-full"></div>
                            @endif
                        </button>

                        <button type="button"
                                wire:click="$set('status', 'completed')"
                                class="relative pb-2 text-sm transition-colors whitespace-nowrap cursor-pointer {{ $status === 'completed' ? 'font-semibold text-edtech-blue' : 'font-medium text-edtech-muted hover:text-edtech-text' }}">
                            Concluídas
                            @if ($status === 'completed')
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-edtech-blue rounded-full"></div>
                            @endif
                        </button>
                    </div>

                    <!-- Selects: Prioridade & Ordenação -->
                    <div class="flex items-center gap-3">
                        <!-- Select Prioridade -->
                        <div class="relative">
                            <select wire:model.live="priority"
                                    aria-label="Filtrar por prioridade"
                                    class="appearance-none h-10 pl-3 pr-8 rounded-xl border border-edtech-border bg-edtech-surface text-[13px] font-medium text-edtech-text focus:outline-none focus:border-edtech-blue cursor-pointer">
                                <option value="all">Prioridade: Todas</option>
                                <option value="mandatory">Prioridade: Obrigatórias</option>
                                <option value="optional">Prioridade: Opcionais</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-edtech-muted">
                                <x-icon name="chevron-left" class="w-3.5 h-3.5 -rotate-90" />
                            </div>
                        </div>

                        <!-- Select Ordenar -->
                        <div class="relative">
                            <select wire:model.live="sort"
                                    aria-label="Ordenar trilhas"
                                    class="appearance-none h-10 pl-3 pr-8 rounded-xl border border-edtech-border bg-edtech-surface text-[13px] font-medium text-edtech-text focus:outline-none focus:border-edtech-blue cursor-pointer">
                                <option value="recent">Ordenar: Mais recentes</option>
                                <option value="progress">Ordenar: Maior progresso</option>
                                <option value="title">Ordenar: A-Z</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-edtech-muted">
                                <x-icon name="chevron-left" class="w-3.5 h-3.5 -rotate-90" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Total Count Indicator -->
                <div class="text-[13px] font-medium text-edtech-muted shrink-0 self-center">
                    @php $totalTracks = $trackEnrollments->total(); @endphp
                    {{ $totalTracks }} {{ $totalTracks === 1 ? 'trilha' : 'trilhas' }}
                </div>
            </div>

            <!-- 3. Track Cards Grid or Filter Empty State -->
            @if ($trackEnrollments->isEmpty())
                <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-10 text-center space-y-4 shadow-card">
                    <div class="w-12 h-12 rounded-xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                        <x-icon name="search" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-edtech-text">Nenhuma trilha encontrada para a sua busca.</h2>
                        <p class="text-sm text-edtech-muted mt-1">Tente ajustar os filtros ou pesquisar por outro termo.</p>
                    </div>
                    <button type="button"
                            wire:click="resetFilters"
                            class="inline-flex items-center gap-2 px-4 h-10 rounded-xl border border-edtech-border bg-edtech-surface hover:bg-edtech-border-light text-edtech-text font-medium text-sm transition-colors cursor-pointer">
                        Limpar filtros
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 {{ $trackEnrollments->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-6 items-start">
                    @foreach ($trackEnrollments as $trackEnrollment)
                        @php
                            $track = $trackEnrollment->track;
                            $publishedCourses = $track ? $track->publishedCourses : collect();
                            $totalCourses = $publishedCourses->count();
                            $isCompleted = ($trackEnrollment->status === 'completed' || $trackEnrollment->progress_percent >= 100);
                        @endphp
                        <article class="bg-edtech-surface border border-edtech-border rounded-2xl p-6 shadow-card hover:shadow-card-hover motion-safe:hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between gap-5">
                            <div>
                                <!-- Top Row: Department & Status Indicator (Plain text, no pill badges) -->
                                <div class="flex items-center justify-between text-[13px] mb-2">
                                    <span class="text-edtech-muted font-medium">
                                        {{ $track->department ?: 'Geral' }}
                                    </span>

                                    @if ($track->is_mandatory)
                                        <span class="font-semibold text-edtech-amber flex items-center gap-1.5">
                                            <x-icon name="alert-triangle" class="w-4 h-4 text-edtech-amber shrink-0" />
                                            Obrigatória
                                        </span>
                                    @else
                                        <span class="font-medium text-edtech-muted">
                                            Opcional
                                        </span>
                                    @endif
                                </div>

                                <!-- Title & Description -->
                                <h2 class="text-[19px] font-bold text-edtech-text leading-snug tracking-tight">
                                    {{ $track->title }}
                                </h2>
                                @if ($track->description)
                                    <p class="text-[14px] text-edtech-muted mt-2 line-clamp-2 leading-relaxed">
                                        {{ $track->description }}
                                    </p>
                                @endif

                                <!-- Consolidated Progress Bar (10px tall) -->
                                <div class="mt-5 mb-2">
                                    <div class="flex items-center justify-between text-[13px] mb-2 font-medium">
                                        <span class="text-edtech-muted">Progresso consolidado da trilha</span>
                                        <span class="font-bold tabular-nums {{ $isCompleted ? 'text-alvora-emerald' : 'text-edtech-text' }}">
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

                            <!-- Course Rows Section -->
                            <div class="border-t border-edtech-border pt-4">
                                <h3 class="text-[14px] font-semibold text-edtech-text mb-2">
                                    Cursos da trilha ({{ $totalCourses }})
                                </h3>

                                @if ($totalCourses === 0)
                                    <p class="text-[13px] text-edtech-muted py-2">
                                        Nenhum curso publicado nesta trilha no momento.
                                    </p>
                                @else
                                    <div class="flex flex-col divide-y divide-edtech-border/60">
                                        @foreach ($publishedCourses as $course)
                                            @php
                                                $courseEnrollment = $courseEnrollments->get($course->id);
                                                $coursePercent = $courseEnrollment ? (int) $courseEnrollment->progress_percent : 0;
                                                $isCourseDone = $courseEnrollment && ($courseEnrollment->status === 'completed' || $coursePercent >= 100);
                                                $isUserEnrolled = (bool) $courseEnrollment;
                                            @endphp

                                            @if ($isUserEnrolled)
                                                <!-- Enrolled: Links to Lesson Player -->
                                                <button type="button"
                                                        wire:click="continueCourse({{ $course->id }})"
                                                        title="Continuar curso {{ $course->title }}"
                                                        class="group py-2.5 px-2 rounded-xl flex items-center justify-between gap-3 text-left hover:bg-edtech-blue-light/60 transition-colors duration-200 cursor-pointer w-full">
                                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                                        <span class="text-[13px] font-medium text-edtech-muted w-4 shrink-0 tabular-nums">{{ $loop->iteration }}.</span>
                                                        <span class="text-[14px] font-medium text-edtech-text group-hover:text-edtech-blue transition-colors truncate">
                                                            {{ $course->title }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        @if ($isCourseDone)
                                                            <span class="text-[13px] font-semibold text-alvora-emerald flex items-center gap-1">
                                                                <x-icon name="check-circle" class="w-4 h-4 text-alvora-emerald shrink-0" />
                                                                Concluído
                                                            </span>
                                                        @else
                                                            <span class="text-[13px] font-semibold text-edtech-muted tabular-nums">{{ $coursePercent }}%</span>
                                                        @endif
                                                        <x-icon name="chevron-right" class="w-4 h-4 text-edtech-muted/70 group-hover:text-edtech-blue transition-colors" />
                                                    </div>
                                                </button>
                                            @else
                                                <!-- Not enrolled: Links to Catalog -->
                                                <a href="{{ route('student.catalog') }}"
                                                   title="Ver curso no catálogo: {{ $course->title }}"
                                                   class="group py-2.5 px-2 rounded-xl flex items-center justify-between gap-3 text-left hover:bg-edtech-blue-light/60 transition-colors duration-200 cursor-pointer w-full">
                                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                                        <span class="text-[13px] font-medium text-edtech-muted w-4 shrink-0 tabular-nums">{{ $loop->iteration }}.</span>
                                                        <span class="text-[14px] font-medium text-edtech-text group-hover:text-edtech-blue transition-colors truncate">
                                                            {{ $course->title }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-[13px] font-medium text-edtech-muted">Disponível no catálogo</span>
                                                        <x-icon name="chevron-right" class="w-4 h-4 text-edtech-muted/70 group-hover:text-edtech-blue transition-colors" />
                                                    </div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- 4. Pagination -->
                @if ($trackEnrollments->hasPages())
                    <div class="mt-4 pt-4 border-t border-edtech-border flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-sm text-edtech-muted font-medium">
                            Mostrando {{ $trackEnrollments->firstItem() }}–{{ $trackEnrollments->lastItem() }} de {{ $trackEnrollments->total() }} trilhas
                        </span>
                        <div>
                            {{ $trackEnrollments->links() }}
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </main>
</div>

