<div class="space-y-8">

    <!-- SUB-HEADER COM BREADCRUMB & METAS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs font-medium text-slate-500 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Home</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Painel do Administrador</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-500">Usuários</span>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900 font-semibold">{{ $roleTitle }}</span>
        </nav>

        <!-- Quick Meta & Badges -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-slate-600 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Base Total de Acessos: <strong class="text-slate-900">{{ $totalUsers }} Usuários</strong></span>
            </div>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <span class="text-xs text-slate-500">Governança Multi-Tenant: <strong class="text-slate-700">Alvora Central</strong></span>
        </div>
    </div>

    <!-- TÍTULO DA PÁGINA + AÇÕES PRINCIPAIS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Gestão de {{ $roleTitle }}</h1>
            <p class="text-sm text-slate-500 mt-1">Supervisão centralizada, concessão de acessos e auditoria cadastral de {{ strtolower($roleTitle) }}.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Botão Exportar CSV -->
            <button wire:click="exportCsv" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span>Exportar CSV</span>
            </button>

            <!-- Botão Primário Adicionar Novo Usuário -->
            <button wire:click="openCreateModal" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/20 cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Adicionar Novo {{ rtrim($roleTitle, 'es') === 'Estudant' ? 'Estudante' : (rtrim($roleTitle, 'res') === 'Administrado' ? 'Administrador' : (rtrim($roleTitle, 'es') === 'Instrutor' ? 'Instrutor' : 'Usuário')) }}</span>
            </button>
        </div>
    </div>

    <!-- MENSAGENS FLASH / ALERTA -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-slate-800 text-sm shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200/80 flex items-center justify-between text-rose-800 text-sm shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-rose-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    <!-- ABAS PRINCIPAIS DE NAVEGAÇÃO -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100/80 rounded-2xl w-fit border border-slate-200/60 shadow-2xs">
        <!-- Aba Administradores -->
        <button wire:click="switchRole('admin')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'admin' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <span>Administradores</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $role === 'admin' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-200 text-slate-700' }}">
                {{ $adminCount }}
            </span>
        </button>

        <!-- Aba Instrutores -->
        <button wire:click="switchRole('instructor')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'instructor' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
            <span>Instrutores</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $role === 'instructor' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-slate-200 text-slate-700' }}">
                {{ $instructorCount }}
            </span>
        </button>

        <!-- Aba Estudantes -->
        <button wire:click="switchRole('student')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'student' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5Zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
            </svg>
            <span>Estudantes</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $role === 'student' ? 'bg-slate-200 text-slate-800' : 'bg-slate-200 text-slate-700' }}">
                {{ $studentCount }}
            </span>
        </button>
    </div>

    <!-- FILTROS & BARRA DE PESQUISA -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Input de Busca com Lupa Perfeitamente Alinhada -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por nome, e-mail ou telefone de {{ strtolower($roleTitle) }}..." 
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-slate-800 placeholder-slate-400">
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filtro de Empresa / Tenant -->
            <select wire:model.live="companyFilter" 
                    class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                <option value="all">Todas as Empresas (Multi-Tenant)</option>
                <option value="global">Alvora Global HQ (Sem Tenant)</option>
                @foreach ($companies as $comp)
                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                @endforeach
            </select>

            <!-- Filtro de Status -->
            <select wire:model.live="statusFilter" 
                    class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                <option value="all">Todos os Status</option>
                <option value="active">Somente Ativos</option>
                <option value="inactive">Somente Inativos</option>
            </select>
        </div>
    </div>

    <!-- TABELA DE USUÁRIOS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Usuário</th>
                        <th class="py-3.5 px-4">Contato</th>
                        <th class="py-3.5 px-4">Empresa (Tenant)</th>
                        <th class="py-3.5 px-4">Papel &amp; Permissão</th>
                        @if ($role === 'instructor')
                            <th class="py-3.5 px-4 text-center">Cursos Criados</th>
                        @elseif ($role === 'student')
                            <th class="py-3.5 px-4 text-center">Matrículas</th>
                        @else
                            <th class="py-3.5 px-4 text-center">Atividades</th>
                        @endif
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <!-- ID -->
                            <td class="py-3.5 px-4 text-center text-xs text-slate-400 font-mono">
                                #{{ $user->id }}
                            </td>

                            <!-- Usuário (Avatar + Nome + Email) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200/80 font-bold text-slate-700 flex items-center justify-center text-xs shrink-0 select-none shadow-2xs">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition-colors truncate">
                                            {{ $user->name }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-mono truncate">
                                            {{ $user->email }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Contato -->
                            <td class="py-3.5 px-4">
                                @if ($user->phone)
                                    <span class="text-xs font-mono text-slate-700 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ $user->phone }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Não informado</span>
                                @endif
                            </td>

                            <!-- Empresa (Tenant) -->
                            <td class="py-3.5 px-4">
                                @if ($user->company)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        {{ $user->company->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Alvora Global HQ
                                    </span>
                                @endif
                            </td>

                            <!-- Papel & Permissão -->
                            <td class="py-3.5 px-4">
                                @php
                                    $roleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
                                @endphp
                                @if ($roleVal === 'admin_alvora')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        Super Admin
                                    </span>
                                @elseif ($roleVal === 'company_admin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        Admin Empresa (RH)
                                    </span>
                                @elseif ($roleVal === 'instructor')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Instrutor
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Estudante
                                    </span>
                                @endif
                            </td>

                            <!-- Métricas da Aba -->
                            @if ($role === 'instructor')
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                        {{ $user->courses_count }} cursos
                                    </span>
                                </td>
                            @elseif ($role === 'student')
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200/60">
                                        {{ $user->enrollments_count }} matriculados
                                    </span>
                                </td>
                            @else
                                <td class="py-3.5 px-4 text-center">
                                    <span class="text-xs text-slate-500 font-mono">
                                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                            @endif

                            <!-- Status (Ativo / Inativo) -->
                            <td class="py-3.5 px-4 text-center">
                                <button wire:click="toggleStatus({{ $user->id }})" 
                                        type="button" 
                                        title="Clique para alternar o status do usuário"
                                        class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all hover:scale-105 {{ ($user->is_active ?? true) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($user->is_active ?? true) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ ($user->is_active ?? true) ? 'Ativo' : 'Inativo' }}</span>
                                </button>
                            </td>

                            <!-- Ações -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Ação de Matricular Direto (se Estudante) -->
                                    @if ($roleVal === 'student')
                                        <a href="{{ route('admin.enrollments.create') }}?student_id={{ $user->id }}" 
                                           title="Matricular em Curso" 
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        </a>
                                    @endif

                                    <!-- Redefinir Senha -->
                                    <button wire:click="openPasswordModal({{ $user->id }})" 
                                            type="button" 
                                            title="Redefinir Senha de Acesso"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                        </svg>
                                    </button>

                                    <!-- Editar Dados -->
                                    <button wire:click="openEditModal({{ $user->id }})" 
                                            type="button" 
                                            title="Editar Dados Cadastrais"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Excluir -->
                                    <button wire:click="confirmDelete({{ $user->id }})" 
                                            type="button" 
                                            title="Excluir Usuário"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Nenhum {{ strtolower(rtrim($roleTitle, 's')) }} encontrado</h3>
                                    <p class="text-xs text-slate-500 mt-1">Nenhum registro corresponde aos filtros ou termo de busca aplicado.</p>
                                    <button wire:click="$set('search', '')" class="mt-4 text-xs font-semibold text-blue-600 hover:text-blue-700 cursor-pointer">
                                        Limpar termo de busca
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DE CRIAÇÃO / EDIÇÃO DE USUÁRIO -->
    @if ($showUserModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeUserModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Conteúdo do Modal -->
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <div class="p-6">
                        <!-- Topo do Modal -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900" id="modal-title">
                                    {{ $editingUserId ? 'Editar Dados Cadastrais' : 'Adicionar Novo ' . ($role === 'admin' ? 'Administrador' : ($role === 'instructor' ? 'Instrutor' : 'Estudante')) }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Preencha os campos abaixo com os dados cadastrais corporativos.</p>
                            </div>
                            <button wire:click="closeUserModal" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Formulário -->
                        <form wire:submit.prevent="saveUser" class="mt-5 space-y-4">
                            <!-- Nome Completo -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nome Completo *</label>
                                <input wire:model="name" 
                                       type="text" 
                                       placeholder="Ex: Carlos Eduardo de Oliveira" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800">
                                @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- E-mail e Telefone em grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">E-mail Corporativo *</label>
                                    <input wire:model="email" 
                                           type="email" 
                                           placeholder="usuario@empresa.com.br" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800 font-mono text-xs">
                                    @error('email') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Telefone / WhatsApp</label>
                                    <input wire:model="phone" 
                                           type="text" 
                                           placeholder="(11) 98765-4321" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800 font-mono text-xs">
                                    @error('phone') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Papel (Role) e Empresa (Tenant) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Papel / Nível *</label>
                                    <select wire:model.live="userRole" 
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                        <option value="admin_alvora">Super Admin Alvora</option>
                                        <option value="company_admin">Admin Empresa (RH)</option>
                                        <option value="instructor">Instrutor</option>
                                        <option value="student">Estudante</option>
                                    </select>
                                    @error('userRole') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Empresa (Tenant) {{ $userRole === 'admin_alvora' ? '(Opcional)' : '*' }}
                                    </label>
                                    <select wire:model="company_id" 
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                        <option value="">{{ $userRole === 'admin_alvora' ? 'Alvora Global HQ (Sem Tenant)' : '-- Selecione a Empresa --' }}</option>
                                        @foreach ($companies as $comp)
                                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Senha e Confirmação -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Senha {{ $editingUserId ? '(Opcional)' : '*' }}
                                    </label>
                                    <input wire:model="password" 
                                           type="password" 
                                           placeholder="{{ $editingUserId ? 'Deixe em branco para manter' : 'Mínimo 8 caracteres' }}" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800">
                                    @error('password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirmar Senha</label>
                                    <input wire:model="password_confirmation" 
                                           type="password" 
                                           placeholder="Repita a senha" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800">
                                </div>
                            </div>

                            <!-- Toggle Ativo -->
                            <div class="flex items-center gap-2 pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-2.5 text-xs font-semibold text-slate-700">Conta Ativa e Apta a Acessar</span>
                                </label>
                            </div>

                            <!-- Ações do Modal -->
                            <div class="pt-5 border-t border-slate-200/80 flex items-center justify-end gap-3">
                                <button wire:click="closeUserModal" 
                                        type="button" 
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm shadow-blue-500/20 cursor-pointer">
                                    {{ $editingUserId ? 'Salvar Alterações' : 'Cadastrar Usuário' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE REDEFINIÇÃO DE SENHA -->
    @if ($showPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closePasswordModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Redefinir Senha</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Definir nova credencial de acesso para <strong>{{ $passwordUserName }}</strong>.</p>
                            </div>
                            <button wire:click="closePasswordModal" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="updatePassword" class="mt-5 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nova Senha *</label>
                                <input wire:model="new_password" 
                                       type="password" 
                                       placeholder="Mínimo 8 caracteres" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800">
                                @error('new_password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirmar Nova Senha *</label>
                                <input wire:model="new_password_confirmation" 
                                       type="password" 
                                       placeholder="Repita a nova senha" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800">
                            </div>

                            <div class="pt-5 border-t border-slate-200/80 flex items-center justify-end gap-3">
                                <button wire:click="closePasswordModal" 
                                        type="button" 
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 transition-colors shadow-sm shadow-amber-500/20 cursor-pointer">
                                    Atualizar Senha
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-slate-900">Excluir Usuário</h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Tem certeza que deseja remover o usuário <strong>{{ $deletingUserName }}</strong>? Esta ação é irreversível.
                                </p>

                                @if ($hasCertificatesWarning)
                                    <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs">
                                        <strong class="font-bold block mb-0.5">Aviso de Governança de Certificados:</strong>
                                        Este usuário possui certificados emitidos. Para preservar a conformidade e validade pública dos documentos, a exclusão direta é bloqueada. Recomendamos <strong>desativar</strong> o usuário.
                                    </div>
                                @endif

                                @if ($deleteErrorMessage)
                                    <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs">
                                        {{ $deleteErrorMessage }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-200/80 flex items-center justify-end gap-3">
                            <button wire:click="closeDeleteModal" 
                                    type="button" 
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button wire:click="deleteUser" 
                                    type="button" 
                                    class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition-colors shadow-sm shadow-rose-500/20 cursor-pointer">
                                Confirmar Exclusão
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
