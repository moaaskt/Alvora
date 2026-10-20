<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-emerald-400 p-[2px] shadow-lg shadow-indigo-600/30 mb-2">
                <div class="w-full h-full bg-[#090D16] rounded-[14px] flex items-center justify-center font-black text-2xl text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">
                    A
                </div>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Portal Corporativo</h2>
            <p class="text-slate-400 text-sm">
                Acesse o ambiente de capacitação corporativa da sua organização.
            </p>
        </div>

        <!-- Card de Login -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
            <form wire:submit="login" class="space-y-6">
                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">E-mail Corporativo</label>
                    <input type="email" id="email" wire:model="email" autofocus placeholder="seu.nome@empresa.com.br"
                           class="w-full bg-slate-950/70 border @error('email') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Senha -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300">Senha de Acesso</label>
                    </div>
                    <input type="password" id="password" wire:model="password" placeholder="••••••••"
                           class="w-full bg-slate-950/70 border @error('password') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    @error('password')
                        <p class="text-rose-400 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-xs text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900 mr-2">
                        Lembrar acesso neste dispositivo
                    </label>
                </div>

                <!-- Botão de Login -->
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.01] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75">
                    <span wire:loading.remove>Entrar na Plataforma</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Autenticando...
                    </span>
                </button>
            </form>
        </div>

        <!-- Rodapé do Card -->
        <div class="text-center text-xs text-slate-400 space-y-2">
            <p>
                Sua empresa ainda não é cliente Alvora? 
                <a href="{{ url('/#demonstracao') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold underline underline-offset-4">
                    Solicite uma demonstração
                </a>
            </p>
            <p class="text-slate-500 text-[11px]">
                Acesso seguro protegido por autenticação de sessão e isolamento de dados.
            </p>
        </div>
    </div>
</div>
