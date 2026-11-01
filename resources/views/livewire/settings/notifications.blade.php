<div class="{{ auth()->user()?->isStudent() ? 'flex flex-col lg:flex-row items-start gap-6 lg:gap-8' : 'max-w-4xl mx-auto' }}">
    @if(auth()->user()?->isStudent())
        <x-student.sidebar active="profile" />
    @endif

    <div class="{{ auth()->user()?->isStudent() ? 'flex-1 min-w-0 w-full' : 'w-full' }}">
        @include('livewire.settings.partials.header')

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form wire:submit.prevent="savePreferences" class="space-y-6 max-w-xl">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h2 class="text-base font-bold text-slate-900 leading-tight">
                    Preferências de Notificações
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Escolha quais tipos de alertas e atualizações pedagógicas você deseja receber em seu e-mail.
                </p>
            </div>

            <div class="space-y-4">
                <!-- Toggle 1: Comunicados e Alertas Gerais -->
                <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                        <span class="text-sm font-semibold text-slate-900 block">Comunicados e Avisos do Sistema</span>
                        <span class="text-xs text-slate-500 mt-0.5 block">Notificações operacionais, novidades da plataforma e avisos corporativos.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                        <input type="checkbox" wire:model="email_announcements" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <!-- Toggle 2: Cursos e Trilhas -->
                <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                        <span class="text-sm font-semibold text-slate-900 block">Atualizações de Cursos & Trilhas</span>
                        <span class="text-xs text-slate-500 mt-0.5 block">Novos módulos publicados, lições adicionadas e lembretes de progresso.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                        <input type="checkbox" wire:model="email_course_updates" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <!-- Toggle 3: Avaliações e Certificados -->
                <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                        <span class="text-sm font-semibold text-slate-900 block">Certificados e Avaliações</span>
                        <span class="text-xs text-slate-500 mt-0.5 block">Avisos de emissão de certificados oficiais e resultados de quizzes.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                        <input type="checkbox" wire:model="email_assessments" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="savePreferences">Salvar Preferências</span>
                    <span wire:loading wire:target="savePreferences" class="flex items-center gap-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Salvando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>

