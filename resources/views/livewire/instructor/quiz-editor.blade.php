<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- SUB-NAV / BREADCRUMBS TOP BAR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Voltar para Avaliações</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Home</span>
            <span class="text-slate-300">/</span>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="text-xs text-slate-500 hover:text-blue-600 font-medium transition-colors">
                {{ $course->title }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">{{ $quiz ? 'Editar Avaliação' : 'Nova Avaliação' }}</span>
        </div>

        @if($quiz)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-slate-100 text-slate-600 border border-slate-200/80">
                ID: #{{ $quiz->id }}
            </span>
        @endif
    </div>

    <!-- HEADER DA PÁGINA -->
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-slate-900 font-bold tracking-tight">
                {{ $quiz ? 'Editar Avaliação / Quiz: ' . $quiz->title : 'Nova Avaliação / Quiz de Fixação' }}
            </h1>
            <p class="text-xs text-slate-500">
                Configure a nota de corte, as questões de múltipla escolha e os gabaritos para aferir o aprendizado.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']) }}"
               class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                Cancelar
            </a>
            <button type="button"
                    wire:click="save"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer">
                <svg class="w-4 h-4 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                </svg>
                <span>Salvar Avaliação</span>
            </button>
        </div>
    </header>

    {{-- Dados Gerais do Quiz --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-5 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Configurações da Avaliação</h2>
                <p class="text-xs text-slate-400">Parâmetros de corte e vínculo curricular</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-semibold text-slate-700">Título do Quiz *</label>
                <input type="text"
                       wire:model="title"
                       placeholder="Ex: Avaliação de Fixação — Módulo 1"
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                @error('title') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700">Nota de Corte (% de acerto) *</label>
                <input type="number"
                       min="1"
                       max="100"
                       wire:model="passing_score"
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                @error('passing_score') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-3 space-y-1">
                <label class="block text-xs font-semibold text-slate-700">Vincular a um Módulo Específico (Opcional)</label>
                <div class="relative">
                    <select wire:model="module_id"
                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer shadow-2xs">
                        <option value="">Avaliação Geral do Curso (Sem módulo específico)</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod->id }}">{{ $mod->title }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Bloco de Questões --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Questões da Avaliação ({{ count($questions) }})</span>
            </h2>
            <button type="button"
                    wire:click="addQuestion"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition border border-blue-200/60 shadow-2xs">
                + Adicionar Pergunta
            </button>
        </div>

        @foreach($questions as $qIndex => $q)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm relative">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg">
                        Questão #{{ $qIndex + 1 }}
                    </span>
                    @if(count($questions) > 1)
                        <button type="button"
                                wire:click="removeQuestion({{ $qIndex }})"
                                class="text-slate-400 hover:text-rose-500 text-xs font-semibold transition">
                            Remover Questão ✕
                        </button>
                    @endif
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Enunciado da Questão *</label>
                    <textarea rows="2"
                              wire:model="questions.{{ $qIndex }}.question_text"
                              placeholder="Qual o objetivo principal do protocolo..."
                              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs"></textarea>
                </div>

                {{-- Alternativas da Questão --}}
                <div class="space-y-2 pt-2">
                    <label class="block text-xs font-semibold text-slate-600">Alternativas de Resposta (Selecione a correta no radio)</label>
                    <div class="space-y-2">
                        @foreach($q['options'] as $oIndex => $opt)
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                                <input type="radio"
                                       name="correct_option_{{ $qIndex }}"
                                       wire:click="setCorrectOption({{ $qIndex }}, {{ $oIndex }})"
                                       {{ $opt['is_correct'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                       title="Marcar como alternativa correta">

                                <input type="text"
                                       wire:model="questions.{{ $qIndex }}.options.{{ $oIndex }}.option_text"
                                       placeholder="Texto da alternativa..."
                                       class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500">

                                @if($opt['is_correct'])
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full shrink-0">
                                        Correta ✓
                                    </span>
                                @endif

                                @if(count($q['options']) > 2)
                                    <button type="button"
                                            wire:click="removeOption({{ $qIndex }}, {{ $oIndex }})"
                                            class="text-slate-400 hover:text-rose-500 p-1 text-xs">
                                        ✕
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            wire:click="addOption({{ $qIndex }})"
                            class="text-xs text-blue-600 hover:text-blue-700 font-semibold pt-1">
                        + Nova Opção de Resposta
                    </button>
                </div>

                <div class="space-y-1 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-500">Justificativa Pedagógica (Exibida após o colaborador responder)</label>
                    <input type="text"
                           wire:model="questions.{{ $qIndex }}.explanation"
                           placeholder="Explicação didática que reforça o aprendizado..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                </div>
            </div>
        @endforeach
    </div>

    {{-- Botões de Rodapé --}}
    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <a href="{{ route('instructor.courses.edit', ['course' => $course, 'tab' => 'quizzes']) }}"
           class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
            Cancelar
        </a>
        <button type="button"
                wire:click="save"
                class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm">
            Salvar Avaliação
        </button>
    </div>
</div>
