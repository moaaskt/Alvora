@props(['title' => null, 'description' => null, 'theme' => 'dark', 'hideNav' => false])
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $theme === 'editorial' ? 'light scroll-smooth' : 'dark scroll-smooth' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>document.documentElement.classList.add('js');</script>

    <title>{{ $title ?? 'ALVORA — Plataforma de Treinamento Corporativo B2B com IA' }}</title>
    <meta name="description" content="{{ $description ?? 'Reduza a evasão em treinamentos corporativos com o cálculo preditivo do Score de Risco de Evasão e intervenção ativa por inteligência artificial.' }}">

    <!-- Favicon & System Icons -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-symbol-avatar.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-symbol-avatar.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="{{ $theme === 'editorial' ? 'bg-edtech-bg text-edtech-text font-sans' : 'bg-alvora-dark text-slate-100 font-sans' }} antialiased selection:bg-edtech-blue selection:text-white min-h-screen flex flex-col justify-between">
    @if (!$hideNav)
    @if ($theme === 'editorial')
        <!-- Header EdTech Corporativa Moderna -->
        <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 backdrop-blur-md bg-edtech-surface/95 border-b border-edtech-border transition-all duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Brand / Identidade -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ url('/') }}" class="flex items-center space-x-3 group focus:outline-none focus:ring-2 focus:ring-edtech-blue/40 rounded-lg p-1">
                            <img src="{{ asset('images/logo-horizontal.png') }}" width="158" height="36" alt="Alvora" class="h-9 w-auto shrink-0 transition-transform duration-200 group-hover:scale-[1.02]">
                        </a>
                    </div>

                    <!-- Navegação EdTech Desktop -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-edtech-muted">
                        <a href="{{ url('/#diferenciais') }}" class="hover:text-edtech-blue transition-colors py-1">Diferenciais</a>
                        <a href="{{ url('/#comparativo') }}" class="hover:text-edtech-blue transition-colors py-1">Por que Alvora?</a>
                        <a href="{{ url('/#cursos') }}" class="hover:text-edtech-blue transition-colors py-1">Programas &amp; Cursos</a>
                        <a href="{{ url('/#docentes') }}" class="hover:text-edtech-blue transition-colors py-1">Especialistas</a>
                        <a href="{{ url('/planos') }}" class="hover:text-edtech-blue transition-colors py-1 {{ request()->is('planos') ? 'text-edtech-blue font-semibold' : '' }}">Planos B2B</a>
                    </nav>

                    <!-- Ações & Autenticação -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-edtech-text hover:text-edtech-blue px-2.5 py-2 sm:px-3 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-edtech-blue/30">
                            Acessar
                        </a>
                        <a href="{{ url('/#apresentacao') }}" class="hidden sm:inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-white bg-edtech-blue hover:bg-edtech-blue-hover rounded-xl shadow-sm shadow-edtech-blue/25 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-edtech-blue/50">
                            Falar com Consultor
                        </a>
                        <!-- Botão Hambúrguer Mobile -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                type="button" 
                                class="md:hidden p-2 rounded-lg text-edtech-text hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-edtech-blue/30"
                                aria-label="Abrir menu de navegação">
                            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Menu Dropdown Mobile -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="md:hidden py-4 border-t border-edtech-border space-y-3 bg-edtech-surface"
                     style="display: none;">
                    <a @click="mobileMenuOpen = false" href="{{ url('/#diferenciais') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-edtech-text hover:bg-slate-50 hover:text-edtech-blue">Diferenciais</a>
                    <a @click="mobileMenuOpen = false" href="{{ url('/#comparativo') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-edtech-text hover:bg-slate-50 hover:text-edtech-blue">Por que Alvora?</a>
                    <a @click="mobileMenuOpen = false" href="{{ url('/#cursos') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-edtech-text hover:bg-slate-50 hover:text-edtech-blue">Programas &amp; Cursos</a>
                    <a @click="mobileMenuOpen = false" href="{{ url('/#docentes') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-edtech-text hover:bg-slate-50 hover:text-edtech-blue">Especialistas</a>
                    <a @click="mobileMenuOpen = false" href="{{ url('/planos') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-edtech-text hover:bg-slate-50 hover:text-edtech-blue">Planos B2B</a>
                    <div class="pt-2">
                        <a @click="mobileMenuOpen = false" href="{{ url('/#apresentacao') }}" class="block w-full text-center px-4 py-2.5 text-xs font-semibold text-white bg-edtech-blue hover:bg-edtech-blue-hover rounded-xl">
                            Falar com Consultor
                        </a>
                    </div>
                </div>
            </div>
        </header>

    @else
        <!-- Navbar Pública Padrão (Dark) -->
        <header class="sticky top-0 z-50 backdrop-blur-md bg-[#090D16]/80 border-b border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Brand / Logo -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ url('/') }}" class="flex items-center space-x-3 group py-1">
                            <img src="{{ asset('images/logo-horizontal-dark.png') }}" width="158" height="36" alt="Alvora" class="h-9 w-auto shrink-0 transition-transform duration-200 group-hover:scale-[1.02]">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
                        <a href="{{ url('/#diferenciais') }}" class="hover:text-white transition-colors">Diferenciais</a>
                        <a href="{{ url('/#comparativo') }}" class="hover:text-white transition-colors">Por que Alvora?</a>
                        <a href="{{ url('/planos') }}" class="hover:text-white transition-colors {{ request()->is('planos') ? 'text-indigo-400 font-semibold' : '' }}">Planos Corporativos</a>
                        <a href="{{ url('/#apresentacao') }}" class="hover:text-white transition-colors">Solicitar Demo</a>
                    </nav>

                    <!-- Actions -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors">
                            Entrar na Plataforma
                        </a>
                        <a href="{{ url('/#apresentacao') }}" class="hidden sm:inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 rounded-lg shadow-md shadow-indigo-600/30 transition-all hover:shadow-indigo-600/50 hover:scale-[1.02]">
                            Solicitar Demonstração
                        </a>
                    </div>
                </div>
            </div>
        </header>
    @endif
    @endif


    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    @if (!$hideNav)
    @if ($theme === 'editorial')
        <x-landing.footer />
    @else
        <!-- Footer Corporativo (Dark) -->
        <footer class="bg-[#0B1120] border-t border-slate-800/80 text-slate-400 text-sm mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    <div class="space-y-4 md:col-span-1">
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('images/logo-horizontal-dark.png') }}" width="140" height="32" alt="Alvora" class="h-8 w-auto shrink-0">
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Plataforma de capacitação corporativa B2B com intervenção ativa contra evasão alimentada por IA.
                        </p>
                        <div class="flex items-center gap-2 text-xs text-emerald-400 font-medium">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            LGPD Compliant &amp; Criptografia Ponta a Ponta
                        </div>
                    </div>

                    <div>
                        <h4 class="text-white font-semibold text-xs uppercase tracking-wider mb-4">Solução</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/#trilhas') }}" class="hover:text-indigo-400 transition-colors">Trilhas Corporativas</a></li>
                            <li><a href="{{ url('/#diferenciais') }}" class="hover:text-indigo-400 transition-colors">Score Preditivo de Evasão</a></li>
                            <li><a href="{{ url('/#diferenciais') }}" class="hover:text-indigo-400 transition-colors">Intervenção Automatizada por IA</a></li>
                            <li><a href="{{ url('/planos') }}" class="hover:text-indigo-400 transition-colors">Planos &amp; Precificação</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-semibold text-xs uppercase tracking-wider mb-4">Empresa</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/#apresentacao') }}" class="hover:text-indigo-400 transition-colors">Solicitar Demonstração</a></li>
                            <li><a href="{{ url('/planos') }}" class="hover:text-indigo-400 transition-colors">Contratação Enterprise</a></li>
                            <li><a href="#" class="hover:text-indigo-400 transition-colors">Termos de Uso</a></li>
                            <li><a href="#" class="hover:text-indigo-400 transition-colors">Política de Privacidade</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-semibold text-xs uppercase tracking-wider mb-4">Acesso</h4>
                        <p class="text-xs text-slate-400 mb-4">Já é cliente Alvora? Acesse o portal do colaborador ou gestor.</p>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition-colors">
                            Entrar na Plataforma
                        </a>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
                    <p>&copy; {{ date('Y') }} ALVORA Educação Corporativa S.A. Todos os direitos reservados.</p>
                    <p class="mt-2 sm:mt-0">Desenvolvido com IA de alta precisão para equipes de alta performance.</p>
                </div>
            </div>
        </footer>
    @endif
    @endif

    <!-- Container Global de Toasts (D-16) -->
    <div x-data="{ toasts: [] }" 
         x-on:toast.window="
            const id = Date.now();
            toasts.push({ id, message: $event.detail.message, type: $event.detail.type || 'success' });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
         " 
         class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-sm font-medium transition-all transform duration-300 border backdrop-blur-md"
                 :class="{
                    'bg-slate-900/90 border-emerald-500/50 text-emerald-300 shadow-emerald-950/40': t.type === 'success',
                    'bg-slate-900/90 border-rose-500/50 text-rose-300 shadow-rose-950/40': t.type === 'error',
                    'bg-slate-900/90 border-indigo-500/50 text-indigo-300 shadow-indigo-950/40': t.type === 'info'
                 }">
                <template x-if="t.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="t.type === 'error'">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
