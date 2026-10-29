@props([
    'active' => 'dashboard', // 'dashboard' | 'tracks' | 'catalog'
])

@php
    $hasTracks = \App\Helpers\StudentNavHelper::hasTracks();
    $user = auth()->user();
    $initials = strtoupper(substr($user->name ?? 'AL', 0, 2));
@endphp

<aside class="w-full lg:w-[280px] shrink-0 lg:sticky lg:top-28 z-20">
    <div class="bg-edtech-surface border-2 border-edtech-blue rounded-[20px] p-5 lg:p-6 shadow-sidebar flex flex-col">
        <!-- Student Profile Identity -->
        <div class="flex flex-row lg:flex-col items-center lg:text-center gap-4 lg:gap-0 pb-4 lg:pb-6 border-b border-edtech-border">
            <div class="w-14 h-14 sm:w-16 sm:h-16 lg:w-[112px] lg:h-[112px] rounded-full bg-gradient-to-br from-edtech-blue to-edtech-blue-dark text-white flex items-center justify-center font-semibold text-xl sm:text-2xl lg:text-[36px] lg:mb-4 shadow-[0_0_0_4px_#FFFFFF] shrink-0">
                {{ $initials }}
            </div>
            <div class="min-w-0">
                <h2 class="font-semibold text-edtech-text text-[17px] lg:text-[20px] leading-tight truncate">
                    {{ $user->name }}
                </h2>
                @if($user->company)
                    <p class="text-[13px] lg:text-[14px] text-edtech-muted mt-1 truncate">
                        {{ $user->company->name }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav aria-label="Menu do Aluno" class="mt-3 lg:mt-4 flex flex-col gap-1">
            <!-- 1. Meu Aprendizado -->
            @php $isDashboard = ($active === 'dashboard'); @endphp
            <a href="{{ route('student.dashboard') }}"
               class="group relative flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-edtech-blue focus-visible:bg-edtech-blue-light/60 overflow-hidden {{ $isDashboard ? 'font-semibold text-edtech-blue bg-edtech-blue-light' : 'font-medium text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60' }}">
                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-6 rounded-r transition-transform origin-center duration-200 ease-out motion-reduce:transform-none {{ $isDashboard ? 'bg-edtech-blue scale-y-100' : 'bg-edtech-blue/50 scale-y-0 group-hover:scale-y-100 group-focus-visible:scale-y-100' }}"></span>
                <x-icon name="book-open" class="w-5 h-5 shrink-0 transition-transform duration-200 ease-out motion-reduce:transform-none {{ $isDashboard ? 'text-edtech-blue' : 'text-edtech-muted group-hover:text-edtech-blue group-hover:translate-x-0.5 group-focus-visible:text-edtech-blue group-focus-visible:translate-x-0.5' }}" />
                <span>Meu Aprendizado</span>
            </a>

            <!-- 2. Minhas Trilhas (rendered only when student has track enrollments) -->
            @if ($hasTracks)
                <div class="h-px bg-edtech-border my-1"></div>
                @php $isTracks = ($active === 'tracks'); @endphp
                <a href="{{ route('student.tracks') }}"
                   class="group relative flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-edtech-blue focus-visible:bg-edtech-blue-light/60 overflow-hidden {{ $isTracks ? 'font-semibold text-edtech-blue bg-edtech-blue-light' : 'font-medium text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60' }}">
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-6 rounded-r transition-transform origin-center duration-200 ease-out motion-reduce:transform-none {{ $isTracks ? 'bg-edtech-blue scale-y-100' : 'bg-edtech-blue/50 scale-y-0 group-hover:scale-y-100 group-focus-visible:scale-y-100' }}"></span>
                    <x-icon name="map" class="w-5 h-5 shrink-0 transition-transform duration-200 ease-out motion-reduce:transform-none {{ $isTracks ? 'text-edtech-blue' : 'text-edtech-muted group-hover:text-edtech-blue group-hover:translate-x-0.5 group-focus-visible:text-edtech-blue group-focus-visible:translate-x-0.5' }}" />
                    <span>Minhas Trilhas</span>
                </a>
            @endif

            <div class="h-px bg-edtech-border my-1"></div>

            <!-- 3. Catálogo de Cursos -->
            @php $isCatalog = ($active === 'catalog'); @endphp
            <a href="{{ route('student.catalog') }}"
               class="group relative flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-edtech-blue focus-visible:bg-edtech-blue-light/60 overflow-hidden {{ $isCatalog ? 'font-semibold text-edtech-blue bg-edtech-blue-light' : 'font-medium text-edtech-muted hover:text-edtech-blue hover:bg-edtech-blue-light/60' }}">
                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-6 rounded-r transition-transform origin-center duration-200 ease-out motion-reduce:transform-none {{ $isCatalog ? 'bg-edtech-blue scale-y-100' : 'bg-edtech-blue/50 scale-y-0 group-hover:scale-y-100 group-focus-visible:scale-y-100' }}"></span>
                <x-icon name="layout-grid" class="w-5 h-5 shrink-0 transition-transform duration-200 ease-out motion-reduce:transform-none {{ $isCatalog ? 'text-edtech-blue' : 'text-edtech-muted group-hover:text-edtech-blue group-hover:translate-x-0.5 group-focus-visible:text-edtech-blue group-focus-visible:translate-x-0.5' }}" />
                <span>Catálogo de Cursos</span>
            </a>

            <div class="h-px bg-edtech-border my-1"></div>

            <!-- 4. Sair (POST Form with CSRF) -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit"
                        title="Sair da Plataforma"
                        class="w-full flex items-center gap-3 px-3.5 h-[48px] rounded-xl text-[15px] font-medium text-edtech-muted hover:text-rose-600 hover:bg-rose-50/60 transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 cursor-pointer text-left">
                    <x-icon name="log-out" class="w-5 h-5 text-edtech-muted shrink-0" />
                    <span>Sair</span>
                </button>
            </form>
        </nav>
    </div>
</aside>

