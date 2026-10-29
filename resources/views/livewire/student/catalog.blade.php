<div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
    <!-- LEFT SIDEBAR -->
    <x-student.sidebar active="catalog" />

    <!-- RIGHT MAIN CONTENT -->
    <main class="flex-1 min-w-0 w-full flex flex-col gap-6">
        <!-- 1. Page Header -->
        <div>
            <h1 class="font-bold text-[28px] sm:text-[32px] text-edtech-text tracking-tight leading-tight">
                Catálogo de Cursos
            </h1>
            <p class="text-[15px] text-edtech-navy-soft font-medium mt-1.5 leading-relaxed">
                Cursos liberados para a sua empresa. Matricule-se e comece quando quiser.
            </p>
        </div>

        <!-- Flash Messages -->
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

        @if ($totalCompanyCourses === 0)
            <!-- Empty State: Company has no published courses -->
            <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-10 text-center space-y-3 shadow-card">
                <div class="w-14 h-14 rounded-2xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                    <x-icon name="layout-grid" class="w-7 h-7" />
                </div>
                <h2 class="text-base font-semibold text-edtech-text">Sua empresa ainda não liberou cursos.</h2>
                <p class="text-sm text-edtech-muted max-w-md mx-auto leading-relaxed">
                    Novos treinamentos corporativos serão disponibilizados pelo time de RH e capacitação em breve.
                </p>
            </div>
        @else
            <!-- 2. Filter & Controls Bar (Select sits fully inside and wraps cleanly) -->
            <div class="w-full bg-edtech-surface rounded-2xl border border-edtech-border shadow-card p-4 flex flex-col lg:flex-row flex-wrap items-stretch lg:items-center justify-between gap-4">
                <!-- Search & Underline Status Tabs -->
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-4 flex-1">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-[260px] lg:w-[280px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-edtech-muted">
                            <x-icon name="search" class="w-4 h-4" />
                        </div>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Buscar por curso..."
                               aria-label="Buscar por curso"
                               class="w-full h-10 pl-9 pr-3.5 rounded-xl border border-edtech-border bg-edtech-surface text-sm text-edtech-text placeholder-edtech-muted focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue transition-colors">
                    </div>

                    <!-- Underline Tabs -->
                    <div class="flex items-center gap-4 border-b border-edtech-border sm:border-b-0 pb-2 sm:pb-0 overflow-x-auto no-scrollbar">
                        <button type="button"
                                wire:click="$set('status', 'all')"
                                class="relative pb-2 text-sm transition-colors whitespace-nowrap cursor-pointer {{ $status === 'all' ? 'font-semibold text-edtech-blue' : 'font-medium text-edtech-muted hover:text-edtech-text' }}">
                            Todos
                            @if ($status === 'all')
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-edtech-blue rounded-full"></div>
                            @endif
                        </button>

                        <button type="button"
                                wire:click="$set('status', 'available')"
                                class="relative pb-2 text-sm transition-colors whitespace-nowrap cursor-pointer {{ $status === 'available' ? 'font-semibold text-edtech-blue' : 'font-medium text-edtech-muted hover:text-edtech-text' }}">
                            Disponíveis
                            @if ($status === 'available')
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
                            Concluídos
                            @if ($status === 'completed')
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-edtech-blue rounded-full"></div>
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Right: Sorting & Count Indicator (Sits strictly inside the container) -->
                <div class="flex items-center justify-between lg:justify-end gap-3 shrink-0">
                    <div class="relative">
                        <select wire:model.live="sort"
                                aria-label="Ordenar cursos"
                                class="appearance-none h-10 pl-3 pr-8 rounded-xl border border-edtech-border bg-edtech-surface text-[13px] font-medium text-edtech-text focus:outline-none focus:border-edtech-blue cursor-pointer">
                            <option value="recent">Ordenar: Mais recentes</option>
                            <option value="title">Ordenar: A-Z</option>
                            <option value="duration">Ordenar: Duração</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-edtech-muted">
                            <x-icon name="chevron-left" class="w-3.5 h-3.5 -rotate-90" />
                        </div>
                    </div>

                    <span class="text-[13px] font-medium text-edtech-muted whitespace-nowrap tabular-nums">
                        @php $totalCoursesCount = $courses->total(); @endphp
                        {{ $totalCoursesCount }} {{ $totalCoursesCount === 1 ? 'curso' : 'cursos' }}
                    </span>
                </div>
            </div>

            <!-- 3. Course Cards Grid or Specialized Empty States -->
            @if ($courses->isEmpty())
                <div class="bg-edtech-surface border border-edtech-border rounded-2xl p-10 text-center space-y-3 shadow-card">
                    @if ($status === 'available' && $totalUserEnrolled >= $totalCompanyCourses && $totalCompanyCourses > 0)
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-alvora-emerald mx-auto flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6" />
                        </div>
                        <h2 class="text-base font-semibold text-edtech-text">Você já está matriculado em todos os cursos disponíveis.</h2>
                        <p class="text-sm text-edtech-muted max-w-md mx-auto leading-relaxed">
                            Excelente dedicação! Continue suas aulas em andamento através do painel de aprendizado.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('student.dashboard') }}"
                               class="inline-flex items-center gap-2 px-5 h-11 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs">
                                <span>Ir para Meu Aprendizado</span>
                            </a>
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-edtech-blue-light text-edtech-blue mx-auto flex items-center justify-center">
                            <x-icon name="search" class="w-6 h-6" />
                        </div>
                        <h2 class="text-base font-semibold text-edtech-text">Nenhum curso encontrado para a sua busca.</h2>
                        <p class="text-sm text-edtech-muted max-w-md mx-auto leading-relaxed">
                            Tente pesquisar com outras palavras-chave ou redefina os filtros selecionados.
                        </p>
                        <div class="pt-2">
                            <button type="button"
                                    wire:click="resetFilters"
                                    class="inline-flex items-center gap-2 px-4 h-10 rounded-xl border border-edtech-border bg-edtech-surface hover:bg-edtech-border-light text-edtech-text font-medium text-sm transition-colors cursor-pointer">
                                Limpar filtros
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                    @foreach ($courses as $course)
                        <x-student.course-card :course="$course" />
                    @endforeach
                </div>

                <!-- 4. Pagination -->
                @if ($courses->hasPages())
                    <div class="mt-4 pt-4 border-t border-edtech-border flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-sm text-edtech-muted font-medium">
                            Mostrando {{ $courses->firstItem() }}–{{ $courses->lastItem() }} de {{ $courses->total() }} cursos
                        </span>
                        <div>
                            {{ $courses->links() }}
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </main>
</div>

