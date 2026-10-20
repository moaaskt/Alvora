<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'ALVORA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#090D16] text-slate-100 antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">
    <div>
        @include('layouts.navigation')

        <!-- Page Heading (Optional) -->
        @isset($header)
            <header class="bg-slate-900/60 border-b border-slate-800">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Main Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Rodapé Simples da Aplicação Interna -->
    <footer class="border-t border-slate-800/80 py-4 text-center text-xs text-slate-500">
        ALVORA Plataforma B2B &bull; Ambiente Multi-Tenant Seguro &bull; Sessão Protegida
    </footer>

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

    @auth
        @if(auth()->user()->isStudent())
            @livewire('student.ai-tutor-drawer')
        @endif
    @endauth

    @livewireScripts
</body>
</html>
