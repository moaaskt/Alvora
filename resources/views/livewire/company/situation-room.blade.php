<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Sala de Situação</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Painel de telemetria preditiva de risco de evasão e intervenção ativa Alvora AI.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="recalculateAllScores" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700/80 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <svg wire:loading.class="animate-spin" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Recomputar Scores</span>
            </button>
        </div>
    </div>

    {{-- Feedback Message --}}
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Card 1: Alto Risco --}}
        <div class="p-5 rounded-2xl bg-gradient-to-br from-rose-950/40 via-slate-900/80 to-slate-950 border border-rose-500/30 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-400">Alto Risco de Evasão</span>
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-white">{{ $highRiskCount }}</span>
                <span class="text-sm font-semibold text-rose-400">({{ $highRiskPercent }}%)</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Colaboradores com score de risco > 70</p>
        </div>

        {{-- Card 2: Médio Risco --}}
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-amber-500/20">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Atenção / Médio Risco</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-white">{{ $mediumRiskCount }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Score entre 40 e 69</p>
        </div>

        {{-- Card 3: Baixo Risco / Estáveis --}}
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-emerald-500/20">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Engajados / Baixo Risco</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-white">{{ $lowRiskCount }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Score entre 0 e 39</p>
        </div>

        {{-- Card 4: Intervenções no Mês --}}
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-purple-500/20">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-400">Intervenções Alvora AI</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-white">{{ $interventionsCount }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Mensagens proativas enviadas este mês</p>
        </div>
    </div>

    {{-- Heatmap Visual Matrix --}}
    <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-white">Heatmap de Engajamento em Tempo Real</h2>
                <p class="text-xs text-slate-400 mt-0.5">Cores indicam a probabilidade preditiva de evasão do colaborador.</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-300">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span> 0-39 Seguro</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500"></span> 40-69 Atenção</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-500"></span> 70-100 Crítico</span>
            </div>
        </div>

        @if ($totalStudents === 0)
            <div class="text-center py-8 text-sm text-slate-500">Nenhum colaborador matriculado encontrado para exibir no heatmap.</div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2.5">
                @foreach ($students as $student)
                    @php
                        $score = $student->latestRiskScore?->score ?? 0;
                        $colorClass = match (true) {
                            $score >= 70 => 'bg-rose-500/20 border-rose-500 text-rose-300 hover:bg-rose-500/30',
                            $score >= 40 => 'bg-amber-500/20 border-amber-500 text-amber-300 hover:bg-amber-500/30',
                            default => 'bg-emerald-500/20 border-emerald-500 text-emerald-300 hover:bg-emerald-500/30',
                        };
                    @endphp
                    <div class="p-2.5 rounded-xl border flex flex-col justify-between transition-all cursor-pointer {{ $colorClass }}"
                         title="{{ $student->name }} - Score: {{ $score }}/100"
                         wire:click="openInterventionModal({{ $student->id }})">
                        <div class="text-xs font-semibold truncate">{{ $student->name }}</div>
                        <div class="mt-2 flex items-baseline justify-between text-[11px]">
                            <span class="font-bold">{{ $score }} pts</span>
                            <span class="text-[10px] opacity-75">{{ $student->latestRiskScore?->level ?? 'low' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Filter and Table Controls --}}
    <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <button wire:click="$set('filterLevel', 'all')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterLevel === 'all' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Todos ({{ $totalStudents }})</button>
                <button wire:click="$set('filterLevel', 'high')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterLevel === 'high' ? 'bg-rose-500 text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Críticos ({{ $highRiskCount }})</button>
                <button wire:click="$set('filterLevel', 'medium')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterLevel === 'medium' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Atenção ({{ $mediumRiskCount }})</button>
                <button wire:click="$set('filterLevel', 'low')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterLevel === 'low' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Engajados ({{ $lowRiskCount }})</button>
            </div>

            <div class="relative w-full sm:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar colaborador..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/50 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-700/60">
                    <tr>
                        <th class="py-3 px-4">Colaborador</th>
                        <th class="py-3 px-4">Score de Risco</th>
                        <th class="py-3 px-4">Fatores Ponderados</th>
                        <th class="py-3 px-4">Status de Intervenção</th>
                        <th class="py-3 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($students as $student)
                        @php
                            $scoreRecord = $student->latestRiskScore;
                            $score = $scoreRecord?->score ?? 0;
                            $breakdown = $scoreRecord?->breakdown ?? [];
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 font-medium text-white">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-emerald-400">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div>{{ $student->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm {{ $score >= 70 ? 'text-rose-400' : ($score >= 40 ? 'text-amber-400' : 'text-emerald-400') }}">
                                        {{ $score }}/100
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full uppercase font-bold {{ $score >= 70 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($score >= 40 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20') }}">
                                        {{ $scoreRecord?->level ?? 'low' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                    <span>Inat: {{ $breakdown['inactivity']['score'] ?? 0 }}%</span>
                                    <span>•</span>
                                    <span>Estag: {{ $breakdown['stagnation']['score'] ?? 0 }}%</span>
                                    <span>•</span>
                                    <span>Quiz: {{ $breakdown['quiz']['score'] ?? 0 }}%</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $latestIntervention = $student->interventions()->latest()->first();
                                @endphp
                                @if ($latestIntervention)
                                    <span class="inline-flex items-center gap-1.5 text-purple-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span>Enviada {{ $latestIntervention->created_at->diffForHumans() }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-500">Nenhuma intervenção</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button wire:click="openInterventionModal({{ $student->id }})" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                                    Intervir
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Nenhum colaborador encontrado com os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de Intervenção Manual --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl relative">
                <h3 class="text-lg font-bold text-white mb-2">Enviar Intervenção Personalizada</h3>
                <p class="text-xs text-slate-400 mb-4">Esta mensagem será entregue diretamente ao colaborador no painel de treinamentos.</p>

                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Mensagem de Apoio / Incentivo</label>
                    <textarea wire:model="manualMessage" rows="5" class="w-full p-3 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"></textarea>
                    @error('manualMessage') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button wire:click="closeModal" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendManualIntervention" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-950 bg-emerald-500 hover:bg-emerald-400 transition-colors">
                        Disparar Intervenção
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
