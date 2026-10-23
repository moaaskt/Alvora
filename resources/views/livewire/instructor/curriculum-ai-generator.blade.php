<div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm text-slate-800">
    <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <svg class="w-5 h-5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>Gerador Curricular Inteligente</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold border border-blue-200/60 font-mono">OpenRouter Cascata</span>
                </h2>
                <p class="text-xs text-slate-500">Gere automaticamente módulos e aulas completos a partir de um comando em linguagem natural.</p>
            </div>
        </div>
    </div>

    {{-- Form de Entrada --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tema / Assunto do Curso</label>
            <input type="text"
                   wire:model="topic"
                   placeholder="Ex: Arquitetura de Microsserviços e Observabilidade..."
                   class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs transition-all">
            @error('topic') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Público-Alvo Corporativo</label>
            <input type="text"
                   wire:model="targetAudience"
                   placeholder="Ex: Engenheiros de Software, Líderes Técnicos..."
                   class="w-full text-xs px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs transition-all">
            @error('targetAudience') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="flex items-center justify-between mb-2">
        <button wire:click="generate"
                wire:loading.attr="disabled"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-sm cursor-pointer disabled:opacity-50">
            <svg wire:loading.class="animate-spin" class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
            <span wire:loading.remove>Gerar Estrutura com IA</span>
            <span wire:loading>Processando via OpenRouter...</span>
        </button>

        @if ($modelUsed)
            <span class="text-[11px] text-slate-400">
                Modelo: <strong class="text-blue-600 font-mono">{{ $modelUsed }}</strong>
            </span>
        @endif
    </div>

    {{-- Pré-visualização da Ementa Gerada --}}
    @if ($generatedCurriculum)
        <div class="mt-5 p-5 rounded-xl bg-slate-50 border border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-4 border-b border-slate-200/80">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $generatedCurriculum['title'] ?? 'Ementa Gerada' }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $generatedCurriculum['description'] ?? '' }}</p>
                </div>
                <button wire:click="applyToCourse"
                        type="button"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors flex-shrink-0 shadow-sm cursor-pointer">
                    Aplicar à Grade do Curso
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($generatedCurriculum['modules'] ?? [] as $mIndex => $mod)
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                        <div class="text-xs font-bold text-slate-800 mb-2">
                            {{ $mod['title'] }}
                        </div>
                        <div class="space-y-1.5 pl-3 border-l-2 border-blue-500/30">
                            @foreach ($mod['lessons'] ?? [] as $lIndex => $les)
                                <div class="flex items-center justify-between text-xs text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 font-mono">{{ $lIndex + 1 }}.</span>
                                        <span>{{ $les['title'] }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $les['duration_minutes'] ?? 15 }} min</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
