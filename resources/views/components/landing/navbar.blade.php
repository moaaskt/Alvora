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
            <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold text-edtech-text hover:text-edtech-blue px-4 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-edtech-blue/30">
                Acessar
            </a>
            <a href="{{ url('/#apresentacao') }}" class="hidden sm:inline-flex items-center justify-center bg-edtech-blue hover:bg-edtech-blue-hover text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm shadow-edtech-blue/20 focus:outline-none focus:ring-2 focus:ring-edtech-blue/40">
                Falar com Consultor
            </a>

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
            <a @click="mobileMenuOpen = false" href="{{ route('login') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-edtech-text border border-edtech-border hover:bg-edtech-bg">
                Acessar
            </a>
            <a @click="mobileMenuOpen = false" href="{{ url('/#apresentacao') }}" class="w-full text-center py-3 px-4 rounded-xl text-sm font-semibold text-white bg-edtech-blue hover:bg-edtech-blue-hover shadow-sm">
                Falar com Consultor
            </a>
        </div>
    </div>
</header>

