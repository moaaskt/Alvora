<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-edtech-surface/95 backdrop-blur-md border-b border-edtech-border transition-colors duration-200">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16 h-20 flex items-center justify-between">
        <!-- LOGO (primary logo for light backgrounds) -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 focus:outline-none focus:ring-2 focus:ring-edtech-blue/40 rounded-lg py-1">
            <img src="{{ asset('images/logo-horizontal.png') }}"
                 width="158"
                 height="36"
                 alt="Alvora"
                 class="h-8 sm:h-9 w-auto shrink-0">
            <span class="sr-only">ALVORA</span>
        </a>

        <!-- DESKTOP NAV LINKS -->
        <nav aria-label="Navegação Principal" class="hidden md:flex items-center gap-6 lg:gap-8">
            <a href="{{ url('/#diferenciais') }}" class="text-sm font-semibold text-edtech-muted hover:text-edtech-blue transition-colors">Diferenciais</a>
            <a href="{{ url('/#comparativo') }}" class="text-sm font-semibold text-edtech-muted hover:text-edtech-blue transition-colors">Por que Alvora?</a>
            <a href="{{ url('/#cursos') }}" class="text-sm font-semibold text-edtech-muted hover:text-edtech-blue transition-colors">Programas &amp; Cursos</a>
            <a href="{{ url('/#docentes') }}" class="text-sm font-semibold text-edtech-muted hover:text-edtech-blue transition-colors">Especialistas</a>
            <a href="{{ url('/planos') }}" class="text-sm font-semibold text-edtech-muted hover:text-edtech-blue transition-colors">Planos B2B</a>
        </nav>

        <!-- RIGHT ACTIONS -->
        <div class="flex items-center gap-2 sm:gap-4">
            @guest
                <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold text-edtech-text hover:text-edtech-blue px-4 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-edtech-blue/30">
                    Acessar
                </a>
                <a href="{{ url('/#apresentacao') }}" class="hidden sm:inline-flex items-center justify-center bg-edtech-blue hover:bg-edtech-blue-hover text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm shadow-edtech-blue/20 focus:outline-none focus:ring-2 focus:ring-edtech-blue/40">
                    Falar com Consultor
                </a>
            @endguest

            @auth
                <!-- User Profile & Navigation Menu -->
                <div x-data="{
                        dropdownOpen: false,
                        name: '{{ e(auth()->user()->name ?? 'Usuário') }}',
                        avatarUrl: '{{ auth()->user()->avatar_url ?? '' }}',
                        get initials() {
                            return this.name ? this.name.substring(0, 2).toUpperCase() : 'AV';
                        }
                    }"
                    x-on:profile-updated.window="
                        if ($event.detail.name) name = $event.detail.name;
                        if ($event.detail.avatarUrl !== undefined) avatarUrl = $event.detail.avatarUrl || '';
                    "
                    class="relative hidden sm:block">
                    <button type="button"
                            @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-edtech-border-light transition-colors cursor-pointer group focus:outline-none focus:ring-2 focus:ring-edtech-blue/30"
                            aria-expanded="false"
                            :aria-expanded="dropdownOpen.toString()">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" :alt="name" class="w-9 h-9 rounded-full object-cover shadow-xs ring-2 ring-edtech-blue/30">
                        </template>
                        <template x-if="!avatarUrl">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-xs tracking-wide shadow-xs shrink-0" x-text="initials">
                                {{ strtoupper(substr(auth()->user()->name ?? 'AV', 0, 2)) }}
                            </div>
                        </template>
                        <div class="text-left">
                            <div class="text-sm font-semibold text-edtech-text leading-tight group-hover:text-edtech-blue transition-colors max-w-[130px] truncate" x-text="name">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[11px] text-edtech-muted leading-tight mt-0.5 font-medium">
                                {{ auth()->user()->role->label() ?? 'Conectado' }}
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="w-3.5 h-3.5 text-edtech-muted group-hover:text-edtech-text transition-transform rotate-90" />
                    </button>

                    <!-- Dropdown Options -->
                    <div x-show="dropdownOpen"
                         @click.outside="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         x-cloak
                         class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-edtech-border py-1 z-50 divide-y divide-edtech-border">
                        <div class="px-3.5 py-2.5 bg-slate-50/70">
                            <div class="text-xs font-semibold text-edtech-text truncate" x-text="name">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[11px] text-edtech-muted truncate mt-0.5">
                                {{ auth()->user()->email }}
                            </div>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('dashboard') }}"
                               class="w-full text-left px-3.5 py-2 text-xs font-medium text-edtech-text hover:text-edtech-blue hover:bg-edtech-blue-light/50 flex items-center gap-2.5 transition-colors">
                                <x-icon name="layout-dashboard" class="w-4 h-4 text-edtech-muted" />
                                <span>Acessar Meu Painel</span>
                            </a>
                            <a href="{{ route('settings.profile') }}"
                               @click="dropdownOpen = false"
                               class="w-full text-left px-3.5 py-2 text-xs font-medium text-edtech-text hover:text-edtech-blue hover:bg-edtech-blue-light/50 flex items-center gap-2.5 transition-colors cursor-pointer">
                                <x-icon name="person" class="w-4 h-4 text-edtech-muted" />
                                <span>Meu Perfil</span>
                            </a>
                        </div>

                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-3.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-2.5 transition-colors cursor-pointer">
                                    <x-icon name="box-arrow-right" class="w-4 h-4 text-rose-500" />
                                    <span>Sair da Plataforma</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- MOBILE HAMBURGER BUTTON -->
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    type="button"
                    class="md:hidden p-2 rounded-xl text-edtech-text hover:bg-edtech-border-light focus:outline-none focus:ring-2 focus:ring-edtech-blue/30"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-label="Abrir menu de navegação">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- MOBILE MENU DROPDOWN -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden px-4 pt-2 pb-5 border-t border-edtech-border bg-edtech-surface space-y-2"
         style="display: none;">
        <a @click="mobileMenuOpen = false" href="{{ url('/#diferenciais') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-edtech-text hover:bg-edtech-bg hover:text-edtech-blue">Diferenciais</a>
        <a @click="mobileMenuOpen = false" href="{{ url('/#comparativo') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-edtech-text hover:bg-edtech-bg hover:text-edtech-blue">Por que Alvora?</a>
        <a @click="mobileMenuOpen = false" href="{{ url('/#cursos') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-edtech-text hover:bg-edtech-bg hover:text-edtech-blue">Programas &amp; Cursos</a>
        <a @click="mobileMenuOpen = false" href="{{ url('/#docentes') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-edtech-text hover:bg-edtech-bg hover:text-edtech-blue">Especialistas</a>
        <a @click="mobileMenuOpen = false" href="{{ url('/planos') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-edtech-text hover:bg-edtech-bg hover:text-edtech-blue">Planos B2B</a>
        <div class="pt-3 border-t border-edtech-border flex flex-col gap-2.5">
            @guest
                <a @click="mobileMenuOpen = false" href="{{ route('login') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-edtech-text border border-edtech-border hover:bg-edtech-bg">
                    Acessar
                </a>
                <a @click="mobileMenuOpen = false" href="{{ url('/#apresentacao') }}" class="w-full text-center py-3 px-4 rounded-xl text-sm font-semibold text-white bg-edtech-blue hover:bg-edtech-blue-hover shadow-sm">
                    Falar com Consultor
                </a>
            @endguest

            @auth
                <div class="flex items-center gap-3 px-2 py-2 mb-1 bg-edtech-bg rounded-xl border border-edtech-border">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-edtech-blue/30 shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-xs tracking-wide shrink-0">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-edtech-text truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-edtech-muted truncate">{{ auth()->user()->role->label() ?? 'Conectado' }}</div>
                    </div>
                </div>

                <a @click="mobileMenuOpen = false" href="{{ route('dashboard') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-edtech-blue hover:bg-edtech-blue-hover shadow-sm">
                    Acessar Meu Painel
                </a>

                <a @click="mobileMenuOpen = false" href="{{ route('settings.profile') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-edtech-text border border-edtech-border hover:bg-edtech-bg">
                    Meu Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full text-center py-2 px-4 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                        Sair da Plataforma
                    </button>
                </form>
            @endauth
        </div>
    </div>
</header>

