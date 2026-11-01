@props([
    'showHeroBand' => true,
    'bandHeight' => 340,
    'showAiTutor' => true,
    'title' => null,
    'active' => 'dashboard',
])
@php
    $hasTracks = \App\Helpers\StudentNavHelper::hasTracks();
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Alvora — Meu Aprendizado' }}</title>

    <!-- Favicon & System Icons -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-symbol-avatar.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-symbol-avatar.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://www.youtube.com/iframe_api"></script>
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-edtech-bg text-edtech-text font-sans antialiased relative selection:bg-edtech-blue selection:text-white">
    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-edtech-surface/95 backdrop-blur-md border-b border-edtech-border">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand & Organization -->
            <div class="flex items-center gap-6 lg:gap-8 min-w-0">
                <a href="{{ route('student.dashboard') }}" class="flex flex-col min-w-0 focus:outline-none">
                    <img src="{{ asset('images/logo-horizontal.png') }}"
                         width="142"
                         height="32"
                         alt="Alvora"
                         class="h-7 sm:h-8 w-auto shrink-0 object-contain object-left">
                    @if(auth()->user()->company)
                        <span class="text-[13px] text-edtech-muted font-medium tracking-normal mt-0.5 truncate max-w-[180px] sm:max-w-[240px]">
                            {{ auth()->user()->company->name }}
                        </span>
                    @endif
                </a>

                <!-- Navigation Links -->
                <nav aria-label="Navegação Principal" class="hidden md:flex items-center gap-2 ml-2">
                    <a href="{{ route('student.dashboard') }}"
                       class="px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('student.dashboard') ? 'text-edtech-blue bg-edtech-blue-light font-semibold' : 'text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60 font-medium' }}">
                        Meu Aprendizado
                    </a>
                    @if($hasTracks)
                        <a href="{{ route('student.tracks') }}"
                           class="px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('student.tracks') ? 'text-edtech-blue bg-edtech-blue-light font-semibold' : 'text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60 font-medium' }}">
                            Minhas Trilhas
                        </a>
                    @endif
                    <a href="{{ route('student.catalog') }}"
                       class="px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('student.catalog') ? 'text-edtech-blue bg-edtech-blue-light font-semibold' : 'text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60 font-medium' }}">
                        Catálogo de Cursos
                    </a>
                </nav>
            </div>

            <!-- Right User Block & Dropdown ("Select") -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                <div x-data="{
                    dropdownOpen: false,
                    name: '{{ e(auth()->user()->name ?? 'Aluno') }}',
                    avatarUrl: '{{ auth()->user()->avatar_url ?? '' }}',
                    get initials() {
                        return this.name ? this.name.substring(0, 2).toUpperCase() : 'AL';
                    }
                }"
                x-on:profile-updated.window="
                    if ($event.detail.name) name = $event.detail.name;
                    if ($event.detail.avatarUrl !== undefined) avatarUrl = $event.detail.avatarUrl || '';
                "
                class="relative">
                    <button type="button"
                            @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center gap-3 p-1 rounded-xl hover:bg-edtech-border-light transition-colors cursor-pointer group focus:outline-none"
                            aria-expanded="false"
                            :aria-expanded="dropdownOpen.toString()">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" :alt="name" class="w-10 h-10 rounded-full object-cover shadow-xs ring-2 ring-edtech-blue/30">
                        </template>
                        <template x-if="!avatarUrl">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-sm tracking-wide shadow-xs shrink-0" x-text="initials">
                                {{ strtoupper(substr(auth()->user()->name ?? 'AL', 0, 2)) }}
                            </div>
                        </template>
                        <div class="hidden sm:block text-left">
                            <div class="text-sm font-semibold text-edtech-text leading-tight group-hover:text-edtech-blue transition-colors" x-text="name">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[13px] text-edtech-muted leading-tight mt-0.5">Aluno</div>
                        </div>
                        <x-icon name="chevron-right" class="w-3.5 h-3.5 text-edtech-muted group-hover:text-edtech-text transition-transform rotate-90" />
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
                            <button type="button"
                                    @click="dropdownOpen = false; $dispatch('open-profile-drawer')"
                                    class="w-full text-left px-3.5 py-2 text-xs font-medium text-edtech-text hover:text-edtech-blue hover:bg-edtech-blue-light/50 flex items-center gap-2.5 transition-colors cursor-pointer">
                                <x-icon name="person" class="w-4 h-4 text-edtech-muted" />
                                <span>Meu Perfil</span>
                            </button>
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
            </div>
        </div>
    </header>

    @if($showHeroBand ?? true)
        <!-- HERO BACKGROUND BAND (linear vertical gradient, clean straight bottom edge) -->
        <div class="absolute top-20 left-0 right-0 w-full pointer-events-none z-0"
             style="height: {{ $bandHeight ?? 340 }}px; background: linear-gradient(180deg, var(--color-edtech-bg) 0%, var(--color-edtech-hero-mid) 45%, var(--color-edtech-hero-end) 100%);"
             aria-hidden="true"></div>
    @endif

    <!-- MAIN WRAPPER -->
    <main class="relative z-10 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-20 flex-1">
        {{ $slot }}
    </main>

    <!-- MINIMAL FOOTER -->
    <footer class="w-full bg-edtech-surface border-t border-edtech-border py-6 text-center text-[13px] text-edtech-muted relative z-10">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            ALVORA Plataforma B2B &bull; Ambiente Multi-Tenant Seguro &bull; Sessão Protegida
        </div>
    </footer>

    <!-- Container Global de Toasts -->
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

    @auth
        @livewire('profile-drawer')
        @if(auth()->user()->isStudent() && ($showAiTutor ?? true))
            @livewire('student.ai-tutor-drawer')
        @endif
    @endauth

    @livewireScripts
</body>
</html>
