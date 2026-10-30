<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header da Página -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        Painel Executivo B2B
                    </span>
                    <span class="text-xs text-slate-500">Mercado Pago Gateway</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                    Gestão Global de Faturamento B2B
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    Visão consolidada de receita recorrente mensal, contratos corporativos e auditoria de webhooks.
                </p>
            </div>

            <!-- Botão de Sincronização Manual (Copywriting Contract) -->
            <div>
                <button wire:click="syncSubscriptions" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all disabled:opacity-50">
                    <svg wire:loading.remove wire:target="syncSubscriptions" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg wire:loading wire:target="syncSubscriptions" class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Sincronizar Assinaturas Mercado Pago</span>
                </button>
            </div>
        </div>

        <!-- Flash & Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Ponto Focal: Grid de KPIs Consolidados -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- KPI 1: MRR (Display 32px) -->
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-6 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">MRR Consolidado</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Recorrência</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-[32px] font-extrabold text-white tracking-tight leading-none">
                        R$ {{ number_format($mrr, 2, ',', '.') }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">/mês</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Receita recorrente ativa gerada por contratos corporativos vigentes.
                </p>
            </div>

            <!-- KPI 2: Clientes Ativos & Mix de Planos -->
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-6 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Empresas Assinantes</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ativas</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-[32px] font-extrabold text-emerald-400 tracking-tight leading-none">
                        {{ $activeCompaniesCount }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">empresas</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-800 flex items-center gap-3 text-[11px] text-slate-300">
                    <span><strong class="text-white">{{ $starterCount }}</strong> Starter</span>
                    <span>•</span>
                    <span><strong class="text-indigo-400">{{ $proCount }}</strong> Pro + IA</span>
                    <span>•</span>
                    <span><strong class="text-emerald-400">{{ $enterpriseCount }}</strong> Enterprise</span>
                </div>
            </div>

            <!-- KPI 3: Inadimplência & Carência -->
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-6 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Risco & Inadimplência</span>
                    @if ($overdueCompaniesCount > 0 || $suspendedCompaniesCount > 0)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Atenção</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Saudável</span>
                    @endif
                </div>
                <div class="mt-3 flex items-baseline gap-4">
                    <div>
                        <span class="text-2xl sm:text-[32px] font-extrabold text-amber-400 tracking-tight leading-none">
                            {{ $overdueCompaniesCount }}
                        </span>
                        <span class="text-xs text-slate-400 block mt-1">Em carência (5d)</span>
                    </div>
                    <div class="border-l border-slate-800 pl-4">
                        <span class="text-2xl sm:text-[32px] font-extrabold text-rose-400 tracking-tight leading-none">
                            {{ $suspendedCompaniesCount }}
                        </span>
                        <span class="text-xs text-slate-400 block mt-1">Suspensas</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Contratos com falha de pagamento que exigem acompanhamento do time de suporte.
                </p>
            </div>

        </div>

        <!-- Filtros & Busca -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
            <div class="relative w-full sm:w-80">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por empresa ou CNPJ..."
                    class="w-full bg-[#0F172A] border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                />
                @if (!empty($search))
                    <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-xs">&times;</button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Filtrar status:</span>
                <select
                    wire:model.live="statusFilter"
                    class="bg-[#0F172A] border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500"
                >
                    <option value="all">Todos os Status</option>
                    <option value="active">Ativas</option>
                    <option value="overdue">Em Carência</option>
                    <option value="suspended">Suspensas</option>
                </select>
            </div>
        </div>

        <!-- Tabela de Auditoria de Empresas Clientes -->
        <div class="bg-[#0F172A] border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Contratos e Assinaturas Corporativas</h3>
                <span class="text-xs text-slate-400">{{ $companies->total() }} empresas cadastradas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/50 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Empresa / CNPJ</th>
                            <th class="px-6 py-3.5">Plano Contratado</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Valor Recorrente</th>
                            <th class="px-6 py-3.5">Último Pagamento</th>
                            <th class="px-6 py-3.5">Vagas Ocupadas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($companies as $comp)
                            @php
                                $lastSub = $comp->subscriptions->first();
                                $lastInvoice = $comp->invoices->first();
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <!-- Empresa / CNPJ -->
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-white">{{ $comp->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $comp->cnpj ?? 'CNPJ não informado' }}</div>
                                </td>

                                <!-- Plano Contratado -->
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-white">Plano {{ ucfirst($comp->plan ?? 'Starter') }}</span>
                                    <div class="text-[11px] text-slate-400">Até {{ $comp->max_users }} vagas</div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    @if ($comp->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Ativo
                                        </span>
                                    @elseif ($comp->status === 'overdue')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Em Carência (5d)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Suspenso
                                        </span>
                                    @endif
                                </td>

                                <!-- Valor Recorrente -->
                                <td class="px-6 py-4 font-semibold text-white font-mono">
                                    @if ($lastSub && $lastSub->amount > 0)
                                        R$ {{ number_format($lastSub->amount, 2, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- Último Pagamento -->
                                <td class="px-6 py-4 text-slate-400">
                                    @if ($lastInvoice && $lastInvoice->paid_at)
                                        <div>{{ $lastInvoice->paid_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-[10px] text-emerald-400">R$ {{ number_format($lastInvoice->amount, 2, ',', '.') }}</div>
                                    @elseif ($lastSub && $lastSub->starts_at)
                                        <div>{{ $lastSub->starts_at->format('d/m/Y') }}</div>
                                        <div class="text-[10px] text-slate-500">Início de ciclo</div>
                                    @else
                                        <span class="text-slate-600">Sem histórico</span>
                                    @endif
                                </td>

                                <!-- Vagas Ocupadas -->
                                <td class="px-6 py-4 text-slate-300">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-white">{{ $comp->users()->count() }}</span>
                                        <span class="text-slate-500">/ {{ $comp->max_users }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State Oficial do Copywriting Contract -->
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="max-w-md mx-auto space-y-2">
                                        <div class="w-12 h-12 rounded-full bg-slate-800 mx-auto flex items-center justify-center text-slate-500 mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-white">Nenhuma empresa assinante ativa</h4>
                                        <p class="text-xs text-slate-400 leading-relaxed">
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
                <div class="px-6 py-4 border-t border-slate-800">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
