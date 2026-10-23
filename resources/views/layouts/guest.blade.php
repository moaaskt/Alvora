@props(['title' => null, 'theme' => 'dark'])
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $theme === 'editorial' ? 'light scroll-smooth' : 'dark scroll-smooth' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ALVORA — Plataforma de Treinamento Corporativo B2B com IA' }}</title>
    <meta name="description" content="Reduza a evasão em treinamentos corporativos com o cálculo preditivo do Score de Risco de Evasão e intervenção ativa por inteligência artificial.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="{{ $theme === 'editorial' ? 'bg-[#FAF8F5] text-[#1A1C1E] font-body' : 'bg-[#090D16] text-slate-100 font-body' }} antialiased selection:bg-[#385E4E] selection:text-white min-h-screen flex flex-col justify-between">
    @if ($theme === 'editorial')
        <!-- Header Editorial Compacto (LMS Academy B2B) -->
        <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F5]/90 border-b border-[#E8E2D9] transition-all duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Brand / Identidade -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ url('/') }}" class="flex items-center space-x-3.5 group focus:outline-none focus:ring-2 focus:ring-[#385E4E]/40 rounded-lg p-1">
                            <div class="w-9 h-9 rounded-lg bg-[#1A1C1E] text-[#FAF8F5] flex items-center justify-center font-serif text-xl font-bold tracking-tight shadow-sm group-hover:bg-[#385E4E] transition-colors duration-200">
                                A
                            </div>
                            <div class="flex flex-col">
                                <span class="font-serif text-xl tracking-tight font-semibold text-[#1A1C1E] group-hover:text-[#385E4E] transition-colors">ALVORA</span>
                                <span class="text-[9px] uppercase tracking-[0.2em] text-[#646973] font-medium -mt-1">Corporate Academy</span>
                            </div>
                        </a>
                    </div>

                    <!-- Navegação Editorial -->
                    <nav class="hidden md:flex items-center space-x-9 text-sm font-medium text-[#2D3136]">
                        <a href="{{ url('/#catalogo') }}" class="hover:text-[#385E4E] transition-colors py-1">Cursos & Programas</a>
                        <a href="{{ url('/#trilhas') }}" class="hover:text-[#385E4E] transition-colors py-1">Trilhas Corporativas</a>
                        <a href="{{ url('/#instrutores') }}" class="hover:text-[#385E4E] transition-colors py-1">Especialistas</a>
                        <a href="{{ url('/#metodologia-ia') }}" class="hover:text-[#385E4E] transition-colors flex items-center gap-1.5 py-1">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#385E4E]"></span>
                            IA & Retenção
                        </a>
                        <a href="{{ url('/planos') }}" class="hover:text-[#385E4E] transition-colors py-1 {{ request()->is('planos') ? 'text-[#385E4E] font-semibold' : '' }}">Planos B2B</a>
                    </nav>

                    <!-- Ações & Autenticação -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-[#2D3136] hover:text-[#1A1C1E] px-3 py-2 rounded-lg hover:bg-[#E8E2D9]/40 transition-colors focus:outline-none focus:ring-2 focus:ring-[#385E4E]/30">
                            Acessar Plataforma
                        </a>
                        <a href="{{ url('/#demonstracao') }}" class="inline-flex items-center justify-center px-4.5 py-2.5 text-xs uppercase tracking-wider font-semibold text-white bg-[#1A1C1E] hover:bg-[#385E4E] rounded-lg shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#385E4E]/50">
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
                        <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-emerald-400 p-[2px] shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
                                <div class="w-full h-full bg-[#090D16] rounded-[10px] flex items-center justify-center">
                                    <span class="font-extrabold text-xl tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">A</span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors">ALVORA</span>
                                <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold -mt-1">IA Corporativa</span>
                            </div>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
                        <a href="{{ url('/#recursos') }}" class="hover:text-white transition-colors">Recursos</a>
                        <a href="{{ url('/#diferenciais-ia') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Diferenciais de IA
                        </a>
                        <a href="{{ url('/planos') }}" class="hover:text-white transition-colors {{ request()->is('planos') ? 'text-indigo-400 font-semibold' : '' }}">Planos Corporativos</a>
                        <a href="{{ url('/#demonstracao') }}" class="hover:text-white transition-colors">Solicitar Demo</a>
                    </nav>

                    <!-- Actions -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors">
                            Entrar na Plataforma
                        </a>
                        <a href="{{ url('/#demonstracao') }}" class="hidden sm:inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 rounded-lg shadow-md shadow-indigo-600/30 transition-all hover:shadow-indigo-600/50 hover:scale-[1.02]">
                            Solicitar Demonstração
                        </a>
                    </div>
                </div>
            </div>
        </header>
    @endif


    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    @if ($theme === 'editorial')
        <!-- Rodapé Editorial Sofisticado B2B -->
        <footer class="bg-[#F4EFEA] border-t border-[#E8E2D9] text-[#646973] text-sm mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-14">
                    <div class="space-y-4 md:col-span-5">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-[#1A1C1E] text-[#FAF8F5] flex items-center justify-center font-serif text-lg font-bold">A</div>
                            <span class="font-serif text-xl font-bold text-[#1A1C1E] tracking-tight">ALVORA</span>
                        </div>
                        <p class="text-xs text-[#646973] leading-relaxed max-w-sm">
                            Plataforma de capacitação corporativa orientada a retenção com inteligência preditiva ativa. Projetada para empresas que investem estrategicamente no desenvolvimento de seus talentos.
                        </p>
                        <div class="flex items-center gap-2 text-xs text-[#385E4E] font-medium pt-1">
                            <svg class="w-4 h-4 text-[#385E4E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Conformidade com LGPD & Criptografia Ponta a Ponta</span>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <h4 class="text-[#1A1C1E] font-semibold text-xs uppercase tracking-wider mb-4">Catálogo B2B</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/#catalogo') }}" class="hover:text-[#385E4E] transition-colors">Cursos em Destaque</a></li>
                            <li><a href="{{ url('/#trilhas') }}" class="hover:text-[#385E4E] transition-colors">Trilhas por Departamento</a></li>
                            <li><a href="{{ url('/#instrutores') }}" class="hover:text-[#385E4E] transition-colors">Corpo Docente</a></li>
                            <li><a href="{{ url('/#metodologia-ia') }}" class="hover:text-[#385E4E] transition-colors">Score Preditivo de Evasão</a></li>
                        </ul>
                    </div>

                    <div class="md:col-span-2">
                        <h4 class="text-[#1A1C1E] font-semibold text-xs uppercase tracking-wider mb-4">Empresa</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/planos') }}" class="hover:text-[#385E4E] transition-colors">Planos Corporativos</a></li>
                            <li><a href="{{ url('/#demonstracao') }}" class="hover:text-[#385E4E] transition-colors">Solicitar Apresentação</a></li>
                            <li><a href="#" class="hover:text-[#385E4E] transition-colors">Termos de Serviço</a></li>
                            <li><a href="#" class="hover:text-[#385E4E] transition-colors">Privacidade de Dados</a></li>
                        </ul>
                    </div>

                    <div class="md:col-span-3">
                        <h4 class="text-[#1A1C1E] font-semibold text-xs uppercase tracking-wider mb-4">Acesso Corporativo</h4>
                        <p class="text-xs text-[#646973] mb-4">Sua empresa já é parceira Alvora? Acesse o portal do colaborador ou gestor de RH.</p>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 rounded-lg bg-[#1A1C1E] hover:bg-[#385E4E] text-white text-xs font-semibold tracking-wide transition-colors">
                            Entrar na Plataforma
                        </a>
                    </div>
                </div>

                <div class="pt-8 border-t border-[#E8E2D9] flex flex-col sm:flex-row items-center justify-between text-xs text-[#7A7F8A]">
                    <p>&copy; {{ date('Y') }} ALVORA Educação Corporativa S.A. Todos os direitos reservados.</p>
                    <p class="mt-2 sm:mt-0 font-serif italic">Desenvolvimento de liderança e alta performance com inteligência ativa.</p>
                </div>
            </div>
        </footer>
    @else
        <!-- Footer Corporativo (Dark) -->
        <footer class="bg-[#0B1120] border-t border-slate-800/80 text-slate-400 text-sm mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    <div class="space-y-4 md:col-span-1">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white">A</div>
                            <span class="text-lg font-bold text-white tracking-wide">ALVORA</span>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Plataforma de capacitação corporativa B2B com intervenção ativa contra evasão alimentada por IA.
                        </p>
                        <div class="flex items-center gap-2 text-xs text-emerald-400 font-medium">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            LGPD Compliant & Criptografia Ponta a Ponta
                        </div>
                    </div>

                    <div>
                        <h4 class="text-white font-semibold text-xs uppercase tracking-wider mb-4">Solução</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/#recursos') }}" class="hover:text-indigo-400 transition-colors">Trilhas Corporativas</a></li>
                            <li><a href="{{ url('/#diferenciais-ia') }}" class="hover:text-indigo-400 transition-colors">Score Preditivo de Evasão</a></li>
                            <li><a href="{{ url('/#diferenciais-ia') }}" class="hover:text-indigo-400 transition-colors">Intervenção Automatizada por IA</a></li>
                            <li><a href="{{ url('/planos') }}" class="hover:text-indigo-400 transition-colors">Planos & Precificação</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-semibold text-xs uppercase tracking-wider mb-4">Empresa</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="{{ url('/#demonstracao') }}" class="hover:text-indigo-400 transition-colors">Solicitar Demonstração</a></li>
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

