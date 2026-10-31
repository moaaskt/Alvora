<div class="space-y-8">

    <!-- SUB-HEADER COM BREADCRUMB & METAS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs font-medium text-slate-500 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Home</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-300" />
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Painel do Administrador</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-300" />
            <span class="text-slate-500">Usuários</span>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-300" />
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
                <x-icon name="upload" class="w-4 h-4 text-slate-500" />
                <span>Exportar CSV</span>
            </button>

            <!-- Botão Primário Adicionar Novo Usuário -->
            <button wire:click="openCreateModal" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm shadow-blue-500/20 cursor-pointer">
                <x-icon name="plus-lg" class="w-4 h-4 text-white" />
                <span>Adicionar Novo {{ rtrim($roleTitle, 'es') === 'Estudant' ? 'Estudante' : (rtrim($roleTitle, 'res') === 'Administrado' ? 'Administrador' : (rtrim($roleTitle, 'es') === 'Instrutor' ? 'Instrutor' : 'Usuário')) }}</span>
            </button>
        </div>
    </div>

    <!-- MENSAGENS FLASH / ALERTA -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-slate-800 text-sm shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <x-icon name="check-circle-fill" class="w-4 h-4 text-emerald-600" />
                </div>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <x-icon name="x-lg" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200/80 flex items-center justify-between text-rose-800 text-sm shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                    <x-icon name="exclamation-triangle-fill" class="w-4 h-4 text-rose-600" />
                </div>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-rose-600">
                <x-icon name="x-lg" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- ABAS PRINCIPAIS DE NAVEGAÇÃO -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100/80 rounded-2xl w-fit border border-slate-200/60 shadow-2xs">
        <!-- Aba Administradores -->
        <button wire:click="switchRole('admin')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'admin' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <x-icon name="shield-lock-fill" class="w-4 h-4 text-blue-600" />
            <span>Administradores</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $role === 'admin' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-200 text-slate-700' }}">
                {{ $adminCount }}
            </span>
        </button>

        <!-- Aba Instrutores -->
        <button wire:click="switchRole('instructor')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'instructor' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <x-icon name="mortarboard-fill" class="w-4 h-4 text-amber-600" />
            <span>Instrutores</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $role === 'instructor' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-slate-200 text-slate-700' }}">
                {{ $instructorCount }}
            </span>
        </button>

        <!-- Aba Estudantes -->
        <button wire:click="switchRole('student')" 
                type="button" 
                class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer {{ $role === 'student' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900' }}">
            <x-icon name="people-fill" class="w-4 h-4 text-slate-600" />
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
                <x-icon name="search" class="w-4 h-4" />
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
                                        <x-icon name="telephone-fill" class="w-3.5 h-3.5 text-slate-400" />
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
                                            <x-icon name="plus-lg" class="w-4 h-4" />
                                        </a>
                                    @endif

                                    <!-- Redefinir Senha -->
                                    <button wire:click="openPasswordModal({{ $user->id }})" 
                                            type="button" 
                                            title="Redefinir Senha de Acesso"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer">
                                        <x-icon name="key-fill" class="w-4 h-4" />
                                    </button>

                                    <!-- Editar Dados -->
                                    <button wire:click="openEditModal({{ $user->id }})" 
                                            type="button" 
                                            title="Editar Dados Cadastrais"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer">
                                        <x-icon name="pencil-square" class="w-4 h-4" />
                                    </button>

                                    <!-- Excluir -->
                                    <button wire:click="confirmDelete({{ $user->id }})" 
                                            type="button" 
                                            title="Excluir Usuário"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        <x-icon name="trash3-fill" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <x-icon name="people" class="w-6 h-6 text-slate-300" />
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
                                <x-icon name="x-lg" class="w-4 h-4" />
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
                                <x-icon name="x-lg" class="w-4 h-4" />
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
                                <x-icon name="exclamation-triangle-fill" class="w-5 h-5" />
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

