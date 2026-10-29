<div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
    <!-- LEFT SIDEBAR (Transient Quiz state: none active) -->
    <x-student.sidebar active="" />

    <!-- CONTENT AREA (Centered, max-width 768px, no second column) -->
    <div class="flex-1 max-w-[768px] mx-auto w-full pb-28 space-y-6">

        <!-- 1. TOP CARD (Header & Context) -->
        <section class="bg-edtech-surface rounded-2xl border border-edtech-border shadow-xs p-6">
            <!-- Breadcrumb / Back Link -->
            <a href="{{ route('student.player', ['course' => $quiz->course->slug]) }}"
               class="inline-flex items-center gap-2 text-[13px] text-edtech-muted hover:text-edtech-blue transition-colors mb-3 group">
                <x-icon name="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-0.5 shrink-0" />
                <span>Voltar ao Curso</span>
            </a>

            <!-- Quiz Title -->
            <h1 class="text-[22px] font-semibold text-edtech-text leading-snug">
                {{ $quiz->title }}
            </h1>

            <!-- Approval Requirement Line -->
            <div class="flex items-center gap-2 text-[14px] text-edtech-muted mt-2">
                <x-icon name="award" class="w-4 h-4 text-edtech-blue shrink-0" />
                <span>Nota mínima para aprovação: {{ $quiz->passing_score }}%</span>
            </div>
        </section>

        <!-- Flash Warnings / Notifications -->
        @if (session('warning'))
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[14px] flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <x-icon name="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0" />
                    <span class="font-medium">{{ session('warning') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="p-1 text-amber-700 hover:text-amber-950 rounded-lg transition-colors cursor-pointer" aria-label="Fechar">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        <!-- 2. RESULT BANNER (Rendered only after submission) -->
        @if ($submitted)
            <section class="space-y-4">
                @if ($passed)
                    <!-- PASS BANNER -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-alvora-emerald shrink-0">
                                <x-icon name="check-circle" class="w-6 h-6 text-alvora-emerald" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[20px] font-semibold text-emerald-700 leading-tight">Aprovado</span>
                                </div>
                                <p class="text-[14px] text-edtech-muted mt-0.5">
                                    Você obteve {{ $score }}% de aproveitamento.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('student.player', ['course' => $quiz->course->slug]) }}"
                           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 font-medium text-[14px] transition-colors shrink-0">
                            Voltar ao Curso
                        </a>
                    </div>
                @else
                    <!-- FAIL BANNER -->
                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                                <x-icon name="x-circle" class="w-6 h-6 text-rose-600" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[20px] font-semibold text-rose-600 leading-tight">Não aprovado</span>
                                </div>
                                <p class="text-[14px] text-edtech-muted mt-0.5">
                                    Você obteve {{ $score }}% de aproveitamento.
                                </p>
                            </div>
                        </div>

                        <button type="button"
                                wire:click="retry"
                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-edtech-blue hover:bg-edtech-blue-dark text-white font-medium text-[14px] transition-colors shrink-0 cursor-pointer shadow-xs">
                            Tentar Novamente
                        </button>
                    </div>
                @endif
            </section>
        @endif

        <!-- 3. QUESTION CARDS STACK -->
        <div class="space-y-6">
            @foreach ($quiz->questions as $index => $question)
                @php
                    $selectedOptionId = $userAnswers[$question->id] ?? null;
                    $correctOption = $question->options->firstWhere('is_correct', true);
                @endphp

                <article class="bg-edtech-surface rounded-2xl border border-edtech-border shadow-xs p-6">
                    <header class="mb-3">
                        <span class="text-[13px] text-edtech-muted font-medium">
                            Questão {{ $index + 1 }} de {{ $totalQuestions }}
                        </span>
                    </header>

                    <h2 class="text-[16px] font-semibold text-edtech-text leading-relaxed mb-5">
                        {{ $question->question_text }}
                    </h2>

                    <!-- Answer Options -->
                    <div class="space-y-3">
                        @foreach ($question->options as $option)
                            @php
                                $isSelected = ($selectedOptionId == $option->id);
                                $isCorrect = (bool) $option->is_correct;
                            @endphp

                            @if (!$submitted)
                                {{-- Pre-submission option --}}
                                <button type="button"
                                        wire:click="selectOption({{ $question->id }}, {{ $option->id }})"
                                        class="group w-full flex items-center min-h-[44px] p-4 rounded-2xl border text-left transition-colors cursor-pointer {{ $isSelected ? 'border-2 border-edtech-blue bg-edtech-blue-light' : 'border-edtech-border bg-edtech-surface hover:bg-edtech-blue-light hover:border-blue-200' }}">
                                    @if ($isSelected)
                                        <span class="w-6 h-6 rounded-full bg-edtech-blue flex items-center justify-center mr-3.5 shrink-0">
                                            <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                                        </span>
                                        <span class="text-[15px] font-semibold text-edtech-text leading-normal">
                                            {{ $option->option_text }}
                                        </span>
                                    @else
                                        <span class="w-6 h-6 rounded-full border-2 border-slate-300 group-hover:border-edtech-blue flex items-center justify-center mr-3.5 shrink-0 transition-colors"></span>
                                        <span class="text-[15px] text-edtech-text leading-normal">
                                            {{ $option->option_text }}
                                        </span>
                                    @endif
                                </button>
                            @else
                                {{-- Post-submission option (Correction mode) --}}
                                @if ($isCorrect)
                                    {{-- Correct Option: #F0FDF4 bg, #BBF7D0 border, check icon green, text green semibold --}}
                                    <div class="flex items-center min-h-[44px] p-4 rounded-2xl border border-emerald-200 bg-emerald-50">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center mr-3.5 shrink-0">
                                            <x-icon name="check" class="w-3.5 h-3.5 stroke-[2.5]" />
                                        </div>
                                        <span class="text-[15px] font-semibold text-emerald-800 leading-normal">
                                            {{ $option->option_text }}
                                        </span>
                                    </div>
                                @elseif ($isSelected && !$isCorrect)
                                    {{-- Selected Wrong Option: #FEF2F2 bg, #FECACA border, x icon red, text red --}}
                                    <div class="flex items-center min-h-[44px] p-4 rounded-2xl border border-rose-200 bg-rose-50">
                                        <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center mr-3.5 shrink-0">
                                            <x-icon name="x" class="w-3.5 h-3.5 stroke-[2.5]" />
                                        </div>
                                        <span class="text-[15px] font-medium text-rose-700 leading-normal">
                                            {{ $option->option_text }}
                                        </span>
                                    </div>
                                @else
                                    {{-- Unselected Neutral Option: white bg, no icon, text slate dimmed --}}
                                    <div class="flex items-center min-h-[44px] p-4 rounded-2xl border border-edtech-border bg-edtech-surface opacity-60">
                                        <div class="w-6 h-6 mr-3.5 shrink-0"></div>
                                        <span class="text-[15px] text-edtech-muted leading-normal">
                                            {{ $option->option_text }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>

                    <!-- Pedagogical Explanation Box (Shown only after submission if explanation exists) -->
                    @if ($submitted && !empty($question->explanation))
                        <div class="mt-5 p-4 rounded-2xl bg-edtech-bg border border-edtech-border flex items-start gap-3">
                            <x-icon name="info" class="w-5 h-5 text-edtech-blue shrink-0 mt-0.5" />
                            <div class="min-w-0">
                                <span class="text-[13px] font-semibold text-edtech-text block mb-1">Justificativa</span>
                                <p class="text-[14px] text-edtech-muted leading-relaxed">
                                    {{ $question->explanation }}
                                </p>
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <!-- 4. STICKY BOTTOM BAR (Hidden after submission) -->
        @if (!$submitted)
            @php
                $answeredCount = count($userAnswers);
                $isAllAnswered = ($totalQuestions > 0 && $answeredCount >= $totalQuestions);
                $progressPercent = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;
            @endphp

            <aside class="sticky bottom-0 z-30 bg-edtech-surface border-t border-edtech-border shadow-md py-4 px-4 sm:px-6 rounded-2xl mt-8">
                <div class="max-w-[768px] mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Left: Counter & Progress bar -->
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[14px] text-edtech-muted font-medium">
                            Respondidas: <strong class="text-edtech-text font-semibold">{{ $answeredCount }}</strong> de <strong class="text-edtech-text font-semibold">{{ $totalQuestions }}</strong> questões
                        </span>
                        <div class="w-full sm:w-64 h-1.5 bg-edtech-border rounded-full overflow-hidden">
                            <div class="h-full bg-edtech-blue rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%;"></div>
                        </div>
                    </div>

                    <!-- Right: Submit Action Button -->
                    <div class="flex items-center">
                        <button type="button"
                                wire:click="submit"
                                @if (!$isAllAnswered) disabled @endif
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl font-semibold text-[14px] shadow-xs transition-colors duration-200 {{ $isAllAnswered ? 'bg-edtech-blue hover:bg-edtech-blue-dark text-white cursor-pointer' : 'bg-edtech-blue text-white opacity-50 cursor-not-allowed' }}">
                            <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
                            <span>Finalizar e Ver Resultado</span>
                        </button>
                    </div>
                </div>
            </aside>
        @endif

    </div>
</div>
