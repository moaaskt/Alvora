<div class="relative bg-gradient-to-b from-slate-900/90 to-slate-900/50 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 lg:p-10 shadow-2xl shadow-indigo-950/30">
    @if ($submitted)
        <div class="text-center py-10 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white">Solicitação Recebida com Sucesso!</h3>
            <p class="text-slate-400 text-sm max-w-md mx-auto">
                Obrigado pelo interesse. Um especialista Alvora entrará em contato em menos de 24 horas para apresentar uma demonstração personalizada para sua organização.
            </p>
            <div class="pt-4">
                <button wire:click="resetForm" type="button" class="inline-flex items-center text-sm font-semibold text-indigo-400 hover:text-indigo-300">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Enviar nova solicitação
                </button>
            </div>
        </div>
    @else
        <div class="mb-8">
            <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Agende uma Apresentação Executiva</h3>
            <p class="text-slate-400 text-sm mt-2">
                Descubra como nossa IA preditiva identifica padrões de desistência e aumenta a conclusão dos cursos do seu time.
            </p>
        </div>

        <form wire:submit="submit" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nome -->
                <div>
                    <label for="lead_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Seu Nome Completo *</label>
                    <input type="text" id="lead_name" wire:model="name" placeholder="Ex: Carlos Silva"
                           class="w-full bg-slate-950/60 border @error('name') border-rose-500 @else border-slate-700/80 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('name')
                        <p class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Corporativo -->
                <div>
                    <label for="lead_email" class="block text-xs font-semibold text-slate-300 mb-1.5">E-mail Corporativo *</label>
                    <input type="email" id="lead_email" wire:model="email" placeholder="carlos@empresa.com.br"
                           class="w-full bg-slate-950/60 border @error('email') border-rose-500 @else border-slate-700/80 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Empresa -->
                <div>
                    <label for="lead_company" class="block text-xs font-semibold text-slate-300 mb-1.5">Nome da Empresa *</label>
                    <input type="text" id="lead_company" wire:model="company" placeholder="Sua Empresa S.A."
                           class="w-full bg-slate-950/60 border @error('company') border-rose-500 @else border-slate-700/80 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('company')
                        <p class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Cargo / Função -->
                <div>
                    <label for="lead_role" class="block text-xs font-semibold text-slate-300 mb-1.5">Cargo / Departamento</label>
                    <input type="text" id="lead_role" wire:model="role" placeholder="Ex: Diretor de RH, Head de T&D"
                           class="w-full bg-slate-950/60 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Número de Colaboradores -->
            <div>
                <label for="lead_employees" class="block text-xs font-semibold text-slate-300 mb-1.5">Número de Colaboradores para Capacitar</label>
                <select id="lead_employees" wire:model="employees_count"
                        class="w-full bg-slate-950/60 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <option value="1-20" class="bg-slate-900 text-white">Até 20 colaboradores (Starter)</option>
                    <option value="21-100" class="bg-slate-900 text-white">21 a 100 colaboradores (Pro)</option>
                    <option value="101-500" class="bg-slate-900 text-white">101 a 500 colaboradores (Enterprise)</option>
                    <option value="500+" class="bg-slate-900 text-white">Mais de 500 colaboradores (Custom)</option>
                </select>
            </div>

            <!-- Mensagem Opcional -->
            <div>
                <label for="lead_message" class="block text-xs font-semibold text-slate-300 mb-1.5">Principais Desafios de Treinamento (Opcional)</label>
                <textarea id="lead_message" wire:model="message" rows="3" placeholder="Ex: Alta taxa de abandono nos cursos de onboarding..."
                          class="w-full bg-slate-950/60 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-wait">
                <span wire:loading.remove>Solicitar Demonstração</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Processando...
                </span>
            </button>

            <p class="text-[11px] text-center text-slate-500 mt-2">
                🔒 Seus dados estão seguros e protegidos em conformidade rigorosa com a LGPD.
            </p>
        </form>
    @endif
</div>
