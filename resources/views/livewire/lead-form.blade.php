<div class="relative bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 lg:p-10 shadow-sm">
    @if ($submitted)
        <div class="text-center py-10 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="font-bold text-2xl text-slate-900">Solicitação Recebida com Sucesso</h3>
            <p class="text-slate-600 text-sm max-w-md mx-auto leading-relaxed">
                Agradecemos o contato. Um consultor corporativo da Alvora entrará em contato em menos de 24 horas úteis para apresentar o diagnóstico preditivo adaptado ao seu time.
            </p>
            <div class="pt-4">
                <button wire:click="resetForm" type="button" class="inline-flex items-center text-xs uppercase tracking-wider font-semibold text-blue-600 hover:text-blue-700 cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Enviar nova solicitação
                </button>
            </div>
        </div>
    @else
        <div class="mb-7">
            <span class="text-[11px] uppercase tracking-[0.18em] text-blue-600 font-semibold">Apresentação Executiva</span>
            <h3 class="font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight mt-1.5">
                Agende uma Apresentação Corporativa
            </h3>
            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                Conheça na prática como o Score de Risco de Evasão antecipa a desistência e eleva as taxas de conclusão do treinamento da sua empresa.
            </p>
        </div>

        <form wire:submit="submit" class="space-y-4 sm:space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                <!-- Nome -->
                <div>
                    <label for="lead_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nome Completo *</label>
                    <input type="text" id="lead_name" wire:model="name" placeholder="Ex: Carlos Silva"
                           class="w-full bg-slate-50/80 border @error('name') border-rose-400 focus:ring-rose-400 @else border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 @enderror rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all">
                    @error('name')
                        <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Corporativo -->
                <div>
                    <label for="lead_email" class="block text-xs font-semibold text-slate-700 mb-1.5">E-mail Corporativo *</label>
                    <input type="email" id="lead_email" wire:model="email" placeholder="carlos@empresa.com.br"
                           class="w-full bg-slate-50/80 border @error('email') border-rose-400 focus:ring-rose-400 @else border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 @enderror rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all">
                    @error('email')
                        <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                <!-- Empresa -->
                <div>
                    <label for="lead_company" class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa *</label>
                    <input type="text" id="lead_company" wire:model="company" placeholder="Sua Empresa S.A."
                           class="w-full bg-slate-50/80 border @error('company') border-rose-400 focus:ring-rose-400 @else border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 @enderror rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all">
                    @error('company')
                        <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Cargo / Função -->
                <div>
                    <label for="lead_role" class="block text-xs font-semibold text-slate-700 mb-1.5">Cargo / Departamento</label>
                    <input type="text" id="lead_role" wire:model="role" placeholder="Ex: Diretor de RH, Head de T&D"
                           class="w-full bg-slate-50/80 border border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all">
                </div>
            </div>

            <!-- Número de Colaboradores -->
            <div>
                <label for="lead_employees" class="block text-xs font-semibold text-slate-700 mb-1.5">Tamanho da Equipe a Capacitar</label>
                <select id="lead_employees" wire:model="employees_count"
                        class="w-full bg-slate-50/80 border border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 transition-all cursor-pointer">
                    <option value="1-20">Até 20 colaboradores (Starter B2B)</option>
                    <option value="21-100">21 a 100 colaboradores (Plano Pro + IA Preditiva)</option>
                    <option value="101-500">101 a 500 colaboradores (Enterprise Academy)</option>
                    <option value="500+">Mais de 500 colaboradores (Customizado Corporativo)</option>
                </select>
            </div>

            <!-- Mensagem Opcional -->
            <div>
                <label for="lead_message" class="block text-xs font-semibold text-slate-700 mb-1.5">Objetivos ou Desafios Principais (Opcional)</label>
                <textarea id="lead_message" wire:model="message" rows="3" placeholder="Ex: Desejamos reduzir o abandono no onboarding técnico de líderes..."
                          class="w-full bg-slate-50/80 border border-slate-200 focus:ring-blue-600/20 focus:border-blue-600 rounded-xl px-3.5 py-2.5 sm:py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all resize-y"></textarea>
            </div>

            <!-- Botão de Envio -->
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 px-6 rounded-xl font-semibold text-xs uppercase tracking-wider text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-wait">
                <span wire:loading.remove>Solicitar Apresentação Corporativa</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Enviando solicitação...
                </span>
            </button>

            <p class="text-[11px] text-center text-slate-500 mt-2 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Privacidade garantida em estrita conformidade com a LGPD.</span>
            </p>
        </form>
    @endif
</div>

