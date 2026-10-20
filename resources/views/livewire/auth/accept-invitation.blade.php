<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-md w-full space-y-8">
        @if($isInvalid)
            <!-- Estado de Convite Inválido / Expirado -->
            <div class="bg-slate-900/90 border border-rose-500/40 rounded-2xl p-8 text-center shadow-2xl backdrop-blur-xl space-y-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white">Convite Não Disponível</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                    {{ $errorMessage }}
                </p>
                <div class="pt-2">
                    <a href="{{ url('/') }}" class="inline-flex items-center text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                        &larr; Voltar para a página inicial
                    </a>
                </div>
            </div>
        @else
            <!-- Header do Convite Válido -->
            <div class="text-center space-y-2">
                <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-emerald-400 p-[2px] shadow-lg shadow-indigo-600/30 mb-2">
                    <div class="w-full h-full bg-[#090D16] rounded-[14px] flex items-center justify-center font-black text-2xl text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">
                        A
                    </div>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Ativar Seu Acesso</h2>
                <p class="text-slate-400 text-xs sm:text-sm">
                    Você foi convidado pela <strong class="text-white">{{ $company?->name }}</strong> para iniciar seus treinamentos na Alvora.
                </p>
            </div>

            <!-- Card do Formulário -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
                <form wire:submit="accept" class="space-y-5">
                    <!-- E-mail (Somente Leitura) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Seu E-mail Corporativo</label>
                        <input type="text" value="{{ $invitation->email }}" disabled
                               class="w-full bg-slate-950/40 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-400 cursor-not-allowed">
                    </div>

                    <!-- Nome Completo -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Seu Nome Completo *</label>
                        <input type="text" id="name" wire:model="name" autofocus placeholder="Ex: Roberto Dias"
                               class="w-full bg-slate-950/70 border @error('name') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                        @error('name')
                            <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Criar Senha de Acesso *</label>
                        <input type="password" id="password" wire:model="password" placeholder="Mínimo de 8 caracteres"
                               class="w-full bg-slate-950/70 border @error('password') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                        @error('password')
                            <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirmar Senha *</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Repita a senha criada"
                               class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>

                    <!-- Botão de Conclusão -->
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.01] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75">
                        <span wire:loading.remove>Concluir Cadastro e Acessar</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Criando seu acesso...
                        </span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
