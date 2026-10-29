<div class="space-y-space-xl">
    <!-- Top Header & Fast Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                    Super Admin
                </span>
                <span class="text-xs text-slate-400 font-medium">Controle Global da Plataforma</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 font-headline-lg">
                Painel Geral & Métricas do Negócio
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Monitoramento consolidado de cursos, aulas, matrículas, alunos e instrutores de todas as empresas clientes.
            </p>
        </div>

        <!-- Quick Actions Buttons -->
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-container shadow-sm transition-all hover:shadow">
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Novo Curso</span>
            </a>
            <a href="{{ route('admin.billing') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                </svg>
                <span>Faturamento B2B</span>
            </a>
        </div>
    </div>

    <!-- 5 KPIs Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total de Cursos -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Cursos</span>
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalCourses }}</div>
                <div class="flex items-center gap-2 mt-2 text-xs">
                    <span class="inline-flex items-center text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">
                        {{ $publishedCourses }} publ.
                    </span>
                    <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 px-1.5 py-0.5 rounded">
                        {{ $draftCourses }} rasc.
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Total de Aulas -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Aulas</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalLessons }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                    <span class="font-semibold text-slate-700">
                        {{ $totalCourses > 0 ? round($totalLessons / $totalCourses, 1) : 0 }}
                    </span>
                    <span>aulas em média / curso</span>
                </div>
            </div>
        </div>

        <!-- 3. Total Geral de Matrículas -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Matrículas</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalEnrollments }}</div>
                <div class="flex items-center gap-2 mt-2 text-xs">
                    <span class="inline-flex items-center text-blue-600 font-semibold bg-blue-50 px-1.5 py-0.5 rounded">
                        {{ $activeEnrollments }} ativas
                    </span>
                    <span class="inline-flex items-center text-emerald-700 font-medium bg-emerald-50 px-1.5 py-0.5 rounded">
                        {{ $completedEnrollments }} concl.
                    </span>
                </div>
            </div>
        </div>

        <!-- 4. Alunos Ativos -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alunos Ativos</span>
                <div class="w-9 h-9 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalStudents }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Colaboradores matriculados</span>
                </div>
            </div>
        </div>

        <!-- 5. Instrutores Cadastrados -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Instrutores</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalInstructors }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                    <span class="font-semibold text-slate-700">
                        {{ $totalInstructors > 0 ? round($totalCourses / $totalInstructors, 1) : 0 }}
                    </span>
                    <span>cursos / instrutor</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Columns: Cursos Recentes & Últimas Matrículas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Coluna da Esquerda: Cursos Recentes (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 leading-tight">Cursos Recentes da Plataforma</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Últimos treinamentos corporativos criados</p>
                    </div>
                    <a href="{{ route('admin.courses.index') }}" class="text-xs font-semibold text-primary hover:text-primary-container transition-colors">
                        Ver todos &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 overflow-x-auto">
                    @forelse($recentCourses as $course)
                        <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($course->thumbnail)
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 shrink-0">
                                @else
                                    <div class="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 font-bold text-xs shrink-0">
                                        ALV
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="text-sm font-semibold text-slate-900 hover:text-primary transition-colors truncate block">
                                        {{ $course->title }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                        <span>{{ $course->instructor?->name ?? 'Sem instrutor' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $course->lessons_count }} aulas</span>
                                        <span>&bull;</span>
                                        <span>{{ $course->enrollments_count }} matrículas</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                @if($course->status === 'published')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        Publicado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        Rascunho
                                    </span>
                                @endif
                                <a href="{{ route('admin.courses.edit', $course) }}" class="p-1.5 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Nenhum curso cadastrado ainda.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-3 bg-slate-50/50 border-t border-slate-100 text-center text-xs text-slate-500">
                Total de {{ $totalCourses }} cursos sob gestão da Alvora
            </div>
        </div>

        <!-- Coluna da Direita: Últimas Matrículas (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 leading-tight">Últimas Matrículas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Entradas recentes de alunos em cursos</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">
                        {{ $totalEnrollments }} Total
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentEnrollments as $enrollment)
                        <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                    {{ substr($enrollment->user?->name ?? 'A', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-semibold text-slate-900 truncate block">
                                        {{ $enrollment->user?->name ?? 'Aluno Removido' }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 truncate block">
                                        {{ $enrollment->course?->title ?? 'Curso' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] font-medium text-slate-400 block">
                                    {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('d/m/Y') : $enrollment->created_at->format('d/m/Y') }}
                                </span>
                                @if($enrollment->status === 'completed')
                                    <span class="inline-block text-[10px] font-semibold text-emerald-600">Concluído</span>
                                @else
                                    <span class="inline-block text-[10px] font-semibold text-blue-600">{{ $enrollment->progress_percent }}%</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Nenhuma matrícula registrada ainda.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-3 bg-slate-50/50 border-t border-slate-100 text-center text-xs text-slate-500">
                Auditoria contínua de matrículas corporativas
            </div>
        </div>
    </div>

    <!-- Bottom Row: Governança do LMS & Atalhos dos Próximos Módulos -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-6 text-white shadow-md">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 mb-2">
                    LMS Enterprise Alvora
                </span>
                <h3 class="text-lg font-bold">Módulos Administrativos Globais</h3>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    Acesso unificado para gerenciar categorias com subcategorias, atribuição manual de matrículas com histórico auditável, gestão de perfis (Admin, Instrutor e Aluno) e configurações técnicas do sistema.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Categorias
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Matrículas
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Usuários
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                    Configurações
                </span>
            </div>
        </div>
    </div>
</div>

