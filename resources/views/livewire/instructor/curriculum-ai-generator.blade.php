<div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 text-slate-100">
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span>Gerador Curricular Inteligente</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono">OpenRouter Cascata</span>
                </h2>
                <p class="text-xs text-slate-400">Gere automaticamente módulos e aulas completos a partir de um comando em linguagem natural.</p>
            </div>
        </div>
    </div>

    {{-- Form de Entrada --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tema / Assunto do Curso</label>
            <input type="text"
                   wire:model="topic"
                   placeholder="Ex: Arquitetura de Microsserviços e Observabilidade..."
                   class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-purple-500">
            @error('topic') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Público-Alvo Corporativo</label>
            <input type="text"
                   wire:model="targetAudience"
                   placeholder="Ex: Engenheiros de Software, Líderes Técnicos..."
                   class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-purple-500">
            @error('targetAudience') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="flex items-center justify-between mb-6">
        <button wire:click="generate"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-purple-600 hover:bg-purple-500 transition-all shadow-md shadow-purple-600/20 disabled:opacity-50">
            <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span wire:loading.remove>Gerar Estrutura com IA</span>
            <span wire:loading>Processando via OpenRouter...</span>
        </button>

        @if ($modelUsed)
            <span class="text-[11px] text-slate-400">
                Modelo utilizado: <strong class="text-purple-400 font-mono">{{ $modelUsed }}</strong>
            </span>
        @endif
    </div>

    {{-- Pré-visualização da Ementa Gerada --}}
    @if ($generatedCurriculum)
        <div class="mt-6 p-5 rounded-2xl bg-slate-950/70 border border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white">{{ $generatedCurriculum['title'] ?? 'Ementa Gerada' }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $generatedCurriculum['description'] ?? '' }}</p>
                </div>
                <button wire:click="applyToCourse"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 transition-colors flex-shrink-0">
                    Aplicar à Grade do Curso
                </button>
            </div>

            <div class="space-y-4">
                @foreach ($generatedCurriculum['modules'] ?? [] as $mIndex => $mod)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80">
                        <div class="text-xs font-bold text-purple-300 mb-2">
                            {{ $mod['title'] }}
                        </div>
                        <div class="space-y-2 pl-3 border-l-2 border-slate-800">
                            @foreach ($mod['lessons'] ?? [] as $lIndex => $les)
                                <div class="flex items-center justify-between text-xs text-slate-300">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 font-mono">{{ $lIndex + 1 }}.</span>
                                        <span>{{ $les['title'] }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $les['duration_minutes'] ?? 15 }} min</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
