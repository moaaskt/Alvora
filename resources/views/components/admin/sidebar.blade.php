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
        <div class="px-space-md py-space-xs" x-data="{ openMenu: {{ request()->routeIs('admin.enrollments.*') ? "'matriculas'" : (request()->routeIs('admin.users.*') ? "'usuarios'" : "null") }} }">
            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                Governança & LMS
            </div>

            <nav class="flex flex-col gap-1">
                <!-- Dashboard -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.dashboard') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="speedometer2" class="w-5 h-5 shrink-0" />
                        <span>Dashboard</span>
                    </div>
                </a>

                <!-- Cursos -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.courses.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.courses.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="journal-bookmark-fill" class="w-5 h-5 shrink-0" />
                        <span>Cursos</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.courses.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                        {{ \App\Models\Course::count() }}
                    </span>
                </a>

                <!-- Categorias -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.categories.index') }}" title="Gestão de Categorias e Subcategorias">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="tags-fill" class="w-5 h-5 shrink-0" />
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
                            <x-icon name="mortarboard-fill" class="w-5 h-5 shrink-0" />
                            <span>Matrículas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.enrollments.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                                {{ \App\Models\Enrollment::withoutGlobalScopes()->count() }}
                            </span>
                            <x-icon name="chevron-down" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.enrollments.*') ? 'text-white' : 'text-slate-400' }}" x-bind:class="{ 'rotate-180': openMenu === 'matriculas' }" />
                        </div>
                    </button>
                    <div x-show="openMenu === 'matriculas'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('admin.enrollments.index') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.enrollments.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Histórico de Matrículas</span>
                        </a>
                        <a href="{{ route('admin.enrollments.create') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.enrollments.create') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Matricular Aluno</span>
                        </a>
                    </div>
                </div>

                <!-- Usuários (Com Sublinks) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'usuarios' ? null : 'usuarios')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <div class="flex items-center gap-space-sm">
                            <x-icon name="people-fill" class="w-5 h-5 shrink-0" />
                            <span>Usuários</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ request()->routeIs('admin.users.*') ? 'bg-white/20 text-white font-semibold' : 'bg-surface-container text-on-surface-variant font-semibold' }}">
                                {{ \App\Models\User::withoutGlobalScopes()->count() }}
                            </span>
                            <x-icon name="chevron-down" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-400' }}" x-bind:class="{ 'rotate-180': openMenu === 'usuarios' }" />
                        </div>
                    </button>
                    <div x-show="openMenu === 'usuarios'" x-collapse class="pl-9 pr-2 py-1 flex flex-col gap-1 text-sm text-slate-600">
                        <a href="{{ route('admin.users.admins') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.users.admins') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Administrador</span>
                        </a>
                        <a href="{{ route('admin.users.instructors') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.users.instructors') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Instrutor</span>
                        </a>
                        <a href="{{ route('admin.users.students') }}" class="px-2 py-1.5 rounded transition-colors flex items-center justify-between {{ request()->routeIs('admin.users.students') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 hover:text-slate-900' }}">
                            <span>Estudante</span>
                        </a>
                    </div>
                </div>

                <!-- Configurações (Com Sublinks) -->
                <div class="flex flex-col">
                    <button type="button" @click="openMenu = (openMenu === 'configuracoes' ? null : 'configuracoes')" class="w-full flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors">
                        <div class="flex items-center gap-space-sm">
                            <x-icon name="gear-fill" class="w-5 h-5 shrink-0" />
                            <span>Configurações</span>
                        </div>
                        <x-icon name="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': openMenu === 'configuracoes' }" />
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
                        <x-icon name="credit-card-2-front-fill" class="w-5 h-5 shrink-0" />
                        <span>Faturamento B2B</span>
                    </div>
                </a>

                <!-- Templates de Certificados -->
                <a class="flex items-center justify-between px-space-sm py-2 rounded-lg font-body-md text-body-md transition-colors {{ request()->routeIs('admin.certificates.*') ? 'bg-primary-container text-on-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}" href="{{ route('admin.certificates.index') }}">
                    <div class="flex items-center gap-space-sm">
                        <x-icon name="award-fill" class="w-5 h-5 shrink-0" />
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
                <x-icon name="box-arrow-right" class="w-5 h-5" />
            </button>
        </form>
    </div>
</aside>

