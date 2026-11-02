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
        <div class="px-space-md py-space-xs" x-data="{ openMenu: {{ request()->routeIs('settings.*') ? "'configuracoes'" : "null" }} }">
            <nav class="flex flex-col gap-1">
                <!-- Dashboard -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('dashboard') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="speedometer2" class="w-5 h-5 shrink-0" />
                        <span>Dashboard</span>
                    </div>
                </a>

                <!-- Cursos -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('instructor.courses.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('instructor.courses.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="journal-bookmark-fill" class="w-5 h-5 shrink-0" />
                        <span>Cursos</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('instructor.courses.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\Course::count() }}
                    </span>
                </a>

                <!-- Trilhas de Aprendizado -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.tracks.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.tracks.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="map" class="w-5 h-5 shrink-0" />
                        <span>Trilhas de Aprendizado</span>
                    </div>
                </a>

                <!-- Gestão de Alunos -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('employees.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('employees.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="people-fill" class="w-5 h-5 shrink-0" />
                        <span>Gestão de Alunos</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('employees.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\User::where('role', 'student')->when(auth()->user()?->company_id, fn($q, $c) => $q->where('company_id', $c))->count() }}
                    </span>
                </a>

                <!-- Sala de Situação -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('company.situation-room') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('company.situation-room') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="activity" class="w-5 h-5 shrink-0" />
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
                        <x-icon name="stars" class="w-5 h-5 shrink-0" />
                        <span>Insights de IA</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed font-semibold">Novo</span>
                </a>

                <!-- Assinatura & Plano / Faturamento Global B2B -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ (request()->routeIs('company.billing*') || request()->routeIs('admin.billing*')) ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ auth()->user()?->isAlvoraAdmin() ? route('admin.billing') : route('company.billing') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="credit-card-2-front-fill" class="w-5 h-5 shrink-0" />
                        <span>{{ auth()->user()?->isAlvoraAdmin() ? 'Faturamento Global B2B' : 'Assinatura & Plano' }}</span>
                    </div>
                </a>

                <!-- Templates de Certificados -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('instructor.certificate-templates.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('instructor.certificate-templates.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="award-fill" class="w-5 h-5 shrink-0" />
                        <span>Certificados</span>
                    </div>
                </a>

                <!-- Configurações (Com Sublink: Meu Perfil) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'configuracoes' ? null : 'configuracoes')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('settings.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <div class="flex items-center gap-space-sm">
                            <x-icon name="gear-fill" class="w-5 h-5 shrink-0" />
                            <span>Configurações</span>
                        </div>
                        <x-icon name="chevron-down" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-400' }}" x-bind:class="{ 'rotate-180': openMenu === 'configuracoes' }" />
                    </button>
                    <div x-show="openMenu === 'configuracoes'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('settings.profile') }}" class="px-2 py-1.5 rounded transition-colors flex items-center gap-2 {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <x-icon name="person-fill" class="w-4 h-4 shrink-0 {{ request()->routeIs('settings.*') ? 'text-blue-600' : 'text-slate-400' }}" />
                            <span>Meu Perfil</span>
                        </a>
                    </div>
                </div>
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

