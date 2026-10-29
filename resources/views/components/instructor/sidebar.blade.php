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
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('employees.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\User::where('role', 'student')->when(auth()->user()?->company_id, fn($q, $c) => $q->where('company_id', $c))->count() }}
                    </span>
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

                <!-- Assinatura & Plano / Faturamento Global B2B -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ (request()->routeIs('company.billing*') || request()->routeIs('admin.billing*')) ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ auth()->user()?->isAlvoraAdmin() ? route('admin.billing') : route('company.billing') }}">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                        </svg>
                        <span>{{ auth()->user()?->isAlvoraAdmin() ? 'Faturamento Global B2B' : 'Assinatura & Plano' }}</span>
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
