<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-gray-900/60 border border-white/5 p-6 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="{{ route('student.dashboard') }}" class="hover:text-white transition">Painel do Aluno</a>
                <span>&bull;</span>
                <span class="text-indigo-400">{{ $quiz->course->title }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">
                {{ $quiz->title }}
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">
                Nota mínima para aprovação: <strong class="text-emerald-400">{{ $quiz->passing_score }}%</strong>
            </p>
        </div>

        <div>
            <a
                href="{{ route('student.dashboard') }}"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white transition border border-white/5 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar aos Cursos
            </a>
        </div>
    </div>

    {{-- Notificações --}}
    @if (session('warning'))
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm flex items-center justify-between">
            <span>{{ session('warning') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-amber-400/60 hover:text-amber-300">✕</button>
        </div>
    @endif

    {{-- Banner de Resultado caso já tenha sido submetido --}}
    @if ($submitted)
        <div class="p-6 rounded-2xl border {{ $passed ? 'bg-emerald-950/20 border-emerald-500/30' : 'bg-rose-950/20 border-rose-500/30' }} flex flex-col sm:flex-row sm:items-center justify-between gap-6 shadow-xl">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    @if ($passed)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Aprovado ✓
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            Não Atingiu a Nota de Corte
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">Resultado da Tentativa</span>
                </div>
                <h2 class="text-2xl font-extrabold text-white">
                    Seu Aproveitamento: <span class="{{ $passed ? 'text-emerald-400' : 'text-rose-400' }}">{{ $score }}%</span>
                </h2>
                <p class="text-xs text-gray-300 max-w-xl">
                    @if ($passed)
                        Parabéns! Você demonstrou domínio dos conceitos e seu resultado foi computado no seu histórico corporativo.
                    @else
                        Você precisa de no mínimo {{ $quiz->passing_score }}% para aprovação. Revise as explicações abaixo e faça uma nova tentativa.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if (!$passed)
                    <button
                        type="button"
                        wire:click="retry"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Tentar Novamente
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Lista de Questões --}}
    <div class="space-y-6">
        @foreach ($quiz->questions as $index => $question)
            @php
                $selectedOptionId = $userAnswers[$question->id] ?? null;
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isQuestionCorrect = $submitted && $selectedOptionId == $correctOption?->id;
            @endphp

            <div class="bg-gray-900/60 border border-white/5 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">
                        Questão {{ $index + 1 }} de {{ $totalQuestions }}
                    </span>
                    @if ($submitted)
                        @if ($isQuestionCorrect)
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Resposta Correta
                            </span>
                        @else
                            <span class="text-xs font-bold text-rose-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Resposta Incorreta
                            </span>
                        @endif
                    @endif
                </div>

                <h3 class="text-base font-semibold text-white leading-relaxed">
                    {{ $question->question_text }}
                </h3>

                {{-- Alternativas --}}
                <div class="space-y-2 pt-2">
                    @foreach ($question->options as $option)
                        @php
                            $isSelected = $selectedOptionId == $option->id;
                            $cardClasses = 'border-white/5 hover:border-white/10 text-gray-300 bg-gray-950/40';

                            if ($submitted) {
                                if ($option->is_correct) {
                                    $cardClasses = 'border-emerald-500/40 bg-emerald-950/30 text-emerald-200 font-semibold';
                                } elseif ($isSelected && !$option->is_correct) {
                                    $cardClasses = 'border-rose-500/40 bg-rose-950/30 text-rose-200';
                                } else {
                                    $cardClasses = 'border-white/5 bg-gray-950/20 text-gray-500';
                                }
                            } elseif ($isSelected) {
                                $cardClasses = 'border-indigo-500 bg-indigo-950/40 text-white font-semibold ring-1 ring-indigo-500';
                            }
                        @endphp

                        <div
                            @if (!$submitted) wire:click="selectOption({{ $question->id }}, {{ $option->id }})" @endif
                            class="p-4 rounded-xl border transition flex items-center justify-between gap-3 text-sm {{ $cardClasses }} {{ !$submitted ? 'cursor-pointer hover:bg-gray-800/40' : '' }}"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0 text-xs
                                    {{ $isSelected ? 'border-indigo-400 bg-indigo-600 text-white' : 'border-gray-600 text-gray-400' }}">
                                    @if ($submitted && $option->is_correct)
                                        ✓
                                    @elseif ($isSelected)
                                        •
                                    @endif
                                </span>
                                <span>{{ $option->option_text }}</span>
                            </div>

                            @if ($submitted && $option->is_correct)
                                <span class="text-xs text-emerald-400 font-semibold">Correta</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Feedback Pedagógico (Após envio) --}}
                @if ($submitted && $question->explanation)
                    <div class="mt-4 p-3.5 rounded-xl bg-gray-950/80 border border-white/5 text-xs text-gray-300 space-y-1">
                        <span class="font-bold text-indigo-400 flex items-center gap-1">
                            💡 Justificativa Pedagógica:
                        </span>
                        <p class="leading-relaxed">{{ $question->explanation }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Botão de Submissão --}}
    @if (!$submitted)
        <div class="p-6 bg-gray-900/60 border border-white/5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <span class="text-xs text-gray-400">
                Respondidas: <strong>{{ count($userAnswers) }}</strong> de <strong>{{ $totalQuestions }}</strong> questões
            </span>

            <button
                type="button"
                wire:click="submit"
                class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-xl shadow-indigo-600/30 transition flex items-center justify-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Finalizar Tentativa e Ver Nota
            </button>
        </div>
    @endif
</div>
