<div class="px-space-xl py-space-lg flex flex-col gap-space-lg max-w-[1600px] mx-auto w-full"
     x-data="{
         copyLink(url) {
             navigator.clipboard.writeText(url).then(() => {
                 $dispatch('toast', { message: 'Link de convite copiado para a área de transferência!', type: 'success' });
             }).catch(() => {
                 $dispatch('toast', { message: 'Erro ao copiar o link.', type: 'error' });
             });
         }
     }">

    <!-- HEADER EXECUTIVO & METADADOS -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Base Operacional</span>
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                <span class="font-label-sm text-label-sm font-semibold text-tertiary">Sincronizado via SSO Hub</span>
            </div>
            <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">Gestão de Alunos &amp; Acessos</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                Painel de Colaboradores & Acessos: gerencie a base de estudantes corporativos, aprove convites pendentes e acompanhe a retenção preditiva monitorada pelo motor Alvora.
            </p>
        </div>

        <!-- Top Right Controls -->
        <div class="flex flex-wrap items-center gap-space-sm">
            <!-- Botão Exportar CSV -->
            <button wire:click="exportCsv" wire:loading.attr="disabled"
                    class="h-10 px-space-sm py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container text-on-surface shadow-sm font-body-md text-body-md font-medium transition-all flex items-center gap-2 border border-slate-200/80">
                <svg class="w-4 h-4 text-secondary stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.25V16.5M7.5 12 12 16.5m0 0L16.5 12M12 16.5V3" />
                </svg>
                <span wire:loading.remove wire:target="exportCsv">Exportar Alunos</span>
                <span wire:loading wire:target="exportCsv">Gerando CSV...</span>
            </button>

            <!-- CTA Convidar -->
            <button wire:click="toggleInviteModal" type="button"
                    class="h-10 px-space-md py-2 rounded-lg bg-primary-container hover:bg-primary text-on-primary shadow-sm font-headline-sm text-headline-sm font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
                </svg>
                <span>+ Convidar Alunos</span>
            </button>
        </div>
    </div>

    <!-- CARDS DE RESUMO RÁPIDO (METRIC CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
        <!-- Metric 1: Total de Alunos Ativos -->
        <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between gap-space-sm group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-secondary font-medium">Total de Alunos Ativos</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $totalStudents }}</span>
                    @if($company)
                        <span class="font-body-sm text-body-sm text-secondary">/ {{ $company->max_users }} alocados</span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-tertiary-fixed text-on-tertiary-fixed">
                        {{ $company ? $company->availableSlots() : 0 }} vagas livres
                    </span>
                    <span class="font-label-sm text-label-sm text-secondary">capacidade contratada</span>
                </div>
            </div>
        </div>

        <!-- Metric 2: Convites Aguardando -->
        <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between gap-space-sm group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-secondary font-medium">Convites Aguardando</span>
                <div class="w-8 h-8 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-fixed group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $pendingCount }}</span>
                    <span class="font-body-sm text-body-sm text-secondary">pendentes</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-surface-container-high text-on-surface">Validade 7 dias</span>
                    <span class="font-label-sm text-label-sm text-secondary">ativação por link</span>
                </div>
            </div>
        </div>

        <!-- Metric 3: Taxa Média de Conclusão -->
        <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between gap-space-sm group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-secondary font-medium">Taxa Média de Conclusão</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="font-stat-value text-stat-value text-on-surface font-bold tracking-tight">{{ $avgCompletionRate }}%</span>
                    <span class="font-body-sm text-body-sm text-secondary">currículo</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-tertiary-fixed text-on-tertiary-fixed">
                        {{ $completedCount }} formados
                    </span>
                    <span class="font-label-sm text-label-sm text-secondary">ritmo pedagógico</span>
                </div>
            </div>
        </div>

        <!-- Metric 4: Risco de Evasão Geral -->
        <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col justify-between gap-space-sm group hover:shadow-md transition-shadow border border-slate-200/60">
            <div class="flex items-center justify-between">
                <span class="font-body-sm text-body-sm text-secondary font-medium">Risco de Evasão Médio</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-tertiary group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="font-stat-value text-stat-value {{ $avgRiskScore >= 70 ? 'text-error' : ($avgRiskScore >= 40 ? 'text-amber-600' : 'text-tertiary') }} font-bold tracking-tight">
                        {{ $avgRiskScore }} pts
                    </span>
                    <span class="font-body-sm text-body-sm text-secondary">
                        {{ $avgRiskScore >= 70 ? 'Alto Risco' : ($avgRiskScore >= 40 ? 'Médio Risco' : 'Baixo Risco') }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="w-2 h-2 rounded-full {{ $atRiskCount > 0 ? 'bg-error animate-pulse' : 'bg-tertiary' }}"></span>
                    <span class="font-label-sm text-label-sm text-secondary">
                        {{ $atRiskCount }} colaborador(es) em monitoramento
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- PAINEL DE DISPARO DE CONVITES EM LOTE (CARD EXPANSÍVEL / BANNER) -->
    @if($showInviteModal)
        <div class="rounded-xl bg-surface-container-lowest shadow-sm border border-primary/20 p-space-lg flex flex-col gap-space-md transition-all">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                        <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Convidar Novos Alunos &amp; Instrutores em Lote</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Cole e-mails separados por vírgula, ponto-e-vírgula ou nova linha. O link de convite expira em 7 dias.</p>
                    </div>
                </div>
                <button wire:click="toggleInviteModal" type="button" class="p-1 rounded-lg hover:bg-surface-container text-secondary hover:text-on-surface transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="inviteEmployees" class="flex flex-col gap-space-sm">
                <div>
                    <label for="emailsText" class="block font-label-md text-label-md font-semibold text-on-surface mb-1.5">
                        E-mails Corporativos dos Colaboradores
                    </label>
                    <textarea id="emailsText" wire:model="emailsText" rows="3"
                              placeholder="joao.silva@empresa.com.br, maria.santos@empresa.com.br&#10;carlos.eduardo@empresa.com.br"
                              class="w-full bg-surface-container-low border @error('emailsText') border-error @else border-outline-variant/60 @enderror rounded-xl px-4 py-3 font-body-md text-body-md text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary-container"></textarea>
                    @error('emailsText')
                        <p class="text-error font-label-sm text-label-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md font-semibold text-on-surface mb-1.5">Papel no Treinamento Corporativo</label>
                        <select wire:model="role" class="w-full bg-surface-container-low border border-outline-variant/60 rounded-xl px-3 py-2.5 font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary-container">
                            <option value="student">Colaborador / Aluno (Acesso aos Cursos e Trilhas)</option>
                            <option value="instructor">Instrutor Corporativo (Criação e Gestão de Conteúdos)</option>
                        </select>
                    </div>

                    <div class="flex items-center text-secondary font-label-sm text-label-sm pt-4 sm:pt-0">
                        <span>⏰ Convite com token seguro de 7 dias e vinculação automática ao tenant da empresa.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200/60 mt-1">
                    <button wire:click="toggleInviteModal" type="button" class="px-4 py-2 rounded-lg text-secondary hover:text-on-surface font-body-md text-body-md font-medium transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-lg bg-primary-container hover:bg-primary text-on-primary font-headline-sm text-headline-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                        <span wire:loading.remove wire:target="inviteEmployees">Disparar Convites</span>
                        <span wire:loading wire:target="inviteEmployees">Disparando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- SEÇÃO: CONVITES AGUARDANDO ATIVAÇÃO (Exibido na tab 'pending' ou quando há convites pendentes e tab 'all') -->
    @if(($tab === 'pending' || ($tab === 'all' && $pendingInvitations->isNotEmpty())) && $pendingInvitations->isNotEmpty())
        <div class="rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden flex flex-col border border-slate-200/60">
            <div class="px-space-md py-3 bg-surface-container-low flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary shrink-0 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <h2 class="font-headline-sm text-label-md font-semibold text-on-surface">Convites Aguardando Ativação</h2>
                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container text-secondary font-semibold">
                        {{ $pendingInvitations->count() }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/40 h-10 font-table-header text-table-header text-secondary uppercase tracking-wider border-b border-slate-200/60">
                            <th class="px-space-md py-2 font-semibold">E-mail Convidado</th>
                            <th class="px-space-md py-2 font-semibold">Papel</th>
                            <th class="px-space-md py-2 font-semibold">Validade</th>
                            <th class="px-space-md py-2 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-low font-body-md text-body-md text-on-surface">
                        @foreach($pendingInvitations as $invitation)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-space-md py-3 font-medium text-on-surface">
                                    {{ $invitation->email }}
                                </td>
                                <td class="px-space-md py-3">
                                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container text-on-surface font-semibold">
                                        {{ $invitation->role === 'instructor' ? 'Instrutor' : 'Colaborador' }}
                                    </span>
                                </td>
                                <td class="px-space-md py-3 text-secondary font-body-sm text-body-sm">
                                    @if($invitation->isExpired())
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-error-container text-error">
                                            Expirado
                                        </span>
                                    @else
                                        <span>Expira em {{ $invitation->expires_at->diffForHumans() }}</span>
                                    @endif
                                </td>
                                <td class="px-space-md py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="copyLink('{{ $invitation->getInvitationUrl() }}')" type="button"
                                                class="h-8 px-2.5 rounded-md bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md font-medium transition-colors flex items-center gap-1.5"
                                                title="Copiar link de ativação">
                                            <svg class="w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            <span>Copiar Link</span>
                                        </button>
                                        <button wire:click="resendInvitation({{ $invitation->id }})" type="button"
                                                class="h-8 px-2.5 rounded-md bg-surface-container-low hover:bg-surface-container text-primary font-label-md text-label-md font-medium transition-colors">
                                            Reenviar
                                        </button>
                                        <button wire:click="revokeInvitation({{ $invitation->id }})"
                                                wire:confirm="Tem certeza de que deseja revogar este convite para {{ $invitation->email }}?"
                                                type="button"
                                                class="h-8 px-2 rounded-md hover:bg-error-container text-secondary hover:text-error font-label-md text-label-md transition-colors"
                                                title="Revogar convite">
                                            <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TOOLBAR & FILTROS UNIFICADOS -->
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-sm pt-2">
        <!-- Segmented Tabs -->
        <div class="flex items-center p-1 rounded-lg bg-surface-container-low overflow-x-auto border border-slate-200/60">
            <button wire:click="setTab('all')"
                    class="px-space-sm py-1.5 rounded-md font-label-md text-label-md font-semibold whitespace-nowrap transition-all {{ $tab === 'all' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-secondary hover:text-on-surface' }}">
                Todos os Alunos ({{ $totalStudents }})
            </button>
            <button wire:click="setTab('pending')"
                    class="px-space-sm py-1.5 rounded-md font-label-md text-label-md font-medium whitespace-nowrap transition-colors {{ $tab === 'pending' ? 'bg-surface-container-lowest text-on-surface shadow-sm font-semibold' : 'text-secondary hover:text-on-surface' }}">
                Convites Pendentes ({{ $pendingCount }})
            </button>
            <button wire:click="setTab('at_risk')"
                    class="px-space-sm py-1.5 rounded-md font-label-md text-label-md font-medium whitespace-nowrap flex items-center gap-1.5 transition-colors {{ $tab === 'at_risk' ? 'bg-surface-container-lowest text-error shadow-sm font-semibold' : 'text-secondary hover:text-error' }}">
                <span>Em Risco de Evasão</span>
                <span class="px-1.5 py-0.2 rounded-full bg-error-container text-error text-xs font-bold">{{ $atRiskCount }}</span>
            </button>
            <button wire:click="setTab('completed')"
                    class="px-space-sm py-1.5 rounded-md font-label-md text-label-md font-medium whitespace-nowrap transition-colors {{ $tab === 'completed' ? 'bg-surface-container-lowest text-on-surface shadow-sm font-semibold' : 'text-secondary hover:text-on-surface' }}">
                Concluintes ({{ $completedCount }})
            </button>
        </div>

        <!-- Search & Complementary Dropdowns -->
        <div class="flex flex-wrap items-center gap-space-xs flex-1 lg:justify-end">
            <!-- Input de busca -->
            <div class="relative flex-1 sm:max-w-xs min-w-[240px]">
                <svg class="w-4 h-4 absolute left-3 top-3 text-secondary stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input wire:model.live.debounce.300ms="search"
                       class="w-full h-10 pl-9 pr-12 rounded-lg bg-surface-container-lowest font-body-md text-body-md text-on-surface placeholder:text-outline-variant shadow-sm border border-slate-200/80 focus:outline-none focus:ring-2 focus:ring-primary-container"
                       placeholder="Buscar por nome, e-mail..." type="text">
                <span class="absolute right-2.5 top-2.5 px-1.5 py-0.5 rounded font-label-sm text-label-sm bg-surface-container text-secondary font-mono">⌘K</span>
            </div>
        </div>
    </div>

    <!-- TABELA PRINCIPAL: ALUNOS ATIVOS & ENGAJAMENTO (Roster Corporativo) -->
    <div class="rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden flex flex-col border border-slate-200/60">
        <div class="px-space-lg py-space-md p-space-md flex items-center justify-between bg-surface-container-lowest border-b border-slate-200/60">
            <div class="flex items-center gap-2">
                <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Alunos Matriculados &amp; Turmas</h2>
                <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container-high text-on-surface font-semibold">
                    {{ $employees->total() }} registros
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-tertiary"></span>
                <span class="font-label-sm text-label-sm text-secondary">Telemetria preditiva ativa</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low h-11 font-table-header text-table-header text-secondary uppercase tracking-wider border-b border-slate-200/60">
                        <th class="px-space-md py-2 font-semibold">Aluno / Identificação</th>
                        <th class="px-space-md py-2 font-semibold">Cursos / Trilhas</th>
                        <th class="px-space-md py-2 font-semibold">Progresso Pedagógico</th>
                        <th class="px-space-md py-2 font-semibold">Último Acesso</th>
                        <th class="px-space-md py-2 font-semibold">Risco de Evasão (IA)</th>
                        <th class="px-space-md py-2 font-semibold">Status</th>
                        <th class="px-space-md py-2 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low font-body-md text-body-md text-on-surface">
                    @forelse($employees as $student)
                        @php
                            $risk = $student->latestRiskScore;
                            $score = $risk?->score ?? 0;
                            $level = $risk?->level ?? 'low';
                            $enrollmentCount = $student->enrollments->count();
                            $primaryEnrollment = $student->enrollments->first();
                            $avgProgress = $enrollmentCount > 0 ? round($student->enrollments->avg('progress_percent')) : 0;
                            $initials = strtoupper(substr($student->name, 0, 2));
                        @endphp
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Aluno / Identificação -->
                            <td class="px-space-md py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-headline-sm text-headline-sm font-semibold text-on-surface truncate">{{ $student->name }}</span>
                                            @if($risk && $risk->isHighRisk())
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-error-container text-error">ALERTA</span>
                                            @endif
                                        </div>
                                        <span class="font-body-sm text-body-sm text-secondary truncate">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Cursos / Trilhas -->
                            <td class="px-space-md py-4">
                                <div class="flex flex-col gap-0.5">
                                    @if($primaryEnrollment && $primaryEnrollment->course)
                                        <span class="font-medium text-on-surface truncate max-w-xs">{{ $primaryEnrollment->course->title }}</span>
                                        @if($enrollmentCount > 1)
                                            <span class="font-label-sm text-label-sm text-secondary">+ {{ $enrollmentCount - 1 }} curso(s) complementar(es)</span>
                                        @else
                                            <span class="font-label-sm text-label-sm text-secondary">Turma Corporativa</span>
                                        @endif
                                    @else
                                        <span class="font-body-sm text-body-sm text-secondary italic">Nenhum curso iniciado</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Progresso Pedagógico -->
                            <td class="px-space-md py-4">
                                <div class="flex flex-col gap-1.5 w-36">
                                    <div class="flex items-center justify-between font-label-sm text-label-sm">
                                        <span class="font-bold {{ $avgProgress >= 100 ? 'text-tertiary' : ($avgProgress < 30 ? 'text-error' : 'text-on-surface') }}">
                                            {{ $avgProgress }}%
                                        </span>
                                        <span class="text-secondary">{{ $enrollmentCount }} matrícula(s)</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-surface-container overflow-hidden">
                                        <div class="h-2 rounded-full {{ $avgProgress >= 100 ? 'bg-tertiary' : ($avgProgress < 30 ? 'bg-error' : 'bg-primary-container') }}" style="width: {{ min(100, $avgProgress) }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Último Acesso -->
                            <td class="px-space-md py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-on-surface font-body-sm text-body-sm">
                                        {{ $student->updated_at->diffForHumans() }}
                                    </span>
                                    <span class="font-label-sm text-label-sm text-secondary">
                                        Cadastrado em {{ $student->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Risco de Evasão (IA) -->
                            <td class="px-space-md py-4">
                                @if($risk)
                                    @if($risk->isHighRisk())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label-sm text-label-sm bg-error-container text-error font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                            Alto Risco {{ $score }}%
                                        </span>
                                    @elseif($risk->isMediumRisk())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                            Médio {{ $score }}%
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                                            Baixo {{ $score }}%
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label-sm text-label-sm bg-surface-container text-secondary font-medium">
                                        Não calculado
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-space-md py-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-2 w-2 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-tertiary opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-tertiary"></span>
                                    </span>
                                    <span class="font-label-md text-label-md font-medium text-on-surface">Ativo</span>
                                </div>
                            </td>

                            <!-- Ações -->
                            <td class="px-space-md py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($risk && $risk->isHighRisk())
                                        <a href="{{ route('company.situation-room') }}"
                                           class="h-8 px-2.5 rounded-md bg-error text-on-error hover:bg-error/90 font-label-md text-label-md font-medium transition-colors shadow-sm flex items-center gap-1">
                                            <span>Intervir IA</span>
                                        </a>
                                    @else
                                        <a href="{{ route('company.situation-room') }}"
                                           class="h-8 px-2.5 rounded-md bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md font-medium transition-colors flex items-center">
                                            Ver Desempenho
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-space-md py-12 text-center text-secondary">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-secondary/60 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                                    </svg>
                                    <span class="font-headline-sm text-body-md font-semibold text-on-surface">Nenhum colaborador encontrado</span>
                                    <span class="font-body-sm text-body-sm text-secondary">
                                        @if(!empty($search))
                                            Tente ajustar a sua busca por nome ou e-mail.
                                        @else
                                            Comece convidando membros do time através do botão "+ Convidar Alunos".
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINAÇÃO SHADCN -->
        @if($employees->hasPages())
            <div class="px-space-lg py-3.5 bg-surface-container-lowest flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm border-t border-slate-200/60">
                <div class="flex items-center gap-2">
                    <span class="font-body-sm text-body-sm text-secondary">
                        Mostrando <strong class="text-on-surface font-semibold">{{ $employees->firstItem() ?? 0 }} a {{ $employees->lastItem() ?? 0 }}</strong> de <strong class="text-on-surface font-semibold">{{ $employees->total() }}</strong> alunos
                    </span>
                </div>
                <div>
                    {{ $employees->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
