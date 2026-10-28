<!DOCTYPE html>
<html lang="pt-BR" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Alvora — Meu Aprendizado' }}</title>

    <!-- Favicon & System Icons -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-symbol-avatar.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-symbol-avatar.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-edtech-bg text-edtech-text font-sans antialiased relative selection:bg-edtech-blue selection:text-white">
    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-edtech-surface/95 backdrop-blur-md border-b border-edtech-border">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand & Organization -->
            <div class="flex items-center gap-6 lg:gap-8 min-w-0">
                <a href="{{ route('student.dashboard') }}" class="flex flex-col min-w-0 focus:outline-none">
                    <img src="{{ asset('images/logo-horizontal.png') }}"
                         width="142"
                         height="32"
                         alt="Alvora"
                         class="h-7 sm:h-8 w-auto shrink-0 object-contain object-left">
                    @if(auth()->user()->company)
                        <span class="text-[13px] text-edtech-muted font-medium tracking-normal mt-0.5 truncate max-w-[180px] sm:max-w-[240px]">
                            {{ auth()->user()->company->name }}
                        </span>
                    @endif
                </a>

                <!-- Navigation Links -->
                <nav aria-label="Navegação Principal" class="hidden md:flex items-center gap-2 ml-2">
                    <a href="#meu-aprendizado"
                       class="px-4 py-2 rounded-lg text-sm font-semibold text-edtech-blue bg-edtech-blue-light transition-colors">
                        Meu Aprendizado
                    </a>
                    <a href="#catalogo"
                       class="px-4 py-2 rounded-lg text-sm font-medium text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light transition-colors">
                        Catálogo de Cursos
                    </a>
                </nav>
            </div>

            <!-- Right User Block -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-sm tracking-wide shadow-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AL', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold text-edtech-text leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[13px] text-edtech-muted leading-tight mt-0.5">Aluno</div>
                    </div>
                </div>

                <div class="h-6 w-px bg-edtech-border mx-1 hidden sm:block"></div>

                <!-- Logout Form Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            title="Sair da Plataforma"
                            aria-label="Sair da Plataforma"
                            class="p-2 text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light rounded-lg transition-colors cursor-pointer">
                        <x-icon name="log-out" class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- HERO BACKGROUND BAND (340px tall, linear vertical gradient, clean straight bottom edge) -->
    <div class="absolute top-20 left-0 right-0 h-[340px] w-full pointer-events-none z-0"
         style="background: linear-gradient(180deg, var(--color-edtech-bg) 0%, var(--color-edtech-hero-mid) 45%, var(--color-edtech-hero-end) 100%);"
         aria-hidden="true"></div>

    <!-- MAIN WRAPPER -->
    <main class="relative z-10 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-20 flex-1">
        {{ $slot }}
    </main>

    <!-- MINIMAL FOOTER -->
    <footer class="w-full bg-edtech-surface border-t border-edtech-border py-6 text-center text-[13px] text-edtech-muted relative z-10">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            ALVORA Plataforma B2B &bull; Ambiente Multi-Tenant Seguro &bull; Sessão Protegida
        </div>
    </footer>

    <!-- Container Global de Toasts -->
    <div x-data="{ toasts: [] }"
         x-on:toast.window="
            const id = Date.now();
            toasts.push({ id, message: $event.detail.message, type: $event.detail.type || 'success' });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
         "
         class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-sm font-medium transition-all transform duration-300 border backdrop-blur-md bg-edtech-surface"
                 :class="{
                    'border-alvora-emerald text-edtech-text': t.type === 'success',
                    'border-rose-500 text-rose-700': t.type === 'error',
                    'border-edtech-blue text-edtech-blue': t.type === 'info'
                 }">
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @auth
        @if(auth()->user()->isStudent())
            @livewire('student.ai-tutor-drawer')
        @endif
    @endauth

    @livewireScripts
</body>
</html>

