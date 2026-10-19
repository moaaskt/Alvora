<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">
    {{-- Header de Boas-vindas --}}
    <div class="bg-gradient-to-r from-gray-900 via-gray-900 to-indigo-950/40 border border-white/10 rounded-2xl p-6 sm:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-3">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Painel do Aluno
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Olá, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-gray-400 mt-1 text-sm sm:text-base max-w-2xl">
                    Acompanhe seus cursos corporativos, continue suas aulas onde parou e aprimore suas competências profissionais.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="#catalogo" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Explorar Catálogo
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-300">✕</button>
        </div>
    @endif

    @if (session('warning'))
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-amber-400/60 hover:text-amber-300">✕</button>
        </div>
    @endif

    {{-- Cards de Métricas do Aluno --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5 hover:border-white/10 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-400">Total Matriculado</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-3">{{ $totalEnrolled }}</p>
            <p class="text-xs text-gray-500 mt-1">Cursos no seu plano</p>
        </div>

        <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5 hover:border-white/10 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-400">Em Andamento</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-400 mt-3">{{ $inProgressCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Aulas a concluir</p>
        </div>

        <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5 hover:border-white/10 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-400">Concluídos</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-400 mt-3">{{ $completedCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Cursos finalizados</p>
        </div>

        <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-5 hover:border-white/10 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-400">Progresso Geral</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-purple-400 mt-3">{{ $averageProgress }}%</p>
            <p class="text-xs text-gray-500 mt-1">Média de conclusão</p>
        </div>
    </div>

    {{-- Seção: Meus Cursos em Andamento --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Continuar Aprendendo
            </h2>
            <span class="text-xs text-gray-400">{{ $enrollments->count() }} curso(s) matriculado(s)</span>
        </div>

        @if ($enrollments->isEmpty())
            <div class="bg-gray-900/40 border border-white/5 rounded-2xl p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-white">Você ainda não está matriculado em nenhum curso</h3>
                <p class="text-sm text-gray-400 max-w-md mx-auto">
                    Navegue pelo catálogo corporativo abaixo e inscreva-se para começar seu aprendizado.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($enrollments as $enrollment)
                    <div class="bg-gray-900/60 border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition flex flex-col group">
                        <div class="relative h-44 bg-gray-800 overflow-hidden">
                            @if ($enrollment->course->thumbnail)
                                <img src="{{ $enrollment->course->thumbnail }}" alt="{{ $enrollment->course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-950/60 to-gray-900 text-indigo-400">
                                    <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute top-3 right-3">
                                @if ($enrollment->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 backdrop-blur-md">
                                        Concluído ✓
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 backdrop-blur-md">
                                        {{ $enrollment->progress_percent }}% Concluído
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div>
                                <h3 class="font-bold text-white text-base line-clamp-1 group-hover:text-indigo-400 transition">
                                    {{ $enrollment->course->title }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-1 line-clamp-2">
                                    {{ $enrollment->course->description ?? 'Sem descrição informada.' }}
                                </p>
                            </div>

                            <div class="space-y-3 pt-2">
                                {{-- Barra de Progresso --}}
                                <div>
                                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                                        <span>Progresso</span>
                                        <span class="font-semibold text-white">{{ $enrollment->progress_percent }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-2 rounded-full transition-all duration-500" style="width: {{ $enrollment->progress_percent }}%"></div>
                                    </div>
                                </div>

                                {{-- Ação de Continuar --}}
                                <button
                                    wire:click="continueCourse({{ $enrollment->course->id }})"
                                    class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    </svg>
                                    @if ($enrollment->status === 'completed')
                                        Rever Aulas
                                    @else
                                        Continuar Aula
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Seção: Catálogo Corporativo de Cursos Disponíveis --}}
    <div id="catalogo" class="space-y-4 pt-6 border-t border-white/5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Cursos Disponíveis da Sua Empresa
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Explore novos temas e desenvolva novas habilidades</p>
            </div>

            <div class="w-full sm:w-72">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por curso..."
                        class="w-full px-4 py-2 pl-9 bg-gray-900/80 border border-white/10 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    >
                    <svg class="w-4 h-4 text-gray-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        @if ($availableCourses->isEmpty())
            <div class="bg-gray-900/40 border border-white/5 rounded-2xl p-8 text-center text-gray-400 text-sm">
                Nenhum novo curso disponível encontrado no momento.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($availableCourses as $course)
                    <div class="bg-gray-900/60 border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition flex flex-col justify-between group">
                        <div>
                            <div class="relative h-40 bg-gray-800 overflow-hidden">
                                @if ($course->thumbnail)
                                    <img src="{{ $course->thumbnail }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-950/40 to-gray-900 text-indigo-400">
                                        <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg text-xs font-medium bg-black/60 backdrop-blur-md text-gray-300 border border-white/10">
                                    {{ $course->lessons_count }} aulas
                                </span>
                            </div>

                            <div class="p-5 space-y-2">
                                <h3 class="font-bold text-white text-base line-clamp-1 group-hover:text-indigo-400 transition">
                                    {{ $course->title }}
                                </h3>
                                <p class="text-xs text-gray-400 line-clamp-2">
                                    {{ $course->description ?? 'Sem descrição detalhada cadastrada para este curso.' }}
                                </p>
                            </div>
                        </div>

                        <div class="p-5 pt-0">
                            <button
                                wire:click="enroll({{ $course->id }})"
                                class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600/90 hover:bg-indigo-600 border border-indigo-500/30 transition flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Matricular-se Gratuitamente
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
