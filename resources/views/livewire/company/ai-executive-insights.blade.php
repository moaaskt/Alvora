<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center p-2 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Insights Executivos & ROI</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Diagnósticos inteligentes gerados por IA com análise de retenção e retorno do investimento.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="refreshInsights" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-purple-600 hover:bg-purple-500 shadow-md shadow-purple-600/20 transition-all">
                <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Atualizar Diagnóstico</span>
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Cards de Telemetria Quantitativa --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Colaboradores</span>
            <div class="mt-4 text-3xl font-black text-white">{{ $metrics['totalUsers'] }}</div>
            <p class="text-xs text-slate-500 mt-1">Colaboradores ativos na plataforma</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Taxa Estimada de Conclusão</span>
            <div class="mt-4 text-3xl font-black text-emerald-400">{{ $metrics['completionRate'] }}%</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['completedLessons'] }} aulas concluídas no total</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <span class="text-xs font-semibold uppercase tracking-wider text-rose-400">Alunos em Alto Risco</span>
            <div class="mt-4 text-3xl font-black text-rose-400">{{ $metrics['highRiskCount'] }}</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['highRiskPercent'] }}% do quadro total</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-400">Média Geral de Risco</span>
            <div class="mt-4 text-3xl font-black text-indigo-400">{{ $metrics['avgRiskScore'] }}/100</div>
            <p class="text-xs text-slate-500 mt-1">Score ponderado corporativo</p>
        </div>
    </div>

    {{-- Diagnóstico da IA com OpenRouter Fallback --}}
    <div class="p-6 rounded-2xl bg-gradient-to-br from-purple-950/20 via-slate-900 to-slate-950 border border-purple-500/30 shadow-xl relative overflow-hidden">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-400 animate-pulse"></span>
                <h2 class="text-base font-bold text-white">Parecer Executivo Alvora AI</h2>
            </div>
            @if ($modelUsed)
                <span class="text-xs text-slate-400 font-mono">
                    Engine: <strong class="text-purple-400">{{ $modelUsed }}</strong>
                </span>
            @endif
        </div>

        <div class="prose prose-invert max-w-none text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line">
            {{ $executiveSummary ?? 'Gerando parecer executivo com base nos dados mais recentes...' }}
        </div>
    </div>
</div>
