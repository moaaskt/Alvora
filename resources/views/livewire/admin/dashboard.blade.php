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
                <x-icon name="plus-lg" class="w-4 h-4" />
                <span>Novo Curso</span>
            </a>
            <a href="{{ route('admin.billing') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 shadow-xs transition-colors">
                <x-icon name="credit-card-2-front-fill" class="w-4 h-4 text-slate-500" />
                <span>Faturamento B2B</span>
            </a>
        </div>
    </div>

    <!-- 5 KPIs Cards Grid (Bootstrap Icons - sem background) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total de Cursos (bi-journal-bookmark-fill) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Cursos</span>
                <x-icon name="journal-bookmark-fill" class="w-5 h-5 text-blue-600 shrink-0" />
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

        <!-- 2. Total de Aulas (bi-collection-play-fill) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Aulas</span>
                <x-icon name="collection-play-fill" class="w-5 h-5 text-indigo-600 shrink-0" />
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

        <!-- 3. Total Geral de Matrículas (bi-mortarboard-fill) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Matrículas</span>
                <x-icon name="mortarboard-fill" class="w-5 h-5 text-emerald-600 shrink-0" />
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

        <!-- 4. Alunos Ativos (bi-people-fill) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alunos Ativos</span>
                <x-icon name="people-fill" class="w-5 h-5 text-cyan-600 shrink-0" />
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $totalStudents }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Colaboradores matriculados</span>
                </div>
            </div>
        </div>

        <!-- 5. Instrutores Cadastrados (bi-person-badge-fill) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Instrutores</span>
                <x-icon name="person-badge-fill" class="w-5 h-5 text-amber-600 shrink-0" />
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
                                @if($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 shrink-0">
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
                                    <x-icon name="pencil-square" class="w-4 h-4" />
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
                    <x-icon name="tags-fill" class="w-3.5 h-3.5 text-emerald-400" />
                    Categorias
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <x-icon name="mortarboard-fill" class="w-3.5 h-3.5 text-blue-400" />
                    Matrículas
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <x-icon name="people-fill" class="w-3.5 h-3.5 text-amber-400" />
                    Usuários
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/10 transition-colors">
                    <x-icon name="gear-fill" class="w-3.5 h-3.5 text-purple-400" />
                    Configurações
                </span>
            </div>
        </div>
    </div>
</div>

