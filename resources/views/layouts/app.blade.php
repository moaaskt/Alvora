<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'ALVORA') }}</title>

    <!-- Favicon & System Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#090D16] text-slate-100 antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white font-sans">
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
    <div x-data
         x-on:toast.window="
            const d = ($event.detail && typeof $event.detail === 'object') ? (Array.isArray($event.detail) ? $event.detail[0] : $event.detail) : { message: $event.detail };
            AlvoraSwal.fire({
                icon: d.type ?? 'info',
                title: d.title ?? '',
                text: d.message ?? '',
                toast: true,
                position: 'top-end',
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false
            });
         "></div>

    @auth
        @if(auth()->user()->isStudent())
            @livewire('student.ai-tutor-drawer')
        @endif
    @endauth

    @livewireScripts
</body>
</html>
