<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                    <x-icon name="gear-fill" class="w-4 h-4" />
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Central de Configurações
                </h1>
            </div>
            <p class="text-sm text-slate-500 mt-1.5">
                Gerencie seus dados pessoais, governança corporativa, segurança e preferências no ecossistema ALVORA.
            </p>
        </div>
    </div>

    <!-- Segmented Navigation Tabs (shadcn style) -->
    <div class="mt-6 border-b border-slate-200">
        <nav class="flex gap-6 -mb-px" aria-label="Abas de Configurações">
            <a href="{{ route('settings.profile') }}"
               class="py-3 px-1 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ request()->routeIs('settings.profile') ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <x-icon name="person" class="w-4 h-4" />
                <span>Meu Perfil</span>
            </a>

            <a href="{{ route('settings.security') }}"
               class="py-3 px-1 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ request()->routeIs('settings.security') ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <x-icon name="lock" class="w-4 h-4" />
                <span>Segurança e Senha</span>
            </a>

            <a href="{{ route('settings.notifications') }}"
               class="py-3 px-1 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ request()->routeIs('settings.notifications') ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <x-icon name="bell" class="w-4 h-4" />
                <span>Notificações</span>
            </a>
        </nav>
    </div>
</div>

