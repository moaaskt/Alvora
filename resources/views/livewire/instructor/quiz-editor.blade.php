<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']) }}" class="hover:text-white transition flex items-center gap-1">
                    &larr; Voltar para Avaliações & Quizzes
                </a>
                <span>&bull;</span>
                <span class="text-indigo-400 font-semibold">{{ $course->title }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                {{ $quiz ? 'Editar Avaliação / Quiz' : 'Nova Avaliação / Quiz' }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']) }}"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition border border-slate-700"
            >
                Cancelar
            </a>
            <button
                type="button"
                wire:click="save"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Salvar Avaliação
            </button>
        </div>
    </div>

    {{-- Dados Gerais do Quiz --}}
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-5 shadow-xl">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            Configurações da Avaliação
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-semibold text-slate-300">Título do Quiz *</label>
                <input
                    type="text"
                    wire:model="title"
                    placeholder="Ex: Avaliação de Fixação — Módulo 1"
                    class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                @error('title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-300">Nota de Corte (% de acerto) *</label>
                <div class="relative">
                    <input
                        type="number"
                        min="1"
                        max="100"
                        wire:model="passing_score"
                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <span class="absolute right-4 top-2.5 text-xs text-slate-500 font-bold">%</span>
                </div>
                @error('passing_score') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-3 space-y-1">
                <label class="block text-xs font-semibold text-slate-300">Vincular ao Módulo (Opcional)</label>
                <select
                    wire:model="module_id"
                    class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Avaliação Geral do Curso (Sem módulo específico)</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->title }}</option>
                    @endforeach
                </select>
                @error('module_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- Lista de Questões --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Questões de Múltipla Escolha ({{ count($questions) }})
            </h2>
            <button
                type="button"
                wire:click="addQuestion"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 hover:bg-indigo-500/20 transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Adicionar Questão
            </button>
        </div>

        @foreach ($questions as $qIndex => $question)
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-5 relative shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-600/20 text-indigo-300 border border-indigo-500/30">
                        Questão #{{ $qIndex + 1 }}
                    </span>
                    @if (count($questions) > 1)
                        <button
                            type="button"
                            wire:click="removeQuestion({{ $qIndex }})"
                            class="text-xs text-rose-400 hover:text-rose-300 transition cursor-pointer"
                        >
                            Remover Questão ✕
                        </button>
                    @endif
                </div>

                {{-- Enunciado --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-300">Enunciado da Pergunta *</label>
                    <textarea
                        wire:model="questions.{{ $qIndex }}.question_text"
                        rows="2"
                        placeholder="Ex: Qual é a principal vantagem de uma arquitetura orientada a microsserviços?"
                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl p-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                    @error("questions.{$qIndex}.question_text") <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                {{-- Alternativas --}}
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Alternativas (Selecione a resposta correta):
                        </label>
                        <button
                            type="button"
                            wire:click="addOption({{ $qIndex }})"
                            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer"
                        >
                            + Nova Alternativa
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($question['options'] as $oIndex => $option)
                            <div class="flex items-center gap-3 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800 hover:border-slate-700 transition">
                                <input
                                    type="radio"
                                    name="correct_option_{{ $qIndex }}"
                                    wire:click="setCorrectOption({{ $qIndex }}, {{ $oIndex }})"
                                    @checked($option['is_correct'])
                                    class="w-4 h-4 text-emerald-500 bg-slate-900 border-slate-700 focus:ring-emerald-500 cursor-pointer"
                                    title="Marcar como correta"
                                >
                                <input
                                    type="text"
                                    wire:model="questions.{{ $qIndex }}.options.{{ $oIndex }}.option_text"
                                    placeholder="Texto da alternativa..."
                                    class="flex-1 bg-transparent border-0 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-0 p-0"
                                >
                                @if (count($question['options']) > 2)
                                    <button
                                        type="button"
                                        wire:click="removeOption({{ $qIndex }}, {{ $oIndex }})"
                                        class="text-slate-500 hover:text-rose-400 p-1 cursor-pointer"
                                        title="Remover opção"
                                    >
                                        ✕
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Explicação Pedagógica (Feedback ao Aluno) --}}
                <div class="space-y-1 pt-2 border-t border-slate-800">
                    <label class="block text-xs font-semibold text-slate-400">
                        Explicação / Justificativa Pedagógica (Exibida ao aluno após a tentativa):
                    </label>
                    <input
                        type="text"
                        wire:model="questions.{{ $qIndex }}.explanation"
                        placeholder="Ex: Microsserviços permitem implantação independente e escalabilidade granular por domínio."
                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-300 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
            </div>
        @endforeach

        <div class="pt-4 flex justify-end">
            <button
                type="button"
                wire:click="save"
                class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-xl shadow-indigo-600/30 transition flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Salvar Avaliação do Curso
            </button>
        </div>
    </div>
</div>
