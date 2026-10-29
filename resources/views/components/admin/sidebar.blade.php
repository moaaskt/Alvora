<!-- Left Sidebar: Fixed Clean Enterprise Navigation for Super Admin -->
<aside class="fixed left-0 top-0 h-screen w-72 bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-r border-slate-200/60 overflow-y-auto">
    <div class="flex flex-col">
        <!-- Brand Logo Header -->
        <div class="h-16 px-space-lg flex items-center justify-between border-b border-slate-100">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group py-1">
                <img src="{{ asset('images/logo-symbol.png') }}" alt="ALVORA" class="h-8 w-auto shrink-0 transition-transform duration-200 group-hover:scale-105">
                <div class="flex flex-col justify-center">
                    <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface font-bold leading-none">ALVORA</span>
                    <span class="font-label-sm text-label-sm text-amber-600 font-semibold mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Super Admin
                    </span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="px-space-md py-space-xs" x-data="{ openMenu: {{ request()->routeIs('admin.enrollments.*') ? "'matriculas'" : "null" }} }">
            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                Governança & LMS
            </div>

            <nav class="flex flex-col gap-1">
                <!-- Dashboard -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.dashboard') }}">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-13.5 18v-2.25Z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>

                <!-- Cursos -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.courses.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.courses.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path>
                        </svg>
                        <span>Cursos</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.courses.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\Course::count() }}
                    </span>
                </a>

                <!-- Categorias -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.categories.index') }}" title="Gestão de Categorias e Subcategorias">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386l5.411-3.184c.827-.486 1.096-1.528.61-2.355l-9.581-9.581A2.25 2.25 0 0 0 9.568 3Z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"></path>
                        </svg>
                        <span>Categorias</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.categories.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\Category::root()->count() }}
                    </span>
                </a>

                <!-- Matrículas (Com Sublinks) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'matriculas' ? null : 'matriculas')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.enrollments.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"></path>
                            </svg>
                            <span>Matrículas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.enrollments.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                                {{ \App\Models\Enrollment::withoutGlobalScopes()->count() }}
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.enrollments.*') ? 'text-white' : 'text-slate-400' }}" :class="{ 'rotate-180': openMenu === 'matriculas' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div x-show="openMenu === 'matriculas'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('admin.enrollments.index') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.enrollments.*') && request('action') !== 'create' ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Histórico de Matrículas</span>
                        </a>
                        <a href="{{ route('admin.enrollments.index', ['action' => 'create']) }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request('action') === 'create' ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Matricular Aluno</span>
                        </a>
                    </div>
                </div>

                <!-- Usuários (Com Sublinks) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'usuarios' ? null : 'usuarios')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                            </svg>
                            <span>Usuários</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openMenu === 'usuarios' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openMenu === 'usuarios'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('admin.dashboard') }}#usuarios-admin" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>Administrador</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#usuarios-instrutor" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>Instrutor</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#usuarios-estudante" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>Estudante</span>
                        </a>
                    </div>
                </div>

                <!-- Configurações (Com Sublinks) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'configuracoes' ? null : 'configuracoes')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors">
                        <div class="flex items-center gap-space-sm">
                            <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                            </svg>
                            <span>Configurações</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openMenu === 'configuracoes' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openMenu === 'configuracoes'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('admin.dashboard') }}#config-smtp" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>SMTP & Mensageria</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#config-websockets" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>WebSockets (Reverb)</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#config-logs" class="px-2 py-1.5 rounded hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <span>Logs do Sistema</span>
                        </a>
                    </div>
                </div>

                <div class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Financeiro & Empresas
                </div>

                <!-- Faturamento Global B2B -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.billing*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.billing') }}">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                        </svg>
                        <span>Faturamento B2B</span>
                    </div>
                </a>

                <!-- Templates de Certificados -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.certificates.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.certificates.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <svg class="w-5 h-5 shrink-0 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"></path>
                        </svg>
                        <span>Certificados</span>
                    </div>
                </a>
            </nav>
        </div>
    </div>

    <!-- Bottom User Profile Section -->
    <div class="p-space-md border-t border-slate-100 flex items-center justify-between bg-surface-container-lowest">
        <div class="flex items-center gap-space-xs">
            <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center text-xs uppercase border border-amber-200">
                SA
            </div>
            <div class="flex flex-col">
                <span class="font-label-md text-label-md text-on-surface font-semibold leading-tight">Super Admin</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px]">Plataforma Global</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Sair do Sistema">
                <svg class="w-5 h-5 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3.75"></path>
                </svg>
            </button>
        </form>
    </div>
</aside>

