<div class="space-y-8" x-data="{ copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        $dispatch('toast', { message: 'Link copiado para a área de transferência!', type: 'success' });
    }).catch(() => {
        $dispatch('toast', { message: 'Erro ao copiar o link.', type: 'error' });
    });
}}">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Colaboradores & Acessos</h1>
            <p class="text-sm text-slate-400 mt-1">
                Gerencie os membros da sua empresa e envie convites corporativos de aprendizado.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($company)
                <div class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                    <span class="text-slate-400">Vagas disponíveis:</span>
                    <strong class="text-emerald-400 ml-1">{{ $company->availableSlots() }}</strong> de {{ $company->max_users }}
                </div>
            @endif

            <button wire:click="$toggle('showInviteModal')" type="button"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 shadow-md shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Convidar Colaboradores</span>
            </button>
        </div>
    </div>

    <!-- Modal / Card de Disparo de Convites -->
    @if($showInviteModal)
        <div class="p-6 rounded-2xl bg-slate-900/90 border border-indigo-500/40 shadow-2xl relative">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Novo Envio de Convites</h3>
                        <p class="text-xs text-slate-400">Envie um ou múltiplos e-mails colados de uma só vez (D-08).</p>
                    </div>
                </div>
                <button wire:click="$set('showInviteModal', false)" type="button" class="text-slate-400 hover:text-white text-sm">
                    ✕
                </button>
            </div>

            <form wire:submit="inviteEmployees" class="space-y-4">
                <!-- Textarea com múltiplos e-mails -->
                <div>
                    <label for="emailsText" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        E-mails dos Colaboradores (separados por vírgula ou linha)
                    </label>
                    <textarea id="emailsText" wire:model="emailsText" rows="3" 
                              placeholder="joao@suaempresa.com.br, maria@suaempresa.com.br&#10;carlos@suaempresa.com.br"
                              class="w-full bg-slate-950 border @error('emailsText') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    @error('emailsText')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Papel Atribuído D-07 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Papel no Treinamento (D-07)</label>
                        <select wire:model="role" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="student">Colaborador / Aluno (Acesso ao Player e Cursos)</option>
                            <option value="instructor">Instrutor Corporativo (Criação de Conteúdos)</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <p class="text-[11px] text-slate-400">
                            ⏰ O link de convite terá validade de <strong>7 dias</strong> para ativação pelo colaborador (D-06).
                        </p>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end gap-3 pt-2">
                    <button wire:click="$set('showInviteModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800">
                        Cancelar
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-md shadow-indigo-600/30 flex items-center gap-2 cursor-pointer">
                        <span wire:loading.remove>Disparar Convites</span>
                        <span wire:loading>Processando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Tabela 1: Convites Pendentes -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>Convites Aguardando Ativação</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-800 text-slate-300 font-semibold">
                    {{ $pendingInvitations->count() }}
                </span>
            </h2>
        </div>

        @if($pendingInvitations->isEmpty())
            <div class="p-8 text-center rounded-2xl bg-slate-900/40 border border-slate-800/80">
                <p class="text-sm text-slate-400">Não há convites aguardando ativação no momento.</p>
            </div>
        @else
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">E-mail Convidado</th>
                            <th class="py-3 px-4">Papel</th>
                            <th class="py-3 px-4">Status & Validade</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($pendingInvitations as $invitation)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-medium text-white">
                                    {{ $invitation->email }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                        {{ $invitation->role === 'instructor' ? 'Instrutor' : 'Colaborador' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($invitation->isExpired())
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Expirado
                                        </span>
                                    @else
                                        <span class="text-slate-400">
                                            Expira em {{ $invitation->expires_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <!-- Botão Copiar Link D-05 -->
                                    <button @click="copyLink('{{ $invitation->getInvitationUrl() }}')" type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 text-[11px] font-semibold border border-slate-700 transition-colors"
                                            title="Copiar link de convite direto">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>Copiar link</span>
                                    </button>

                                    <!-- Botão Reenviar D-06 -->
                                    <button wire:click="resendInvitation({{ $invitation->id }})" type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold border border-slate-700 transition-colors">
                                        Reenviar
                                    </button>

                                    <!-- Botão Revogar D-06 -->
                                    <button wire:click="revokeInvitation({{ $invitation->id }})" 
                                            wire:confirm="Tem certeza de que deseja cancelar este link de acesso para {{ $invitation->email }}?"
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 text-[11px] font-semibold border border-rose-800/40 transition-colors">
                                        Revogar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Tabela 2: Colaboradores Ativos -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>Colaboradores Ativos</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-800 text-slate-300 font-semibold">
                    {{ $employees->count() }}
                </span>
            </h2>
        </div>

        @if($employees->isEmpty())
            <div class="p-10 text-center rounded-2xl bg-slate-900/40 border border-slate-800/80 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-slate-800 text-slate-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white">Nenhum colaborador cadastrado ainda</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Comece convidando os membros da sua equipe para iniciar os treinamentos corporativos.
                </p>
                <button wire:click="$set('showInviteModal', true)" type="button" class="inline-flex items-center text-xs font-semibold text-indigo-400 hover:text-indigo-300 mt-2">
                    Convidar o primeiro colaborador &rarr;
                </button>
            </div>
        @else
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Colaborador</th>
                            <th class="py-3 px-4">E-mail</th>
                            <th class="py-3 px-4">Papel</th>
                            <th class="py-3 px-4">Membro Desde</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($employees as $employee)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-white flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-bold text-white">
                                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                                    </div>
                                    <span>{{ $employee->name }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400">
                                    {{ $employee->email }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $employee->role?->label() ?? 'Colaborador' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400">
                                    {{ $employee->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Ativo
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
