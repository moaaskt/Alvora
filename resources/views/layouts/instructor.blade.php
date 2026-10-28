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

    <!-- Left Sidebar: Fixed Clean Enterprise Navigation -->
    <aside class="fixed left-0 top-0 h-screen w-72 bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-r border-slate-200/60">
        <div class="flex flex-col">
            <!-- Brand Logo Header -->
            <div class="h-16 px-space-lg flex items-center justify-between border-b border-slate-100">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group py-1">
                    <img src="{{ asset('images/logo-symbol.png') }}" alt="ALVORA" class="h-8 w-auto shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <div class="flex flex-col justify-center">
                        <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface font-bold leading-none">ALVORA</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium mt-0.5">Corporate EdTech</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="px-space-md py-space-xs">
                <nav class="flex flex-col gap-1">
                    <!-- Dashboard -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('dashboard') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-13.5 18v-2.25Z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>

                    <!-- Cursos -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('instructor.courses.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('instructor.courses.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                            </svg>
                            <span>Cursos</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('instructor.courses.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                            {{ \App\Models\Course::count() }}
                        </span>
                    </a>

                    <!-- Trilhas de Aprendizado -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.tracks.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.tracks.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"></path>
                            </svg>
                            <span>Trilhas de Aprendizado</span>
                        </div>
                    </a>

                    <!-- Gestão de Alunos -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('employees.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('employees.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                            </svg>
                            <span>Gestão de Alunos</span>
                        </div>
                    </a>

                    <!-- Sala de Situação -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.situation-room') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.situation-room') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"></path>
                            </svg>
                            <span>Sala de Situação</span>
                        </div>
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-error"></span>
                        </span>
                    </a>

                    <!-- Insights de IA -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.ai-insights') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.ai-insights') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                            </svg>
                            <span>Insights de IA</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed font-semibold">Novo</span>
                    </a>

                    <!-- Assinatura & Plano -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.billing*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.billing') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                            </svg>
                            <span>Assinatura &amp; Plano</span>
                        </div>
                    </a>

                    <!-- Templates de Certificados -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('instructor.certificate-templates.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('instructor.certificate-templates.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557c0 1.58.558 3.03 1.492 4.167"></path>
                            </svg>
                            <span>Certificados</span>
                        </div>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Alvora Copilot IA Box no rodapé da Sidebar -->
        <div class="p-space-md border-t border-slate-100">
            <div class="p-space-sm rounded-xl bg-surface-container-low shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col gap-space-xs border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                        </svg>
                        <span class="font-label-md text-label-md text-on-surface font-semibold">Alvora Copilot IA</span>
                    </div>
                    <div class="w-7 h-4 rounded-full bg-primary flex items-center justify-end px-0.5">
                        <div class="w-3 h-3 rounded-full bg-surface-container-lowest"></div>
                    </div>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant">OpenRouter Cascata / Groq ativo</p>
                <div class="flex items-center justify-between pt-1">
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Créditos IA</span>
                    <span class="font-label-sm text-label-sm text-primary font-semibold">14.850 / 20k</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-surface-container">
                    <div class="h-1.5 rounded-full bg-primary-container w-3/4"></div>
                </div>
            </div>
        </div>
    </aside>

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
    <div x-data="{ toasts: [] }" 
         x-on:toast.window="
            const id = Date.now();
            toasts.push({ id, message: $event.detail.message, type: $event.detail.type || 'success' });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
         " 
         class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-sm font-medium transition-all transform duration-300 border backdrop-blur-md bg-white"
                 :class="{
                    'border-emerald-500 text-emerald-800 shadow-emerald-500/10': t.type === 'success',
                    'border-rose-500 text-rose-800 shadow-rose-500/10': t.type === 'error',
                    'border-blue-500 text-blue-800 shadow-blue-500/10': t.type === 'info'
                 }">
                <template x-if="t.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="t.type === 'error'">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
                <template x-if="t.type === 'info'">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
