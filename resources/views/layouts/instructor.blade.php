<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="web_dashboard" name="shell-type">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ALVORA — Corporate EdTech' }}</title>

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
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "gutter": "1.5rem",
                        "space-lg": "1.5rem",
                        "space-md": "1rem",
                        "space-xs": "0.5rem",
                        "space-sm": "0.75rem",
                        "space-2xs": "0.25rem",
                        "space-2xl": "3rem",
                        "space-xl": "2rem",
                        "margin": "2rem"
                    },
                    fontFamily: {
                        "body-sm": ["Geist Variable", "system-ui", "sans-serif"],
                        "display-lg": ["Geist Variable", "system-ui", "sans-serif"],
                        "headline-lg": ["Geist Variable", "system-ui", "sans-serif"],
                        "stat-value": ["Geist Variable", "system-ui", "sans-serif"],
                        "body-md": ["Geist Variable", "system-ui", "sans-serif"],
                        "display-lg-mobile": ["Geist Variable", "system-ui", "sans-serif"],
                        "label-sm": ["Geist Variable", "system-ui", "sans-serif"],
                        "headline-md": ["Geist Variable", "system-ui", "sans-serif"],
                        "label-md": ["Geist Variable", "system-ui", "sans-serif"],
                        "body-lg": ["Geist Variable", "system-ui", "sans-serif"],
                        "table-header": ["Geist Variable", "system-ui", "sans-serif"],
                        "headline-sm": ["Geist Variable", "system-ui", "sans-serif"]
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

    <x-instructor.sidebar />

    <!-- Main Canvas Area -->
    <div class="pl-72">
        <!-- Top Bar Header -->
        <header class="fixed top-0 left-72 right-0 h-16 bg-surface-container-lowest/80 backdrop-blur-xl z-40 px-space-xl flex items-center justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-slate-200/60">
            <div class="flex items-center gap-space-sm">
                <nav class="flex items-center gap-1.5 font-label-md text-label-md text-on-surface-variant">
                    <a class="hover:text-on-surface transition-colors" href="{{ route('dashboard') }}">Home</a>
                    <svg class="w-3.5 h-3.5 text-secondary shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="m8.25 4.5 7.5 7.5-7.5 7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <a class="hover:text-on-surface transition-colors" href="{{ route('instructor.courses.index') }}">Cursos</a>
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
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-fixed text-on-primary-fixed-variant flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-semibold text-slate-800 leading-tight">{{ auth()->user()->name ?? 'Instrutor' }}</span>
                        <span class="text-[11px] text-slate-500">{{ auth()->user()->company?->name ?? 'Acme Corp' }}</span>
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
        <main class="w-full pt-16 bg-[#f8fafc] min-h-screen">
            {{ $slot }}
        </main>
    </div>

    <!-- Container Global de Toasts do Livewire -->
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

    @livewireScripts
</body>
</html>
