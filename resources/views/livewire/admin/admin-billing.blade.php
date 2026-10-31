<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">

    <!-- Header Title & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestão Global de Faturamento B2B</h1>
                <p class="text-slate-500 text-sm mt-1">Visão consolidada de receita recorrente mensal, contratos corporativos e auditoria de webhooks.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button"
                        wire:click="syncSubscriptions"
                        wire:loading.attr="disabled"
                        class="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 flex items-center gap-2.5 shadow-sm transition-colors text-sm disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="syncSubscriptions" class="inline-flex items-center gap-2">
                        <x-icon name="refresh-cw" class="w-4 h-4" />
                        <span>Sincronizar Assinaturas Mercado Pago</span>
                    </span>
                    <span wire:loading wire:target="syncSubscriptions" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Sincronizando...</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-semibold flex items-center gap-2">
                <x-icon name="check" class="w-4 h-4 shrink-0 text-green-600" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-semibold flex items-center gap-2">
                <x-icon name="alert-triangle" class="w-4 h-4 shrink-0 text-red-600" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: MRR -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-all">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">MRR Consolidado</span>
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700">Recorrência</span>
                    </div>
                    <div class="mt-4 flex items-baseline">
                        <span class="text-3xl font-extrabold text-slate-900 tabular-nums">R$ {{ number_format($mrr, 2, ',', '.') }}</span>
                        <span class="text-sm font-medium text-slate-500 ml-1.5">/mês</span>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 pt-3 border-t border-slate-100">Receita contratada com renovação automática no ciclo corrente.</p>
            </div>

            <!-- Card 2: Empresas Assinantes -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-all">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Empresas Assinantes</span>
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">Ativas</span>
                    </div>
                    <div class="mt-4 flex items-baseline">
                        <span class="text-3xl font-extrabold text-slate-900 tabular-nums">{{ $activeCompaniesCount }}</span>
                        <span class="text-base font-semibold text-slate-600 ml-2">empresas</span>
                    </div>
                </div>
                <p class="text-xs font-medium text-slate-500 mt-4 pt-3 border-t border-slate-100">{{ $starterCount }} Starter • {{ $proCount }} Pro + IA • {{ $enterpriseCount }} Enterprise</p>
            </div>

            <!-- Card 3: Risco & Inadimplência -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-all">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Risco &amp; Inadimplência</span>
                        @if ($overdueCompaniesCount > 0 || $suspendedCompaniesCount > 0)
                            <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700">Atenção</span>
                        @else
                            <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">Saudável</span>
                        @endif
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-2xl font-bold text-slate-900 tabular-nums block">{{ $overdueCompaniesCount }}</span>
                            <span class="text-xs text-slate-500">Em carência (5d)</span>
                        </div>
                        <div>
                            <span class="text-2xl font-bold text-slate-900 tabular-nums block">{{ $suspendedCompaniesCount }}</span>
                            <span class="text-xs text-slate-500">Suspensas</span>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 pt-3 border-t border-slate-100">
                    @if ($overdueCompaniesCount > 0 || $suspendedCompaniesCount > 0)
                        Contratos com falha de pagamento que exigem acompanhamento do time de suporte.
                    @else
                        Nenhuma fatura em atraso ou falha de cobrança nos últimos 30 dias.
                    @endif
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex-1 w-full sm:w-auto relative flex items-center bg-slate-50/70 border border-slate-200 rounded-xl px-3.5 py-2">
                <x-icon name="search" class="w-4 h-4 text-slate-400 shrink-0 mr-2.5 stroke-2" />
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por empresa ou CNPJ..."
                       class="bg-transparent border-none text-sm text-slate-800 focus:outline-none w-full placeholder:text-slate-400" />
                @if (!empty($search))
                    <button type="button"
                            wire:click="$set('search', '')"
                            class="text-slate-400 hover:text-slate-600 p-0.5 rounded transition-colors cursor-pointer"
                            title="Limpar busca">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto justify-end">
                <label class="text-xs font-semibold text-slate-500">Filtrar status:</label>
                <select wire:model.live="statusFilter"
                        class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                    <option value="all">Todas as Empresas</option>
                    <option value="active">Ativas</option>
                    <option value="overdue">Em Carência</option>
                    <option value="suspended">Suspensas</option>
                </select>
            </div>
        </div>

        <!-- Table of Contracts -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Contratos e Assinaturas Corporativas</h2>
                </div>
                <span class="text-[13px] font-medium text-slate-500">{{ $companies->total() }} empresas cadastradas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">Empresa / CNPJ</th>
                            <th class="py-3.5 px-6">Plano Contratado</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6">Valor Recorrente</th>
                            <th class="py-3.5 px-6">Último Pagamento</th>
                            <th class="py-3.5 px-6">Vagas Ocupadas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($companies as $comp)
                            @php
                                $lastSub = $comp->subscriptions->first();
                                $lastInvoice = $comp->invoices->first();
                                $initials = strtoupper(substr(trim($comp->name), 0, 2));
                                $usersCount = $comp->users()->count();
                                $isNearLimit = $comp->max_users > 0 && ($usersCount / $comp->max_users) >= 0.9;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Empresa / CNPJ -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-edtech-blue-light text-edtech-blue font-semibold flex items-center justify-center shrink-0 text-xs">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-900 block">{{ $comp->name }}</span>
                                            <span class="text-xs text-slate-500 font-mono">{{ $comp->cnpj ?? 'CNPJ não informado' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Plano Contratado -->
                                <td class="py-4 px-6">
                                    <span class="font-medium text-slate-900 block">Plano {{ ucfirst($comp->plan ?? 'Starter') }}</span>
                                    <span class="text-xs text-slate-500">Até {{ $comp->max_users }} vagas</span>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6">
                                    @if ($comp->status === 'active')
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">Ativo</span>
                                    @elseif ($comp->status === 'overdue')
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700">Em Carência (5d)</span>
                                    @else
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-600">Suspenso</span>
                                    @endif
                                </td>

                                <!-- Valor Recorrente -->
                                <td class="py-4 px-6">
                                    @if ($lastSub && $lastSub->amount > 0)
                                        <span class="font-semibold text-slate-900 tabular-nums">R$ {{ number_format($lastSub->amount, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-400 font-medium">—</span>
                                    @endif
                                </td>

                                <!-- Último Pagamento -->
                                <td class="py-4 px-6">
                                    @if ($lastInvoice && $lastInvoice->paid_at)
                                        <span class="font-medium text-slate-800 block text-xs tabular-nums">{{ $lastInvoice->paid_at->format('d/m/Y H:i') }}</span>
                                        <span class="text-xs text-slate-500 tabular-nums">R$ {{ number_format($lastInvoice->amount, 2, ',', '.') }}</span>
                                    @elseif ($lastSub && $lastSub->starts_at)
                                        <span class="font-medium text-slate-800 block text-xs tabular-nums">{{ $lastSub->starts_at->format('d/m/Y') }}</span>
                                        <span class="text-xs text-slate-500 tabular-nums">Início de ciclo</span>
                                    @else
                                        <span class="text-xs italic text-slate-400">Sem histórico</span>
                                    @endif
                                </td>

                                <!-- Vagas Ocupadas -->
                                <td class="py-4 px-6">
                                    <span class="font-medium tabular-nums {{ $isNearLimit ? 'text-amber-600 font-semibold' : 'text-edtech-navy' }}">
                                        {{ $usersCount }} / {{ $comp->max_users }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center">
                                    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center max-w-md mx-auto">
                                        <x-icon name="building-2" class="w-10 h-10 text-slate-400 mx-auto mb-3" />
                                        <h3 class="text-base font-semibold text-slate-800">Nenhuma empresa assinante ativa</h3>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            Ainda não constam empresas com assinaturas ativas processadas pelo Mercado Pago. Aguarde novas transações ou sincronize os webhooks.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação Livewire -->
            @if ($companies->hasPages())
                <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/40 text-xs">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
</div>
