<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="web_dashboard" name="shell-type">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ALVORA — Super Admin & Governança' }}</title>

    <!-- Favicon & System Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <style>
        @layer base {
            html, body {
                margin: 0;
                padding: 0;
            }
            body {
                overscroll-behavior: none;
            }
            main > :first-child {
                margin-top: 0 !important;
            }
            main > :last-child {
                margin-bottom: 0 !important;
            }
        }
        ::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- Tailwind CSS CDN & Exact Stitch Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-fixed-variant": "#003ea8",
                        "surface-dim": "#cbdbf5",
                        "surface-container": "#e5eeff",
                        "on-tertiary-fixed": "#002113",
                        "inverse-primary": "#b4c5ff",
                        "surface": "#f8f9ff",
                        "primary-fixed-dim": "#b4c5ff",
                        "tertiary-container": "#007d55",
                        "on-primary-fixed": "#00174b",
                        "surface-container-low": "#eff4ff",
                        "surface-tint": "#0053db",
                        "on-surface": "#0b1c30",
                        "on-tertiary-fixed-variant": "#005236",
                        "surface-container-lowest": "#ffffff",
                        "error": "#ba1a1a",
                        "on-primary": "#ffffff",
                        "secondary-fixed-dim": "#bec6e0",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#bdffdb",
                        "on-secondary-fixed-variant": "#3f465c",
                        "on-error-container": "#93000a",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed": "#131b2e",
                        "tertiary": "#006242",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed": "#dbe1ff",
                        "on-error": "#ffffff",
                        "surface-variant": "#d3e4fe",
                        "secondary": "#565e74",
                        "primary": "#004ac6",
                        "on-surface-variant": "#434655",
                        "on-background": "#0b1c30",
                        "outline": "#737686",
                        "tertiary-fixed": "#6ffbbe",
                        "surface-container-high": "#dce9ff",
                        "tertiary-fixed-dim": "#4edea3",
                        "background": "#f8f9ff",
                        "secondary-fixed": "#dae2fd",
                        "on-secondary": "#ffffff",
                        "primary-container": "#2563eb",
                        "outline-variant": "#c3c6d7",
                        "on-primary-container": "#eeefff",
                        "surface-container-highest": "#d3e4fe",
                        "surface-bright": "#f8f9ff",
                        "inverse-surface": "#213145",
                        "secondary-container": "#dae2fd",
                        "on-secondary-container": "#5c647a"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "none": "0px",
                        "sm": "0.125rem",
                        "md": "0.375rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "3xl": "1.5rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "space-xs": "4px",
                        "space-sm": "8px",
                        "space-md": "16px",
                        "space-lg": "24px",
                        "space-xl": "32px",
                        "space-2xl": "40px"
                    },
                    fontSize: {
                        "body-sm": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
                        "display-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.025em", "fontWeight": "700" }],
                        "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.02em", "fontWeight": "600" }],
                        "stat-value": ["28px", { "lineHeight": "34px", "letterSpacing": "-0.025em", "fontWeight": "700" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "display-lg-mobile": ["26px", { "lineHeight": "32px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "label-sm": ["11px", { "lineHeight": "14px", "letterSpacing": "0.03em", "fontWeight": "600" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "letterSpacing": "-0.015em", "fontWeight": "600" }],
                        "label-md": ["13px", { "lineHeight": "18px", "fontWeight": "500" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "table-header": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
                        "headline-sm": ["16px", { "lineHeight": "24px", "letterSpacing": "-0.01em", "fontWeight": "600" }]
                    }
                }
            }
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-background font-body-md text-on-surface antialiased" style="font-family: 'Geist Variable', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11'; color: rgb(15, 23, 42); letter-spacing: -0.01em;">

    <x-admin.sidebar />

    <!-- Main Canvas Area -->
    <div class="pl-72">
        <!-- Top Bar Header -->
        <header class="fixed top-0 left-72 right-0 h-16 bg-surface-container-lowest/80 backdrop-blur-xl z-40 px-space-xl flex items-center justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-slate-200/60">
            <div class="flex items-center gap-space-sm">
                <nav class="flex items-center gap-1.5 font-label-md text-label-md text-on-surface-variant">
                    <a class="hover:text-on-surface transition-colors" href="{{ route('admin.dashboard') }}">Home</a>
                    <svg class="w-3.5 h-3.5 text-secondary shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="m8.25 4.5 7.5 7.5-7.5 7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span class="text-slate-600">Painel do Administrador</span>
                    @if(isset($breadcrumb) && $breadcrumb)
                        <svg class="w-3.5 h-3.5 text-secondary shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="m8.25 4.5 7.5 7.5-7.5 7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span class="text-on-surface font-semibold">{{ $breadcrumb }}</span>
                    @endif
                </nav>
            </div>

            <!-- Header Right: User Info & Actions -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Admin Alvora
                    </span>
                </div>

                <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-amber-600 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                        {{ substr(auth()->user()->name ?? 'SA', 0, 2) }}
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-semibold text-slate-800 leading-tight">{{ auth()->user()->name ?? 'Administrador' }}</span>
                        <span class="text-[11px] text-slate-500">Super Admin Global</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Encerrar Sessão">
                        <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3.75"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main View Content -->
        <main class="pt-24 pb-12 px-space-xl">
            {{ $slot }}
        </main>
    </div>

    <!-- Global Toast Notifications -->
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

