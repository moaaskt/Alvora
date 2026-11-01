<nav class="bg-[#090D16] border-b border-slate-800 sticky top-0 z-40 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Brand + Nav Links by Role (D-14) -->
            <div class="flex items-center space-x-8">
                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group py-1">
                    <img src="{{ asset('images/logo-symbol.png') }}" alt="ALVORA" class="h-8 w-auto shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <div class="flex flex-col justify-center">
                        <span class="text-lg font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors leading-none">ALVORA</span>
                        @if(auth()->user()->company)
                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 truncate max-w-[140px]">{{ auth()->user()->company->name }}</span>
                        @else
                            <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold mt-0.5">Super Admin</span>
                        @endif
                    </div>
                </a>

                <!-- Navigation Links by Role (D-14) -->
                <div class="hidden md:flex space-x-6 text-sm font-medium">
                    @if(auth()->user()->isAlvoraAdmin())
                        <!-- Admin Alvora Links -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="speedometer2" class="w-4 h-4 text-indigo-400" />
                            Visão Global
                        </a>
                        <a href="{{ route('admin.billing') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('admin.billing*') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="credit-card-2-front-fill" class="w-4 h-4 text-emerald-400" />
                            Faturamento B2B
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Empresas Clientes
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Leads da Landing
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Configurações IA
                        </a>
                    @elseif(auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin())
                        <!-- Painel do Instrutor (O instrutor é a própria empresa) -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="bar-chart-fill" class="w-4 h-4 text-indigo-400" />
                            Painel do Instrutor
                        </a>
                        <a href="{{ route('instructor.courses.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('instructor.courses.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Cursos & Conteúdos
                        </a>
                        <a href="{{ route('employees.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('employees.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="people-fill" class="w-4 h-4 text-emerald-400" />
                            Colaboradores & Convites
                        </a>
                        <a href="{{ route('company.tracks.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.tracks.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Trilhas Corporativas
                        </a>
                        <a href="{{ route('company.situation-room') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('company.situation-room*') ? 'text-rose-400 font-semibold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            Sala de Situação
                        </a>
                        <a href="{{ route('company.ai-insights') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.ai-insights*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Insights de IA
                        </a>
                        <a href="{{ route('company.billing') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.billing*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Assinatura & Plano
                        </a>
                    @else
                        <!-- Aluno / Colaborador Links -->
                        <a href="{{ route('student.dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('student.dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            Meu Aprendizado
                        </a>
                        <a href="{{ route('student.dashboard') }}#catalogo" class="text-slate-300 hover:text-white transition-colors py-2">
                            Catálogo de Cursos
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Minhas Trilhas
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Meus Certificados
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right Side: User Profile & Logout -->
            <div class="flex items-center space-x-4">
                <!-- Role Badge -->
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                    @if(auth()->user()->isAlvoraAdmin()) bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif(auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin()) bg-purple-500/20 text-purple-300 border border-purple-500/30
                    @else bg-slate-800 text-slate-300 border border-slate-700
                    @endif">
                    {{ auth()->user()->isAlvoraAdmin() ? 'Administrador Alvora' : ((auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin()) ? 'Instrutor' : 'Aluno') }}
                </span>

                <!-- User Info & Dropdown ("Select": Meu Perfil + Logout) -->
                <div x-data="{
                    dropdownOpen: false,
                    name: '{{ e(auth()->user()->name ?? 'Usuário') }}',
                    avatarUrl: '{{ auth()->user()->avatar_url ?? '' }}',
                    get initials() {
                        return this.name ? this.name.substring(0, 2).toUpperCase() : 'U';
                    }
                }"
                x-on:profile-updated.window="
                    if ($event.detail.name) name = $event.detail.name;
                    if ($event.detail.avatarUrl !== undefined) avatarUrl = $event.detail.avatarUrl || '';
                "
                class="relative">
                    <button type="button"
                            @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center space-x-2.5 p-1 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer group focus:outline-none"
                            aria-expanded="false"
                            :aria-expanded="dropdownOpen.toString()">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" :alt="name" class="w-8 h-8 rounded-full object-cover border border-slate-700">
                        </template>
                        <template x-if="!avatarUrl">
                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-white" x-text="initials">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </template>
                        <span class="text-xs font-medium text-slate-200 hidden sm:inline-block group-hover:text-white transition-colors" x-text="name">
                            {{ auth()->user()->name }}
                        </span>
                        <x-icon name="chevron-right" class="w-3 h-3 text-slate-400 group-hover:text-slate-200 transition-transform rotate-90" />
                    </button>

                    <!-- Dropdown Options ("Select": Meu Perfil + Logout) -->
                    <div x-show="dropdownOpen"
                         @click.outside="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         x-cloak
                         class="absolute right-0 mt-2 w-56 rounded-xl bg-slate-900 shadow-2xl border border-slate-800 py-1 z-50 divide-y divide-slate-800 text-left">
                        <div class="px-3.5 py-2.5 bg-slate-950/60">
                            <div class="text-xs font-semibold text-white truncate" x-text="name">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[11px] text-slate-400 truncate mt-0.5">
                                {{ auth()->user()->email }}
                            </div>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('settings.profile') }}"
                               @click="dropdownOpen = false"
                               class="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-200 hover:text-indigo-400 hover:bg-slate-800/80 flex items-center gap-2.5 transition-colors cursor-pointer">
                                <x-icon name="person" class="w-4 h-4 text-slate-400" />
                                <span>Meu Perfil</span>
                            </a>
                        </div>

                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-3.5 py-2 text-xs font-medium text-rose-400 hover:bg-rose-500/10 flex items-center gap-2.5 transition-colors cursor-pointer">
                                    <x-icon name="box-arrow-right" class="w-4 h-4 text-rose-400" />
                                    <span>Sair da Plataforma</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
