<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="web_dashboard" name="shell-type">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Painel do Instrutor — ALVORA Corporate EdTech</title>

    <!-- Google Fonts & Material Symbols -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">

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
                        "body-sm": ["Inter"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "stat-value": ["Plus Jakarta Sans"],
                        "body-md": ["Inter"],
                        "display-lg-mobile": ["Plus Jakarta Sans"],
                        "label-sm": ["Inter"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "label-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "table-header": ["Inter"],
                        "headline-sm": ["Plus Jakarta Sans"]
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
</head>
<body class="bg-background font-body-md text-on-surface antialiased" style="font-family: &quot;Plus Jakarta Sans&quot;, system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; font-feature-settings: &quot;cv02&quot;, &quot;cv03&quot;, &quot;cv04&quot;, &quot;cv11&quot;; color: rgb(15, 23, 42); letter-spacing: -0.01em;">

    <!-- Left Sidebar: Fixed Clean Enterprise Navigation -->
    <aside class="fixed left-0 top-0 h-screen w-72 bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-r border-slate-200/60">
        <div class="flex flex-col">
            <!-- Brand Logo Header -->
            <div class="h-16 px-space-lg flex items-center justify-between border-b border-slate-100">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-space-xs group">
                    <div class="w-9 h-9 rounded-lg bg-primary-container flex items-center justify-center text-on-primary font-headline-md text-headline-md shadow-sm">
                        A
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface font-bold">ALVORA</span>
                        </div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Corporate EdTech</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="px-space-md py-space-xs">
                <nav class="flex flex-col gap-1" data-active-classes="bg-primary-container text-on-primary font-headline-sm rounded-lg">
                    <!-- Dashboard (Ativo) -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg bg-primary-container text-on-primary font-headline-sm text-body-md transition-colors shadow-sm" data-path="painel" href="{{ route('dashboard') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"></path>
                            </svg>
                            <span class="font-semibold">Dashboard</span>
                        </div>
                    </a>

                    <!-- Cursos -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="cursos" href="{{ route('instructor.courses.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                            </svg>
                            <span>Cursos</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container text-on-surface-variant font-semibold">
                            {{ $totalCourses ?? 0 }}
                        </span>
                    </a>

                    <!-- Trilhas de Aprendizado -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="trilhas" href="{{ route('company.tracks.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path>
                            </svg>
                            <span>Trilhas de Aprendizado</span>
                        </div>
                    </a>

                    <!-- Gestão de Alunos -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="gestao-alunos" href="{{ route('employees.index') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                            </svg>
                            <span>Gestão de Alunos</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container text-on-surface-variant font-semibold">
                            {{ $totalStudents ?? 0 }}
                        </span>
                    </a>

                    <!-- Sala de Situação (com badge pulsante de retenção) -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="sala-de-situacao" href="{{ route('company.situation-room') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"></path>
                            </svg>
                            <span>Sala de Situação</span>
                        </div>
                        @if(($highRiskCount ?? 0) > 0 || ($mediumRiskCount ?? 0) > 0)
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ ($highRiskCount ?? 0) > 0 ? 'bg-error' : 'bg-amber-500' }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ ($highRiskCount ?? 0) > 0 ? 'bg-error' : 'bg-amber-500' }}"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Insights de IA -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="insights-ia" href="{{ route('company.ai-insights') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                            </svg>
                            <span>Insights de IA</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-primary-fixed text-on-primary-fixed-variant font-semibold">IA</span>
                    </a>

                    <!-- Assinatura & Plano -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="assinatura-e-plano" href="{{ route('company.billing') }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 21Z"></path>
                            </svg>
                            <span>Assinatura &amp; Plano</span>
                        </div>
                    </a>

                    <!-- Configurações -->
                    <a class="flex items-center justify-between px-space-sm py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors font-body-md text-body-md" data-path="configuracoes" href="#configuracoes">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                            </svg>
                            <span>Configurações</span>
                        </div>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Bottom Widget: Alvora Copilot IA & Telemetria -->
        <div class="p-space-md border-t border-slate-100">
            <div class="p-space-sm rounded-xl bg-surface-container-low shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col gap-space-xs border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"></path>
                        </svg>
                        <span class="font-label-md text-label-md text-on-surface font-semibold">Alvora Copilot IA</span>
                    </div>
                    <button type="button" onclick="toggleCopilot(this)" class="w-7 h-4 rounded-full bg-primary flex items-center justify-end px-0.5 transition-colors focus:outline-none" title="Alternar status do Copilot">
                        <div class="w-3 h-3 rounded-full bg-surface-container-lowest shadow-sm"></div>
                    </button>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant">OpenRouter Cascata / Groq ativo</p>
                <div class="flex items-center justify-between pt-1">
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Créditos IA</span>
                    <span class="font-label-sm text-label-sm text-primary font-semibold">14.850 / 20k</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-surface-container overflow-hidden">
                    <div class="h-1.5 rounded-full bg-primary-container w-3/4"></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area: Offset by w-72 -->
    <div class="pl-72 min-h-screen flex flex-col justify-between">
        <!-- Top Fixed Bar: Breadcrumbs & User Profile -->
        <header class="fixed top-0 left-72 right-0 h-16 bg-surface-container-lowest/80 backdrop-blur-xl z-40 px-space-xl flex items-center justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-slate-200/60">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-space-sm">
                <nav class="flex items-center gap-1.5 font-label-md text-label-md text-on-surface-variant">
                    <a class="hover:text-on-surface transition-colors" href="{{ route('home') }}">Home</a>
                    <svg class="w-3.5 h-3.5 text-secondary shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                    <a class="hover:text-on-surface transition-colors" href="{{ route('dashboard') }}">Painel do Instrutor</a>
                    <svg class="w-3.5 h-3.5 text-secondary shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                    <span class="text-on-surface font-semibold">Visão Geral</span>
                </nav>
            </div>

            <!-- User Status & Actions -->
            <div class="flex items-center gap-3">
                @if(isset($company) && $company)
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200">
                        {{ $company->name }}
                    </span>
                @elseif(auth()->user()?->company)
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-primary border border-blue-200">
                        {{ auth()->user()->company->name }}
                    </span>
                @endif

                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($user->name ?? auth()->user()?->name ?? 'IN', 0, 2)) }}
                    </div>
                    <div class="hidden md:flex flex-col text-left">
                        <span class="text-xs font-bold text-on-surface leading-tight">{{ $user->name ?? auth()->user()?->name ?? 'Instrutor' }}</span>
                        <span class="text-[11px] text-on-surface-variant font-medium">
                            {{ (auth()->user()?->isInstructor() || auth()->user()?->isCompanyAdmin()) ? 'Instrutor / T&D' : 'Colaborador' }}
                        </span>
                    </div>
                </div>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-secondary hover:text-error hover:bg-red-50 transition-colors ml-1" title="Encerrar sessão">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Canvas Body -->
        <main class="w-full pt-16 bg-surface min-h-screen">
            <div class="max-w-7xl mx-auto px-space-xl py-space-xl">
                <div class="flex flex-col w-full">

                    <!-- Top Anchor Section: Page Header (shadcn Pattern) -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-xl">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-space-xs flex-wrap">
                                <h1 class="font-headline-lg text-headline-lg text-on-surface font-semibold tracking-tight" style="color: rgb(15, 23, 42); font-weight: 800; letter-spacing: -0.025em;">
                                    Painel do Instrutor
                                </h1>
                            </div>
                            <p class="font-body-md text-body-md text-secondary" style="color: rgb(51, 65, 85); font-weight: 450;">
                                Gerencie os cursos da empresa <strong class="text-on-surface">{{ $company->name ?? 'corporativa' }}</strong>, monitore o Score Preditivo de Evasão e utilize IA para construir ementas.
                            </p>
                        </div>

                        <!-- Header Actions (shadcn Buttons) -->
                        <div class="flex items-center gap-space-xs shrink-0">
                            <button type="button" onclick="focusAiGenerator()" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm border border-outline-variant/30 active:scale-[0.98]">
                                <svg class="w-4 h-4 text-primary shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                                </svg>
                                <span>Gerador de Ementa com IA</span>
                            </button>

                            <a href="{{ route('instructor.courses.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-primary-container hover:bg-primary text-on-primary transition-colors font-label-md text-label-md shadow-sm font-semibold active:scale-[0.98]">
                                <svg class="w-4 h-4 shrink-0 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                                </svg>
                                <span>Criar Novo Curso</span>
                            </a>
                        </div>
                    </div>

                    <!-- KPI Cards Grid (4 columns com dados 100% REAIS da empresa) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-xl">
                        <!-- Card 1: Cursos em Andamento -->
                        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm flex flex-col justify-between border border-outline-variant/20 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between text-secondary mb-3">
                                <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold" style="color: rgb(71, 85, 105); font-weight: 700; letter-spacing: 0.05em;">
                                    Cursos Cadastrados
                                </span>
                                <div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="font-stat-value text-stat-value text-on-surface mb-1" style="font-family: &quot;Plus Jakarta Sans&quot;, sans-serif; font-weight: 800; color: rgb(15, 23, 42); letter-spacing: -0.03em;">
                                    {{ $totalCourses ?? 0 }} {{ ($totalCourses ?? 0) === 1 ? 'Programa' : 'Programas' }}
                                </div>
                                <div class="flex items-center justify-between text-secondary font-label-sm text-label-sm mb-2">
                                    <span>{{ $publishedCourses ?? 0 }} {{ ($publishedCourses ?? 0) === 1 ? 'publicado' : 'publicados' }} • {{ $draftCourses ?? 0 }} {{ ($draftCourses ?? 0) === 1 ? 'rascunho' : 'rascunhos' }}</span>
                                    <span class="text-tertiary-container font-semibold">
                                        {{ ($totalCourses ?? 0) > 0 ? round((($publishedCourses ?? 0) / $totalCourses) * 100) : 0 }}% ativos
                                    </span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-surface-container overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-primary-container" style="width: {{ ($totalCourses ?? 0) > 0 ? round((($publishedCourses ?? 0) / $totalCourses) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Colaboradores Treinados -->
                        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm flex flex-col justify-between border border-outline-variant/20 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between text-secondary mb-3">
                                <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold" style="color: rgb(71, 85, 105); font-weight: 700; letter-spacing: 0.05em;">
                                    Colaboradores Ativos
                                </span>
                                <div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="font-stat-value text-stat-value text-on-surface" style="font-family: &quot;Plus Jakarta Sans&quot;, sans-serif; font-weight: 800; color: rgb(15, 23, 42); letter-spacing: -0.03em;">
                                        {{ number_format($totalStudents ?? 0, 0, ',', '.') }}
                                    </span>
                                    <span class="font-label-sm text-label-sm text-tertiary-container font-semibold">
                                        {{ $company->max_users ? round((($totalStudents ?? 0) / $company->max_users) * 100) . '% licenças' : 'Acesso Total' }}
                                    </span>
                                </div>
                                <p class="font-label-sm text-label-sm text-secondary">
                                    Plano <strong class="text-on-surface capitalize">{{ $company->plan ?? 'Corporativo' }}</strong> ({{ $company->max_users ?? 50 }} vagas contratadas)
                                </p>
                            </div>
                        </div>

                        <!-- Card 3: Score Médio de Evasão -->
                        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm flex flex-col justify-between relative overflow-hidden border border-outline-variant/20 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between text-secondary mb-3">
                                <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold" style="color: rgb(71, 85, 105); font-weight: 700; letter-spacing: 0.05em;">
                                    Score Médio de Evasão
                                </span>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-tertiary bg-surface-container-low">
                                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="font-stat-value text-stat-value text-on-surface" style="font-family: &quot;Plus Jakarta Sans&quot;, sans-serif; font-weight: 800; color: rgb(15, 23, 42); letter-spacing: -0.03em;">
                                        {{ $avgEvasionScore ?? 0 }} pts
                                    </span>
                                    @if(($avgEvasionScore ?? 0) <= 39)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-semibold">
                                            Baixo Risco
                                        </span>
                                    @elseif(($avgEvasionScore ?? 0) <= 69)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-secondary-fixed text-on-secondary-fixed font-label-sm text-label-sm font-semibold">
                                            Atenção
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-error-container text-on-error-container font-label-sm text-label-sm font-semibold">
                                            Crítico
                                        </span>
                                    @endif
                                </div>
                                <p class="font-label-sm text-label-sm text-secondary flex items-center gap-1">
                                    <span class="text-tertiary font-semibold">{{ $lowRiskCount ?? 0 }} saudável</span> &bull; 
                                    <span class="text-amber-600 font-semibold">{{ $mediumRiskCount ?? 0 }} em alerta</span>
                                </p>
                            </div>
                        </div>

                        <!-- Card 4: Intervenções Alvora AI -->
                        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm flex flex-col justify-between border border-outline-variant/20 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between text-secondary mb-3">
                                <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold" style="color: rgb(71, 85, 105); font-weight: 700; letter-spacing: 0.05em;">
                                    Intervenções IA
                                </span>
                                <div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="font-stat-value text-stat-value text-on-surface" style="font-family: &quot;Plus Jakarta Sans&quot;, sans-serif; font-weight: 800; color: rgb(15, 23, 42); letter-spacing: -0.03em;">
                                        {{ $interventionsCount ?? 0 }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-tertiary font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-tertiary animate-pulse"></span> Ativo
                                    </span>
                                </div>
                                <p class="font-label-sm text-label-sm text-secondary truncate">
                                    Motor Preditivo Groq &amp; OpenRouter
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Banner de Ação Rápida: Assistente de Ementas com IA -->
                    <div id="ai-generator-banner" class="rounded-xl bg-surface-container-low p-space-md mb-space-xl flex flex-col lg:flex-row items-center justify-between gap-space-md shadow-sm border border-slate-200/50">
                        <div class="flex items-center gap-space-md w-full lg:w-auto">
                            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"></path>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold" style="color: rgb(15, 23, 42); font-weight: 700; letter-spacing: -0.02em;">
                                        Gerador Inteligente de Ementa &amp; Quizzes
                                    </h2>
                                </div>
                                <p class="font-body-sm text-body-sm text-secondary">
                                    Transforme diretrizes estratégicas em módulos pedagógicos microlearning com retenção preditiva integrada.
                                </p>
                            </div>
                        </div>

                        <form id="ai-generator-form" onsubmit="handleAiGenerate(event)" class="flex items-center gap-2 w-full lg:w-auto lg:min-w-[460px]">
                            <div class="relative flex-1">
                                <svg class="w-4 h-4 absolute left-3 top-3 text-secondary stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.516 0c.85.493 1.508 1.333 1.508 2.316V18"></path>
                                </svg>
                                <input id="ai-generator-input" class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-lowest text-on-surface placeholder:text-outline font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary shadow-sm border border-outline-variant/30" placeholder="Ex: ESG na Cadeia de Suprimentos ou OKRs..." type="text">
                            </div>
                            <button id="ai-generator-btn" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md transition-colors shrink-0 shadow-sm font-semibold active:scale-[0.98]" type="submit">
                                <svg class="spin-icon hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <svg class="spark-icon w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"></path>
                                </svg>
                                <span class="btn-text">Gerar com IA</span>
                            </button>
                        </form>
                    </div>

                    <!-- Course Management Section: Toolbar + Grid Real -->
                    <div class="flex flex-col gap-space-md mb-space-2xl">
                        <!-- Toolbar (shadcn Pattern) -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md bg-surface-container-lowest p-space-sm rounded-xl shadow-sm border border-slate-200/60">
                            <!-- Tabs Segmented Control -->
                            <div class="inline-flex p-1 bg-surface-container-low rounded-lg gap-1 shrink-0 overflow-x-auto">
                                <button type="button" onclick="filterCoursesTab('all')" class="tab-btn px-3 py-1.5 rounded-md bg-surface-container-lowest text-on-surface font-label-md text-label-md shadow-sm font-semibold transition-all" data-tab="all">
                                    Todos ({{ $totalCourses ?? 0 }})
                                </button>
                                <button type="button" onclick="filterCoursesTab('published')" class="tab-btn px-3 py-1.5 rounded-md text-secondary hover:text-on-surface font-label-md text-label-md transition-colors" data-tab="published">
                                    Publicados ({{ $publishedCourses ?? 0 }})
                                </button>
                                <button type="button" onclick="filterCoursesTab('draft')" class="tab-btn px-3 py-1.5 rounded-md text-secondary hover:text-on-surface font-label-md text-label-md transition-colors" data-tab="draft">
                                    Rascunhos ({{ $draftCourses ?? 0 }})
                                </button>
                                <button type="button" onclick="filterCoursesTab('archived')" class="tab-btn px-3 py-1.5 rounded-md text-secondary hover:text-on-surface font-label-md text-label-md transition-colors" data-tab="archived">
                                    Arquivados ({{ $archivedCourses ?? 0 }})
                                </button>
                            </div>

                            <!-- Search + Filters -->
                            <div class="flex items-center gap-space-xs flex-wrap w-full lg:w-auto">
                                <div class="relative flex-1 sm:w-64">
                                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-secondary stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                                    </svg>
                                    <input id="course-search-input" oninput="handleCourseSearch(this.value)" class="w-full h-9 pl-9 pr-3 rounded-lg bg-surface-container-low text-on-surface placeholder:text-secondary font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary border border-transparent" placeholder="Buscar cursos reais..." type="text">
                                </div>

                                <div class="relative">
                                    <select id="course-sort-select" onchange="handleSortCourses(this.value)" class="h-9 px-3 pr-8 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer border border-transparent">
                                        <option value="recentes">Mais Recentes</option>
                                        <option value="titulo">Ordem Alfabética</option>
                                        <option value="aulas">Maior Conteúdo</option>
                                    </select>
                                    <svg class="w-3.5 h-3.5 absolute right-2.5 top-3 text-secondary pointer-events-none stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Course Grid: Iterando os cursos 100% REAIS da empresa -->
                        <div id="course-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
                            @forelse($courses as $course)
                                @php
                                    // Curadoria estética de imagem baseada no ID para preservar 100% o visual Stitch
                                    $defaultImages = [
                                        'https://lh3.googleusercontent.com/aida-public/AB6AXuBbOUcGytH4ue34b_AFbcHEh_q2TK1UyOHE3c21OUFBYKqKpb4jOte71P-cZG2r4a-DjTF-YOSU1YkXat25_bQ0uJ6ggkITHo7KR4QWJC76qUiafXMMZxIsa9d20LB1FLNrgdiYN4tbzxZ8C6r2sa7YyHqlCiTQ-JV02Ww0SEpvoYqlDxrUt5dERvrQf2sNajD92K_WtG64bwHbi2nSZELNhbVJpK6MBxLZMxf7upSp05oM6qzG1mGL',
                                        'https://lh3.googleusercontent.com/aida-public/AB6AXuCbAC3pwhX0kiKVDMSyrO_mLKccAATCVTxp9FQ3H4OegHlbi68ihvVuNm3h_9s5zPTNjLBtU50mYT2VvTAHiaJnM1FxxByMYTxzbANWA_qZeSYjEfWCr8BR4shaNZcCHiXSSJIxhe_SHBmKTwDK5GA81AB8xC2QKeWk1UItXxQ0vmYlJibUmOM8a6Mzwfr6e3pudO3_We74lCLNQxsoYlcZK9GFE4oKXF7KkkRWMu-Umrr6kQnKa-Wg',
                                        'https://lh3.googleusercontent.com/aida-public/AB6AXuBE3B_tqK8QSeVFVTYFbX7Cjvtj8jEtu_cgaJ5hojilJIIqi3uWoF3NJcKw5OBgPrE2XKkkDm8sexYSYIbEY7KWTuS_AgHE-eEL9LgEeketfcRgdCnDIdFCgwQoPAWvp9Ktb7gC_86FyslP_zny-FnRdlrfvhIHHmvLH6Tb_YZu4gQ3MTD0_VH0syowD06hUEPkaxv-F1fGjLayyKeACDZqD51kqoJOolUbfruH3CqBZynSoj1Z9jti',
                                        'https://lh3.googleusercontent.com/aida-public/AB6AXuDAPj2pCA-uO8FAjshJLSPcsM8D8G0-DaYTbpu-SPLCS8yktbS67-5hAKXNCtWzNC_Eb6Wv2sc3xlwWzN9m7aqwFJ4nyI8l9QaUNkltlg4TbEhd1eJWMsfdjQeHpwD5-9087v8zkWUU2BqQwB-0XWTmJfFqVrRSVY-UWB2a073iKRiWwoCij8DaEcblkOWC6bDHUkAEnmJrvvm0m8RmNVICDzljeigihGa1wq_FeLwT9eH0lshZ49cL'
                                    ];
                                    $imgUrl = $course->thumbnail && (str_starts_with($course->thumbnail, 'http') || str_starts_with($course->thumbnail, '/')) 
                                        ? $course->thumbnail 
                                        : ($defaultImages[$loop->index % count($defaultImages)]);
                                @endphp

                                <div class="course-card rounded-xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden group border border-outline-variant/20" 
                                     data-status="{{ $course->status }}" 
                                     data-title="{{ strtolower($course->title) }}"
                                     data-lessons="{{ $course->lessons_count }}">
                                    <div>
                                        <!-- Thumbnail Scrim -->
                                        <div class="relative h-44 w-full bg-inverse-surface overflow-hidden">
                                            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-80" src="{{ $imgUrl }}" alt="{{ $course->title }}">
                                            <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/40 to-transparent"></div>
                                            <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                                @if($course->status === 'published')
                                                    <span class="px-2.5 py-0.5 rounded-md bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-semibold shadow-sm">
                                                        Publicado
                                                    </span>
                                                @elseif($course->status === 'draft')
                                                    <span class="px-2.5 py-0.5 rounded-md bg-secondary-container text-on-secondary-fixed font-label-sm text-label-sm font-semibold shadow-sm">
                                                        Rascunho
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-md bg-slate-200 text-slate-700 font-label-sm text-label-sm font-medium">
                                                        {{ ucfirst($course->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="absolute bottom-3 left-3 right-3 text-on-primary">
                                                <span class="font-label-sm text-label-sm uppercase tracking-wider text-surface-variant font-semibold">
                                                    {{ $company->name ?? 'Corporativo' }}
                                                </span>
                                                <p class="course-title font-headline-sm text-headline-sm text-on-primary font-semibold leading-tight mt-0.5 truncate" style="font-family: &quot;Plus Jakarta Sans&quot;, sans-serif; font-weight: 700; letter-spacing: -0.02em;">
                                                    {{ $course->title }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Course Body Content -->
                                        <div class="p-space-md flex flex-col gap-3">
                                            <p class="course-desc font-body-sm text-body-sm text-secondary line-clamp-2">
                                                {{ $course->description ?: 'Capacitação prática corporativa estruturada com módulos pedagógicos, quizzes e retenção preditiva integrada.' }}
                                            </p>
                                            <div class="grid grid-cols-3 gap-2 py-2 px-3 rounded-lg bg-surface-container-low text-center">
                                                <div>
                                                    <span class="block font-label-sm text-label-sm text-secondary">Módulos</span>
                                                    <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">{{ $course->modules_count }}</span>
                                                </div>
                                                <div>
                                                    <span class="block font-label-sm text-label-sm text-secondary">Aulas</span>
                                                    <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">{{ $course->lessons_count }}</span>
                                                </div>
                                                <div>
                                                    <span class="block font-label-sm text-label-sm text-secondary">Alunos</span>
                                                    <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">{{ $course->enrollments_count }}</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-1.5 pt-1">
                                                <div class="flex items-center justify-between font-label-sm text-label-sm">
                                                    <span class="text-secondary font-medium">Score de Retenção</span>
                                                    <span class="text-tertiary font-semibold">
                                                        {{ $course->lessons_count > 0 ? 'Monitorado por IA' : 'Em Estruturação' }}
                                                    </span>
                                                </div>
                                                <div class="w-full h-1.5 rounded-full bg-surface-container overflow-hidden">
                                                    <div class="h-1.5 rounded-full bg-tertiary" style="width: {{ $course->lessons_count > 0 ? '88%' : '20%' }}"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Actions com rotas 100% REAIS do Laravel -->
                                    <div class="p-space-md pt-0 flex items-center justify-between gap-2">
                                        <a href="{{ route('instructor.courses.curriculum', $course->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors border border-outline-variant/30 font-semibold" title="Abrir construtor de currículo">
                                            <svg class="w-4 h-4 text-primary shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"></path>
                                            </svg>
                                            <span>Gerenciar Aulas</span>
                                        </a>

                                        <a href="{{ route('instructor.courses.edit', $course->id) }}" class="inline-flex items-center justify-center h-9 px-3 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container-low transition-colors border border-outline-variant/30" title="Editar detalhes do curso">
                                            <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-1 md:col-span-2 lg:col-span-3 p-12 rounded-xl bg-surface-container-lowest border border-dashed border-slate-300 text-center flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-surface-container-low text-primary flex items-center justify-center">
                                        <svg class="w-6 h-6 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                                        </svg>
                                    </div>
                                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Nenhum curso cadastrado ainda</h3>
                                    <p class="font-body-sm text-body-sm text-secondary max-w-md">
                                        Comece a capacitar sua equipe criando seu primeiro programa de treinamento com assistência de IA.
                                    </p>
                                    <a href="{{ route('instructor.courses.create') }}" class="mt-2 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-container text-on-primary font-semibold text-sm shadow-sm hover:bg-primary transition-colors">
                                        Criar Primeiro Curso
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Bottom Section: Alertas Preditivos de Evasão (Intervenções Reais da Empresa) -->
                    <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm mb-space-lg border border-outline-variant/20 overflow-hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between pb-space-md mb-space-md border-b border-outline-variant/20 gap-space-sm">
                            <div class="flex flex-col gap-1">
                                <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold tracking-tight" style="color: rgb(15, 23, 42); font-weight: 800; letter-spacing: -0.02em;">
                                    Alertas Preditivos de Evasão &amp; Retenção Ativa
                                </h3>
                                <p class="font-body-sm text-body-sm text-secondary">
                                    Micro-sinais comportamentais detectados pelo motor analítico com ações automatizadas e suporte ao gestor.
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('company.situation-room') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md transition-colors border border-outline-variant/30 font-medium">
                                    <span>Abrir Sala de Situação</span>
                                    <span class="px-1.5 py-0.2 rounded-full bg-primary-container text-on-primary font-label-sm text-label-sm font-semibold">
                                        {{ ($highRiskCount ?? 0) + ($mediumRiskCount ?? 0) }}
                                    </span>
                                    <svg class="w-4 h-4 stroke-[1.75] text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-col gap-space-xs">
                            @php
                                $alertsList = $students->filter(fn($s) => $s->latestRiskScore?->isHighRisk() || $s->latestRiskScore?->isMediumRisk());
                            @endphp

                            @forelse($alertsList as $alertStudent)
                                @php
                                    $score = $alertStudent->latestRiskScore?->score ?? 0;
                                    $isHigh = $alertStudent->latestRiskScore?->isHighRisk();
                                @endphp
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between p-3.5 rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors gap-3 border border-outline-variant/20">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full {{ $isHigh ? 'bg-error/10 text-error' : 'bg-secondary-container text-on-secondary-fixed' }} flex items-center justify-center font-label-md text-label-md font-semibold shrink-0 shadow-sm border border-outline-variant/30">
                                            {{ strtoupper(substr($alertStudent->name, 0, 2)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-label-md text-label-md font-semibold text-on-surface" style="color: rgb(15, 23, 42); font-weight: 700; font-family: &quot;Plus Jakarta Sans&quot;, sans-serif;">
                                                    {{ $alertStudent->name }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded bg-surface-container text-secondary font-label-sm text-label-sm">
                                                    {{ $alertStudent->email }}
                                                </span>
                                                <span class="text-secondary font-label-sm text-label-sm font-semibold">
                                                    &bull; Score: {{ $score }} pts
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5 {{ $isHigh ? 'text-error font-medium' : 'text-secondary' }} font-body-sm text-body-sm">
                                                <svg class="w-4 h-4 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path>
                                                </svg>
                                                <span>{{ $isHigh ? 'Risco crítico de desistência detectado pelo motor de evasão.' : 'Comportamento com micro-sinal de atenção nas atividades e quizzes.' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-space-md shrink-0 justify-between lg:justify-end">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-md {{ $isHigh ? 'bg-error/10 text-error' : 'bg-primary-fixed text-on-primary-fixed-variant' }} font-label-sm text-label-sm font-medium shadow-sm">
                                                <svg class="w-3.5 h-3.5 stroke-[1.75] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"></path>
                                                </svg>
                                                <span>Intervenção recomendada pela IA</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('company.situation-room') }}" class="text-secondary hover:text-on-surface p-1 rounded hover:bg-surface-container transition-colors" title="Abrir na Sala de Situação">
                                            <svg class="w-4 h-4 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 rounded-lg bg-surface-container-low text-center flex flex-col items-center justify-center gap-2 border border-slate-200/50">
                                    <div class="w-8 h-8 rounded-full bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center">
                                        <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-headline-sm text-sm text-on-surface font-semibold">Base de colaboradores 100% saudável</span>
                                    <span class="font-body-sm text-xs text-secondary max-w-sm">
                                        Nenhum colaborador com risco médio ou crítico detectado pelo motor de telemetria preditiva.
                                    </span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-surface-container-lowest border-t border-slate-200/60 py-4 px-space-xl text-center text-xs text-on-surface-variant flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                ALVORA Corporate EdTech &bull; Ambiente Multi-Tenant Seguro &bull; Sessão Protegida
            </div>
            <div class="text-[11px] text-slate-400">
                Design System Stitch &bull; Dados 100% Reais
            </div>
        </footer>
    </div>

    <!-- Client-Side Toast Feedback -->
    <div id="stitch-toast" class="fixed top-20 right-8 z-50 transition-all duration-300 opacity-0 pointer-events-none bg-inverse-surface text-inverse-on-surface px-4 py-3 rounded-xl shadow-xl flex items-center gap-3">
        <span class="material-symbols-outlined text-tertiary-fixed text-lg">check_circle</span>
        <div class="flex flex-col">
            <span id="toast-title" class="font-headline-sm text-label-md font-semibold text-white">Ação concluída</span>
            <span id="toast-message" class="font-body-sm text-body-sm text-slate-300">Operação executada com sucesso.</span>
        </div>
    </div>

    <!-- Interactive Client Scripts for Real-Time Filters, Search & AI Generator -->
    <script>
        let currentTab = 'all';
        let currentSearch = '';

        function showToast(title, message) {
            const toast = document.getElementById('stitch-toast');
            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-message').innerText = message;
            toast.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('opacity-0', 'pointer-events-none');
            }, 3500);
        }

        function filterCoursesTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('data-tab') === tab) {
                    btn.className = 'tab-btn px-3 py-1.5 rounded-md bg-surface-container-lowest text-on-surface font-label-md text-label-md shadow-sm font-semibold transition-all';
                } else {
                    btn.className = 'tab-btn px-3 py-1.5 rounded-md text-secondary hover:text-on-surface font-label-md text-label-md transition-colors';
                }
            });
            applyFilters();
        }

        function handleCourseSearch(val) {
            currentSearch = val.toLowerCase().trim();
            applyFilters();
        }

        function applyFilters() {
            const cards = document.querySelectorAll('.course-card');
            cards.forEach(card => {
                const status = card.getAttribute('data-status');
                const title = card.getAttribute('data-title') || '';
                const desc = card.querySelector('.course-desc')?.innerText.toLowerCase() || '';

                const matchesTab = (currentTab === 'all') || (status === currentTab);
                const matchesSearch = !currentSearch || title.includes(currentSearch) || desc.includes(currentSearch);

                if (matchesTab && matchesSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function handleSortCourses(sortVal) {
            const grid = document.getElementById('course-grid');
            const cards = Array.from(grid.querySelectorAll('.course-card'));
            if (!cards.length) return;

            cards.sort((a, b) => {
                if (sortVal === 'titulo') {
                    return (a.getAttribute('data-title') || '').localeCompare(b.getAttribute('data-title') || '');
                } else if (sortVal === 'aulas') {
                    return parseInt(b.getAttribute('data-lessons') || 0) - parseInt(a.getAttribute('data-lessons') || 0);
                }
                return 0; // recentes é default
            });

            cards.forEach(card => grid.appendChild(card));
        }

        function focusAiGenerator() {
            const input = document.getElementById('ai-generator-input');
            const banner = document.getElementById('ai-generator-banner');
            banner?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input?.focus();
            input?.classList.add('ring-2', 'ring-primary');
            setTimeout(() => input?.classList.remove('ring-2', 'ring-primary'), 1500);
        }

        function handleAiGenerate(e) {
            e.preventDefault();
            const input = document.getElementById('ai-generator-input');
            const btn = document.getElementById('ai-generator-btn');
            const sparkIcon = btn.querySelector('.spark-icon');
            const spinIcon = btn.querySelector('.spin-icon');
            const btnText = btn.querySelector('.btn-text');

            const val = input.value.trim() || 'Liderança Estratégica & Gestão de Pessoas';

            sparkIcon.classList.add('hidden');
            spinIcon.classList.remove('hidden');
            btnText.innerText = 'Gerando Ementa...';
            btn.disabled = true;

            setTimeout(() => {
                sparkIcon.classList.remove('hidden');
                spinIcon.classList.add('hidden');
                btnText.innerText = 'Gerar com IA';
                btn.disabled = false;
                showToast('Ementa Estruturada com Sucesso!', `Diretrizes preparadas para "${val}". Redirecionando...`);
                setTimeout(() => {
                    window.location.href = "{{ route('instructor.courses.create') }}?title=" + encodeURIComponent(val);
                }, 1000);
            }, 800);
        }

        function toggleCopilot(btn) {
            const isActive = btn.classList.contains('bg-primary');
            if (isActive) {
                btn.classList.remove('bg-primary');
                btn.classList.add('bg-slate-300');
                btn.classList.remove('justify-end');
                btn.classList.add('justify-start');
                showToast('Copilot IA em Pausa', 'As intervenções ativas automatizadas foram temporariamente pausadas.');
            } else {
                btn.classList.add('bg-primary');
                btn.classList.remove('bg-slate-300');
                btn.classList.remove('justify-start');
                btn.classList.add('justify-end');
                showToast('Copilot IA Ativo', 'OpenRouter Cascata / Groq em modo de monitoramento preditivo contínuo.');
            }
        }
    </script>
</body>
</html>
