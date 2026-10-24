<div class="px-space-xl py-space-lg flex flex-col gap-space-lg max-w-[1600px] mx-auto w-full">

    <!-- Header da Seção: Título Executivo, Contexto da Empresa e Ações -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
        <div class="flex flex-col gap-1.5 max-w-3xl">
            <div class="flex items-center flex-wrap gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">Sala de Situação</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-tertiary-fixed text-on-tertiary-fixed">
                    <span class="w-1.5 h-1.5 rounded-full bg-tertiary animate-pulse"></span>
                    Motor Preditivo Ativo
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Painel de telemetria preditiva de risco de evasão e intervenção ativa Alvora AI em tempo real.
            </p>
        </div>

        <!-- Ações de topo: Recomputar Scores -->
        <div class="flex items-center gap-3 shrink-0 self-start lg:self-center">
            <button wire:click="recalculateAllScores" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-container text-on-primary font-label-md text-label-md font-semibold hover:bg-primary shadow-sm hover:shadow transition-all active:scale-[0.98]">
                <svg wire:loading.class="animate-spin" class="w-4 h-4 shrink-0 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span wire:loading.remove wire:target="recalculateAllScores">Recomputar Scores</span>
                <span wire:loading wire:target="recalculateAllScores">Processando IA...</span>
            </button>
        </div>
    </div>

    <!-- Feedback Message / Session Flash -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-tertiary-fixed text-on-tertiary-fixed border border-tertiary/20 text-body-md font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Grid de Métricas / KPIs (Executive KPI Tiles) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
        <!-- Card 1: Alto Risco -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">Alto Risco de Evasão</span>
                <span class="inline-flex items-center rounded-md bg-error text-on-error px-2.5 py-0.5 text-xs font-semibold shadow-sm">
                    Crítico
                </span>
            </div>
            <div class="my-3 flex items-baseline gap-2">
                <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $highRiskCount }}</span>
                <span class="font-label-md text-label-md text-error font-semibold">({{ $highRiskPercent }}%)</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Score de risco &gt; 70 pts</span>
                <span class="font-label-sm text-label-sm text-error font-semibold">Ação Imediata</span>
            </div>
        </div>

        <!-- Card 2: Médio Risco / Atenção -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">Atenção / Médio Risco</span>
                <span class="inline-flex items-center rounded-md bg-amber-500 text-white px-2.5 py-0.5 text-xs font-semibold shadow-sm">
                    Alerta Ativo
                </span>
            </div>
            <div class="my-3 flex items-baseline gap-2">
                <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $mediumRiskCount }}</span>
                <span class="font-label-md text-label-md text-amber-600 font-semibold">
                    ({{ $totalStudents > 0 ? round(($mediumRiskCount / $totalStudents) * 100) : 0 }}%)
                </span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Score entre 40 e 69 pts</span>
                <span class="font-label-sm text-label-sm text-amber-600 font-semibold">Monitoramento</span>
            </div>
        </div>

        <!-- Card 3: Baixo Risco / Engajados -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">Engajados / Baixo Risco</span>
                <span class="inline-flex items-center rounded-md bg-emerald-600 text-white px-2.5 py-0.5 text-xs font-semibold shadow-sm">
                    Saudável
                </span>
            </div>
            <div class="my-3 flex items-baseline gap-2">
                <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $lowRiskCount }}</span>
                <span class="font-label-md text-label-md text-emerald-600 font-semibold">
                    ({{ $totalStudents > 0 ? round(($lowRiskCount / $totalStudents) * 100) : 0 }}%)
                </span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Score entre 0 e 39 pts</span>
                <span class="font-label-sm text-label-sm text-emerald-600 font-semibold">Ritmo Constante</span>
            </div>
        </div>

        <!-- Card 4: Intervenções Alvora AI -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">Intervenções Alvora AI</span>
                <span class="inline-flex items-center rounded-md bg-blue-600 text-white px-2.5 py-0.5 text-xs font-semibold shadow-sm">
                    Autônomo
                </span>
            </div>
            <div class="my-3 flex items-baseline gap-2">
                <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $interventionsCount }}</span>
                <span class="font-label-md text-label-md text-on-surface-variant">ações este mês</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Disparos proativos</span>
                <span class="font-label-sm text-label-sm text-primary font-semibold">Copilot AI</span>
            </div>
        </div>
    </div>

    <!-- Heatmap & Matriz de Risco em Tempo Real -->
    <div class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm flex flex-col gap-space-md border border-slate-200/60">
        <!-- Topo do Heatmap: Título e Legendas -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-2 border-b border-slate-200/60">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold tracking-tight">Distribuição da Base por Matriz de Risco (Heatmap de Engajamento em Tempo Real)</h2>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    Classificação algorítmica multidimensional combinando frequência de acesso, conclusão de atividades e aproveitamento em quizzes.
                </p>
            </div>

            <!-- Legenda Explicativa -->
            <div class="flex items-center flex-wrap gap-2 text-label-sm font-label-sm">
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-tertiary-fixed text-on-tertiary-fixed font-semibold">
                    <span class="w-2 h-2 rounded-full bg-tertiary"></span>
                    Verde (0-39 pts): Seguro / Saudável
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-secondary-fixed text-on-secondary-fixed font-semibold">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    Amarelo (40-69 pts): Atenção / Risco Médio
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-error-container text-on-error-container font-semibold">
                    <span class="w-2 h-2 rounded-full bg-error"></span>
                    Vermelho (70-100 pts): Crítico / Alto Risco
                </div>
            </div>
        </div>

        <!-- Grid Visual de 3 Raias do Heatmap -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-2 items-stretch">
            <!-- Raia 1: Verde (0-39 pts) Seguro / Saudável -->
            <div class="flex flex-col justify-between h-full p-4 rounded-xl bg-surface-container-low border border-slate-200/60">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-label-md text-label-md text-on-surface font-bold">Faixa Segura (0 - 39 pts)</span>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-0.5 text-xs font-semibold text-white shadow-sm">
                            {{ $safeStudents->count() }} colaboradores
                        </span>
                    </div>

                    <!-- Lista de Mini-cards do Colaborador Seguro -->
                    <div class="flex flex-col gap-2.5 max-h-96 overflow-y-auto pr-1">
                        @forelse($safeStudents as $student)
                            @php
                                $score = $student->latestRiskScore?->score ?? 0;
                                $breakdown = $student->latestRiskScore?->breakdown ?? [];
                                $initials = strtoupper(substr($student->name, 0, 2));
                            @endphp
                            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-sm flex flex-col gap-2.5 hover:border-emerald-400 transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-headline-sm text-xs flex items-center justify-center font-bold shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-headline-sm text-body-sm text-on-surface font-semibold truncate">{{ $student->name }}</span>
                                            <span class="font-body-sm text-[11px] text-on-surface-variant truncate">{{ $student->email }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-label-sm font-label-sm bg-tertiary-fixed text-on-tertiary-fixed font-bold shrink-0">
                                        {{ $score }} pts
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center justify-between font-label-sm text-[11px] text-on-surface-variant">
                                        <span>Engajamento Saudável</span>
                                        <span class="text-tertiary font-semibold">Baixo Risco</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-surface-container rounded-full overflow-hidden">
                                        <div class="bg-tertiary h-full rounded-full" style="width: {{ max(5, $score) }}%;"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-1 pt-0.5 text-center font-label-sm text-[10px] text-on-surface-variant">
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Inat: <strong class="text-on-surface">{{ $breakdown['inactivity']['score'] ?? 0 }}%</strong></div>
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Estag: <strong class="text-on-surface">{{ $breakdown['stagnation']['score'] ?? 0 }}%</strong></div>
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Quiz: <strong class="text-on-surface">{{ $breakdown['quiz']['score'] ?? 0 }}%</strong></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 rounded-xl bg-surface-container-lowest/80 border border-dashed border-slate-200/80 flex flex-col items-center justify-center text-center gap-1.5">
                                <span class="font-label-md text-label-md text-secondary font-medium">Nenhum colaborador nesta faixa</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Raia 2: Amarelo (40-69 pts) Atenção / Risco Médio -->
            <div class="flex flex-col justify-between h-full p-4 rounded-xl bg-surface-container-low border border-slate-200/60">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-label-md text-label-md text-on-surface font-bold">Atenção (40 - 69 pts)</span>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white shadow-sm">
                            {{ $warningStudents->count() }} colaboradores
                        </span>
                    </div>

                    <!-- Lista de Mini-cards de Atenção -->
                    <div class="flex flex-col gap-2.5 max-h-96 overflow-y-auto pr-1">
                        @forelse($warningStudents as $student)
                            @php
                                $score = $student->latestRiskScore?->score ?? 0;
                                $breakdown = $student->latestRiskScore?->breakdown ?? [];
                                $initials = strtoupper(substr($student->name, 0, 2));
                            @endphp
                            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-sm flex flex-col gap-2.5 hover:border-amber-400 transition-colors cursor-pointer"
                                 wire:click="openInterventionModal({{ $student->id }})">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-secondary-fixed text-on-secondary-fixed font-headline-sm text-xs flex items-center justify-center font-bold shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-headline-sm text-body-sm text-on-surface font-semibold truncate">{{ $student->name }}</span>
                                            <span class="font-body-sm text-[11px] text-on-surface-variant truncate">{{ $student->email }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-label-sm font-label-sm bg-secondary-fixed text-on-secondary-fixed font-bold shrink-0">
                                        {{ $score }} pts
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center justify-between font-label-sm text-[11px] text-on-surface-variant">
                                        <span>Risco Médio de Desistência</span>
                                        <span class="text-amber-600 font-semibold">Médio Risco</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-surface-container rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ $score }}%;"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-1 pt-0.5 text-center font-label-sm text-[10px] text-on-surface-variant">
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Inat: <strong class="text-on-surface">{{ $breakdown['inactivity']['score'] ?? 0 }}%</strong></div>
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Estag: <strong class="text-on-surface">{{ $breakdown['stagnation']['score'] ?? 0 }}%</strong></div>
                                    <div class="bg-surface-container-low py-0.5 px-1 rounded">Quiz: <strong class="text-on-surface">{{ $breakdown['quiz']['score'] ?? 0 }}%</strong></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 rounded-xl bg-surface-container-lowest/80 border border-dashed border-slate-200/80 flex flex-col items-center justify-center text-center gap-1.5">
                                <span class="font-label-md text-label-md text-secondary font-medium">Nenhum colaborador nesta faixa</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Raia 3: Vermelho (70-100 pts) Crítico / Alto Risco -->
            <div class="flex flex-col justify-between h-full p-4 rounded-xl bg-surface-container-low border border-slate-200/60">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-error"></span>
                            <span class="font-label-md text-label-md text-on-surface font-bold">Crítico (70 - 100 pts)</span>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-error px-2 py-0.5 text-xs font-semibold text-on-error shadow-sm">
                            {{ $criticalStudents->count() }} críticos
                        </span>
                    </div>

                    <!-- Lista de Mini-cards Críticos ou Empty State Elegante -->
                    <div class="flex flex-col gap-2.5 max-h-96 overflow-y-auto pr-1">
                        @forelse($criticalStudents as $student)
                            @php
                                $score = $student->latestRiskScore?->score ?? 0;
                                $breakdown = $student->latestRiskScore?->breakdown ?? [];
                                $initials = strtoupper(substr($student->name, 0, 2));
                            @endphp
                            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-error/40 shadow-sm flex flex-col gap-2.5 hover:border-error transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-error-container text-error font-headline-sm text-xs flex items-center justify-center font-bold shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-headline-sm text-body-sm text-on-surface font-semibold truncate">{{ $student->name }}</span>
                                            <span class="font-body-sm text-[11px] text-on-surface-variant truncate">{{ $student->email }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-label-sm font-label-sm bg-error-container text-error font-bold shrink-0">
                                        {{ $score }} pts
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center justify-between font-label-sm text-[11px] text-error font-semibold">
                                        <span>Alto Risco de Evasão</span>
                                        <span>Crítico</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-surface-container rounded-full overflow-hidden">
                                        <div class="bg-error h-full rounded-full" style="width: {{ $score }}%;"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <div class="text-[10px] text-secondary">
                                        Inat: <strong>{{ $breakdown['inactivity']['score'] ?? 0 }}%</strong> • Estag: <strong>{{ $breakdown['stagnation']['score'] ?? 0 }}%</strong>
                                    </div>
                                    <button wire:click="openInterventionModal({{ $student->id }})" type="button"
                                            class="px-2.5 py-1 rounded bg-error text-on-error font-label-sm text-xs font-semibold shadow-sm hover:bg-error/90 transition-colors">
                                        Intervir IA
                                    </button>
                                </div>
                            </div>
                        @empty
                            <!-- Empty State Elegante Oficial do Stitch -->
                            <div class="p-6 rounded-xl bg-surface-container-lowest/80 border border-dashed border-slate-200/80 flex flex-col items-center justify-center text-center gap-2.5 min-h-[168px] my-auto">
                                <div class="w-9 h-9 rounded-full bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center">
                                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <span class="font-headline-sm text-label-md text-on-surface font-semibold">Nenhum colaborador nesta faixa</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant max-w-[220px]">
                                    Excelente sinal! Nenhuma evasão iminente detectada pelo motor preditivo.
                                </span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Dados e Gestão Preditiva de Colaboradores -->
    <div class="rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden flex flex-col border border-slate-200/60">
        <!-- Toolbar: Segmented Controls + Busca + Atalho ⌘K -->
        <div class="p-space-md flex flex-col lg:flex-row lg:items-center justify-between gap-space-md bg-surface-container-lowest border-b border-slate-200/60">
            <!-- Segmented Tabs shadcn -->
            <div class="inline-flex items-center p-1 rounded-xl bg-surface-container-low gap-1 self-start border border-slate-200/60">
                <button wire:click="setFilter('all')" type="button"
                        class="px-3 py-1.5 rounded-lg text-body-sm font-label-md font-semibold transition-all flex items-center gap-1.5 {{ $filterLevel === 'all' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                    <span>Todos</span>
                    <span class="px-1.5 py-0.2 rounded-full font-label-sm text-label-sm bg-surface-container text-secondary">{{ $totalStudents }}</span>
                </button>
                <button wire:click="setFilter('high')" type="button"
                        class="px-3 py-1.5 rounded-lg text-body-sm font-label-md font-medium transition-all flex items-center gap-1.5 {{ $filterLevel === 'high' ? 'bg-surface-container-lowest text-error font-semibold shadow-sm' : 'text-on-surface-variant hover:text-error' }}">
                    <span>Críticos</span>
                    <span class="px-1.5 py-0.2 rounded-full font-label-sm text-label-sm bg-error-container text-error font-bold">{{ $highRiskCount }}</span>
                </button>
                <button wire:click="setFilter('medium')" type="button"
                        class="px-3 py-1.5 rounded-lg text-body-sm font-label-md font-medium transition-all flex items-center gap-1.5 {{ $filterLevel === 'medium' ? 'bg-surface-container-lowest text-amber-600 font-semibold shadow-sm' : 'text-on-surface-variant hover:text-amber-600' }}">
                    <span>Atenção</span>
                    <span class="px-1.5 py-0.2 rounded-full font-label-sm text-label-sm bg-surface-container text-secondary">{{ $mediumRiskCount }}</span>
                </button>
                <button wire:click="setFilter('low')" type="button"
                        class="px-3 py-1.5 rounded-lg text-body-sm font-label-md font-medium transition-all flex items-center gap-1.5 {{ $filterLevel === 'low' ? 'bg-surface-container-lowest text-tertiary font-semibold shadow-sm' : 'text-on-surface-variant hover:text-tertiary' }}">
                    <span>Engajados</span>
                    <span class="px-1.5 py-0.2 rounded-full font-label-sm text-label-sm bg-surface-container text-secondary">{{ $lowRiskCount }}</span>
                </button>
            </div>

            <!-- Busca Rápida -->
            <div class="relative w-full sm:w-80">
                <svg class="w-4 h-4 absolute left-3 top-3 text-secondary stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input wire:model.live.debounce.300ms="search"
                       class="w-full h-10 pl-9 pr-12 rounded-lg bg-surface-container-lowest font-body-md text-body-md text-on-surface placeholder:text-outline-variant shadow-sm border border-slate-200/80 focus:outline-none focus:ring-2 focus:ring-primary-container"
                       placeholder="Buscar por colaborador ou email..." type="text">
                <span class="absolute right-2.5 top-2.5 px-1.5 py-0.5 rounded font-label-sm text-label-sm bg-surface-container text-secondary font-mono">⌘K</span>
            </div>
        </div>

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low h-11 font-table-header text-table-header text-secondary uppercase tracking-wider border-b border-slate-200/60">
                        <th class="px-space-md py-2 font-semibold">Colaborador</th>
                        <th class="px-space-md py-2 font-semibold">Score de Risco</th>
                        <th class="px-space-md py-2 font-semibold">Fatores Ponderados</th>
                        <th class="px-space-md py-2 font-semibold">Status de Intervenção</th>
                        <th class="px-space-md py-2 font-semibold text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low font-body-md text-body-md text-on-surface">
                    @forelse ($students as $student)
                        @php
                            $scoreRecord = $student->latestRiskScore;
                            $score = $scoreRecord?->score ?? 0;
                            $level = $scoreRecord?->level ?? 'low';
                            $breakdown = $scoreRecord?->breakdown ?? [];
                            $latestIntervention = $student->interventions()->latest()->first();
                            $initials = strtoupper(substr($student->name, 0, 2));
                        @endphp
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Colaborador -->
                            <td class="px-space-md py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-primary shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-headline-sm text-body-md text-on-surface font-semibold truncate">{{ $student->name }}</span>
                                        <span class="font-body-sm text-xs text-secondary truncate">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Score de Risco -->
                            <td class="px-space-md py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm {{ $score >= 70 ? 'text-error' : ($score >= 40 ? 'text-amber-600' : 'text-tertiary') }}">
                                        {{ $score }}/100
                                    </span>
                                    @if($score >= 70)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-error-container text-error">
                                            Crítico
                                        </span>
                                    @elseif($score >= 40)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-secondary-fixed text-on-secondary-fixed">
                                            Atenção
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-tertiary-fixed text-on-tertiary-fixed">
                                            Seguro
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Fatores Ponderados -->
                            <td class="px-space-md py-4">
                                <div class="flex items-center gap-2 text-xs text-secondary">
                                    <span class="bg-surface-container-low px-1.5 py-0.5 rounded">Inat: <strong class="text-on-surface">{{ $breakdown['inactivity']['score'] ?? 0 }}%</strong></span>
                                    <span>•</span>
                                    <span class="bg-surface-container-low px-1.5 py-0.5 rounded">Estag: <strong class="text-on-surface">{{ $breakdown['stagnation']['score'] ?? 0 }}%</strong></span>
                                    <span>•</span>
                                    <span class="bg-surface-container-low px-1.5 py-0.5 rounded">Quiz: <strong class="text-on-surface">{{ $breakdown['quiz']['score'] ?? 0 }}%</strong></span>
                                </div>
                            </td>

                            <!-- Status de Intervenção -->
                            <td class="px-space-md py-4">
                                @if ($latestIntervention)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-primary font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span>Enviada {{ $latestIntervention->created_at->diffForHumans() }}</span>
                                    </span>
                                @else
                                    <span class="text-xs text-secondary italic">Nenhuma intervenção</span>
                                @endif
                            </td>

                            <!-- Ação -->
                            <td class="px-space-md py-4 text-right">
                                <button wire:click="openInterventionModal({{ $student->id }})" type="button"
                                        class="h-8 px-3 rounded-md text-xs font-semibold transition-colors shadow-sm {{ $score >= 70 ? 'bg-error text-on-error hover:bg-error/90' : 'bg-surface-container-low text-on-surface hover:bg-surface-container' }}">
                                    Intervir
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-space-md py-12 text-center text-secondary">
                                Nenhum colaborador encontrado com os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Intervenção Manual / Alvora Copilot IA -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl bg-surface-container-lowest border border-slate-200/80 p-6 shadow-2xl relative flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Enviar Intervenção Personalizada</h3>
                            <p class="font-body-sm text-xs text-on-surface-variant">Esta mensagem será entregue diretamente ao colaborador no painel de treinamentos.</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" type="button" class="p-1 rounded-lg text-secondary hover:text-on-surface transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="block font-label-md text-xs font-semibold text-on-surface">Mensagem de Apoio / Incentivo Pedagógico</label>
                    <textarea wire:model="manualMessage" rows="5"
                              placeholder="Digite a mensagem de apoio para incentivar o colaborador..."
                              class="w-full p-3 font-body-md text-body-md rounded-xl bg-surface-container-low border border-slate-200 text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary-container"></textarea>
                    @error('manualMessage')
                        <span class="font-label-sm text-xs text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200/60">
                    <button wire:click="closeModal" type="button" class="px-4 py-2 text-xs font-semibold rounded-lg text-secondary hover:text-on-surface transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendManualIntervention" type="button"
                            class="px-5 py-2.5 text-xs font-semibold rounded-lg text-on-primary bg-primary-container hover:bg-primary shadow-sm transition-all flex items-center gap-2">
                        <span>Disparar Intervenção</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
