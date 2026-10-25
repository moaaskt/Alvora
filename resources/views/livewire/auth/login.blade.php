<div class="min-h-screen w-full flex flex-col lg:grid lg:grid-cols-12 bg-[#F8FAFC]">
    <!-- PAINEL INSTITUCIONAL (Desktop: Esquerda 5 cols | Mobile: Cabeçalho Compacto) -->
    <div class="lg:col-span-5 bg-[#0F172A] text-white flex flex-col justify-between p-6 sm:p-10 lg:p-12 relative overflow-hidden border-b lg:border-b-0 lg:border-r border-slate-800">
        <!-- Elementos gráficos discretos / Textura educacional e analítica -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 right-0 w-80 h-80 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Topo: Marca e Badge do Portal Corporativo -->
        <div class="relative z-10 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg p-1">
                <img src="{{ asset('images/logo-symbol.png') }}" alt="ALVORA" class="h-8 w-auto shrink-0 transition-transform duration-200 group-hover:scale-105">
                <div class="flex flex-col justify-center">
                    <span class="font-bold text-xl tracking-tight text-white group-hover:text-blue-400 transition-colors leading-none">ALVORA</span>
                    <span class="text-[9px] uppercase tracking-[0.18em] text-blue-300/80 font-semibold mt-0.5">Corporate EdTech</span>
                </div>
            </a>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/25 text-blue-300 text-xs font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                <span>Portal Corporativo</span>
            </div>
        </div>

        <!-- Centro Institucional (Visível no desktop, compacto no mobile) -->
        <div class="relative z-10 my-8 lg:my-auto max-w-lg hidden lg:block space-y-6">
            <div class="space-y-3">
                <span class="text-xs uppercase tracking-widest font-semibold text-blue-400">Desenvolvimento Empresarial com IA</span>
                <h2 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Aprendizagem corporativa contínua e retenção de talentos.
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Acesse o ambiente seguro da sua organização para acompanhar cursos, trilhas estratégicas de capacitação e métricas preditivas de engajamento em tempo real.
                </p>
            </div>

            <!-- Badges de Segurança e Inteligência -->
            <div class="pt-4 space-y-3 border-t border-slate-800/80">
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <div class="w-6 h-6 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span>Criptografia de sessão e isolamento multi-tenant seguro</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <div class="w-6 h-6 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span>Score Preditivo de Evasão e intervenções ativas por IA</span>
                </div>
            </div>
        </div>

        <!-- Rodapé do Painel Institucional -->
        <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-4 border-t border-slate-800/60">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-slate-300 hover:text-white transition-colors group">
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Voltar ao site institucional</span>
            </a>
            <span class="hidden sm:inline text-slate-500">Conformidade LGPD</span>
        </div>
    </div>

    <!-- ÁREA DO FORMULÁRIO (Desktop: Direita 7 cols | Mobile: Corpo Principal) -->
    <div class="lg:col-span-7 flex flex-col justify-center items-center px-4 sm:px-8 md:px-12 lg:px-16 py-10 lg:py-16">
        <div class="w-full max-w-md space-y-8">
            <!-- Cabeçalho do Formulário -->
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    Bem-vindo de volta
                </h1>
                <p class="text-slate-500 text-sm">
                    Informe suas credenciais corporativas para acessar o ambiente de capacitação da sua empresa.
                </p>
            </div>

            <!-- Card do Formulário -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-sm">
                <form wire:submit="login" class="space-y-5" novalidate>
                    <!-- E-mail Corporativo -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            E-mail Corporativo
                        </label>
                        <div class="relative">
                            <input type="email"
                                   id="email"
                                   wire:model="email"
                                   autofocus
                                   autocomplete="email"
                                   placeholder="nome@suaempresa.com.br"
                                   class="w-full bg-white border @error('email') border-rose-500 text-rose-900 focus:ring-rose-500/20 focus:border-rose-500 @else border-slate-300 text-slate-900 focus:ring-blue-600/20 focus:border-blue-600 @enderror rounded-xl px-3.5 py-2.5 text-sm placeholder-slate-400 focus:outline-none focus:ring-4 transition-all">
                        </div>
                        @error('email')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Senha de Acesso com Toggle Mostrar/Ocultar -->
                    <div x-data="{ showPassword: false, showRecoveryNotice: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-700">
                                Senha de Acesso
                            </label>
                            <!-- Orientação neutra e transparente sobre recuperação de credenciais corporativas -->
                            <button type="button" 
                                    @click="showRecoveryNotice = !showRecoveryNotice" 
                                    class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500/30 rounded px-1 transition-colors">
                                Esqueceu a senha?
                            </button>
                        </div>

                        <!-- Caixa Informativa Neutra sobre Redefinição de Senha -->
                        <div x-show="showRecoveryNotice" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="mb-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                            <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Recuperação de Acesso Corporativo</span>
                            </div>
                            <p class="leading-relaxed">
                                Por segurança organizacional, a redefinição de acesso é gerenciada pelo administrador da conta ou setor de RH da sua organização. Solicite uma atualização de acesso internamente.
                            </p>
                        </div>

                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   wire:model="password"
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full bg-white border @error('password') border-rose-500 text-rose-900 focus:ring-rose-500/20 focus:border-rose-500 @else border-slate-300 text-slate-900 focus:ring-blue-600/20 focus:border-blue-600 @enderror rounded-xl pl-3.5 pr-11 py-2.5 text-sm placeholder-slate-400 focus:outline-none focus:ring-4 transition-all">
                            
                            <!-- Botão Mostrar/Ocultar Senha -->
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Ocultar senha' : 'Exibir senha'"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 focus:outline-none focus:text-slate-800 rounded transition-colors">
                                <!-- Ícone Olho Aberto -->
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <!-- Ícone Olho Riscado -->
                                <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Lembrar Acesso -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center text-xs text-slate-600 cursor-pointer select-none">
                            <input type="checkbox"
                                   wire:model="remember"
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 mr-2">
                            <span>Lembrar acesso neste dispositivo</span>
                        </label>
                    </div>

                    <!-- Botão Principal de Submissão -->
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-4 rounded-xl font-semibold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/25 transition-all focus:outline-none focus:ring-4 focus:ring-blue-600/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Entrar na Plataforma</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Autenticando...</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- ACESSOS DE DEMONSTRAÇÃO / SANDBOX (Visualmente Secundários) -->
            <div x-data="{ openDemo: false }" class="border border-slate-200 rounded-xl bg-white/70 overflow-hidden text-xs">
                <button type="button" 
                        @click="openDemo = !openDemo" 
                        class="w-full flex items-center justify-between p-3.5 text-left text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors focus:outline-none">
                    <span class="font-medium flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Ambiente de Avaliação / Demonstração (Sandbox)</span>
                    </span>
                    <svg :class="openDemo ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openDemo" 
                     x-cloak 
                     x-transition 
                     class="p-4 pt-1 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Atalhos para simulação com diferentes papéis do sistema. Protegido contra uso em ambiente de produção:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <!-- 1. Admin -->
                        <button type="button"
                                wire:click="loginAs('admin@alvora.com.br')"
                                class="p-2.5 rounded-lg bg-white hover:bg-blue-50/60 border border-slate-200 text-left transition-all group cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800 group-hover:text-blue-600">Admin</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">SaaS</span>
                            </div>
                            <span class="text-[11px] text-slate-500 block truncate mt-0.5">admin@alvora.com.br</span>
                        </button>

                        <!-- 2. Instrutor -->
                        <button type="button"
                                wire:click="loginAs('professor@acme.com.br')"
                                class="p-2.5 rounded-lg bg-white hover:bg-purple-50/60 border border-slate-200 text-left transition-all group cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800 group-hover:text-purple-700">Instrutor</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">Empresa</span>
                            </div>
                            <span class="text-[11px] text-slate-500 block truncate mt-0.5">professor@acme.com.br</span>
                        </button>

                        <!-- 3. Aluno -->
                        <button type="button"
                                wire:click="loginAs('aluno@acme.com.br')"
                                class="p-2.5 rounded-lg bg-white hover:bg-cyan-50/60 border border-slate-200 text-left transition-all group cursor-pointer focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800 group-hover:text-cyan-700">Aluno</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-700 border border-cyan-200">Player</span>
                            </div>
                            <span class="text-[11px] text-slate-500 block truncate mt-0.5">aluno@acme.com.br</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rodapé Auxiliar do Formulário -->
            <div class="text-center text-xs text-slate-500 space-y-2 pt-2">
                <p>
                    Sua organização ainda não utiliza a plataforma? 
                    <a href="{{ url('/#demonstracao') }}" class="text-blue-600 hover:text-blue-700 font-semibold underline underline-offset-4">
                        Solicite uma demonstração institucional
                    </a>
                </p>
                <p class="text-[11px] text-slate-400">
                    &copy; {{ date('Y') }} ALVORA Educação Corporativa S.A. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
</div>
