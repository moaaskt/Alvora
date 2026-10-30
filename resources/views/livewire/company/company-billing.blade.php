<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Flash & Error Messages -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (!empty($errorMessage))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold flex items-center justify-between">
                <span>{{ $errorMessage }}</span>
                <button wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-white">&times;</button>
            </div>
        @endif

        <!-- Alerta de Carência (D-03) -->
        @if ($company && $company->isOverdue())
            <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-white">Período de Carência Ativo (Tolerância de 5 dias)</h4>
                        <p class="text-xs text-amber-200 mt-0.5">Identificamos uma pendência na renovação da sua assinatura. Seus colaboradores continuam com acesso, mas regularize agora para evitar o bloqueio automático.</p>
                    </div>
                </div>
            </div>
        @elseif ($company && $company->isSuspended())
            <div class="p-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-white">Assinatura Corporativa Suspensa</h4>
                        <p class="text-xs text-rose-200 mt-0.5">O acesso dos colaboradores aos cursos e trilhas está temporariamente bloqueado por pendência financeira. Reative o plano abaixo para restabelecer os acessos.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card de Status da Assinatura e Utilização de Vagas (Ponto Focal) -->
        <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-800">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Assinatura Atual</span>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-white tracking-tight">
                            Plano {{ ucfirst($company->plan ?? 'Starter') }}
                        </h2>
                        @if ($company && $company->isActive())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Ativo
                            </span>
                        @elseif ($company && $company->isOverdue())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                Em Carência
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                Suspenso
                            </span>
                        @endif
                    </div>
                    @if ($activeSubscription && $activeSubscription->current_period_end)
                        <p class="text-xs text-slate-400">
                            Próxima renovação: <span class="text-white font-medium">{{ $activeSubscription->current_period_end->format('d/m/Y') }}</span>
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    @if ($company && $company->isActive())
                        <button wire:click="openCancelModal" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-400 hover:text-white hover:bg-rose-500/20 border border-rose-500/30 transition-colors">
                            Cancelar Assinatura
                        </button>
                    @endif
                </div>
            </div>

            <!-- Utilização de Vagas de Colaboradores -->
            <div class="pt-6">
                <div class="flex justify-between items-center text-xs mb-2">
                    <span class="text-slate-400 font-medium">Vagas de Colaboradores Ocupadas</span>
                    <span class="text-white font-semibold">{{ $currentUsersCount }} de {{ $maxUsers }} vagas ({{ $slotPercentage }}%)</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-3 rounded-full transition-all duration-500" style="width: {{ $slotPercentage }}%"></div>
                </div>
                @if ($currentUsersCount >= $maxUsers)
                    <p class="text-[11px] text-amber-400 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Você atingiu o limite de colaboradores do seu plano. Faça upgrade para o Plano Pro para cadastrar até 100 colaboradores.</span>
                    </p>
                @endif
            </div>
        </div>

        <!-- Seção de Upgrade e Mudança de Plano (D-04) -->
        <div class="space-y-4">
            <div>
                <h3 class="text-xl font-bold text-white tracking-tight">Planos Corporativos Disponíveis</h3>
                <p class="text-xs text-slate-400">Ao realizar upgrade, seu novo ciclo de 30 dias é reiniciado imediatamente com liberação instantânea de vagas e recursos.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- 1. Starter -->
                <div class="p-6 rounded-2xl bg-[#0F172A] border border-slate-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-slate-400">Até 20 Colaboradores</span>
                        <h4 class="text-xl font-bold text-white">Starter</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-extrabold text-white">R$ 490</span>
                            <span class="text-xs text-slate-400">/mês</span>
                        </div>
                        <ul class="space-y-2 text-xs text-slate-300 pt-2 border-t border-slate-800">
                            <li>✓ Cursos e aulas ilimitados</li>
                            <li>✓ Quizzes e certificados DomPDF</li>
                            <li>✓ Isolamento multi-tenant</li>
                        </ul>
                    </div>

                    <div class="pt-6">
                        @if ($company && $company->isStarter() && $company->isActive())
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-400 bg-slate-800 cursor-not-allowed">
                                Plano Atual
                            </button>
                        @else
                            <button wire:click="startCheckout('starter')" wire:loading.attr="disabled" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                                Assinar Plano Starter
                            </button>
                        @endif
                    </div>
                </div>

                <!-- 2. Pro + IA (Destaque) -->
                <div class="p-6 rounded-2xl bg-gradient-to-b from-indigo-950/60 to-slate-900 border-2 border-indigo-500 shadow-xl relative flex flex-col justify-between scale-[1.02]">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-gradient-to-r from-indigo-500 to-emerald-400 text-white font-extrabold text-[10px] uppercase tracking-wider">
                        Mais Recomendado
                    </div>
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-indigo-400">Até 100 Colaboradores</span>
                        <h4 class="text-xl font-bold text-white">Pro + IA</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-extrabold text-white">R$ 1.290</span>
                            <span class="text-xs text-slate-400">/mês</span>
                        </div>
                        <ul class="space-y-2 text-xs text-slate-200 pt-2 border-t border-indigo-900/50">
                            <li>✓ <strong>Tudo do Starter</strong> incluso</li>
                            <li>✓ <strong>Score Preditivo de Evasão</strong></li>
                            <li>✓ <strong>Intervenções ativas automáticas</strong></li>
                            <li>✓ Suporte prioritário via WhatsApp</li>
                        </ul>
                    </div>

                    <div class="pt-6">
                        @if ($company && $company->isPro() && $company->isActive())
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 cursor-not-allowed">
                                Plano Atual
                            </button>
                        @else
                            <button wire:click="startCheckout('pro')" wire:loading.attr="disabled" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-600/30 transition-all">
                                Assinar Plano Pro + IA
                            </button>
                        @endif
                    </div>
                </div>

                <!-- 3. Enterprise -->
                <div class="p-6 rounded-2xl bg-[#0F172A] border border-slate-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-slate-400">Mais de 100 Colaboradores</span>
                        <h4 class="text-xl font-bold text-white">Enterprise</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-extrabold text-white">Customizado</span>
                        </div>
                        <ul class="space-y-2 text-xs text-slate-300 pt-2 border-t border-slate-800">
                            <li>✓ <strong>Tudo do Pro + IA</strong></li>
                            <li>✓ Geração de cursos com fallback IA</li>
                            <li>✓ SLA de 99.9% e gerente de contas</li>
                        </ul>
                    </div>

                    <div class="pt-6">
                        <a href="{{ url('/#demonstracao') }}" class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-xl text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                            Solicitar Demonstração Enterprise
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico de Faturas B2B -->
        <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-4">
            <div>
                <h3 class="text-xl font-bold text-white tracking-tight">Histórico de Faturas</h3>
                <p class="text-xs text-slate-400">Demonstrativo de todas as cobranças processadas pelo Mercado Pago.</p>
            </div>

            @if ($invoices && $invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/60 border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Data</th>
                                <th class="py-3 px-4">ID Mercado Pago</th>
                                <th class="py-3 px-4">Valor</th>
                                <th class="py-3 px-4">Método</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Comprovante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($invoices as $inv)
                                <tr class="hover:bg-slate-900/40 transition-colors">
                                    <td class="py-3.5 px-4 font-medium text-white">{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3.5 px-4 font-mono text-slate-400">{{ $inv->mp_payment_id ?? 'Pendente' }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-white">R$ {{ number_format($inv->amount, 2, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 uppercase text-[10px] text-slate-400">{{ $inv->payment_method ?? 'Checkout Pro' }}</td>
                                    <td class="py-3.5 px-4">
                                        @if ($inv->isPaid())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Pago
                                            </span>
                                        @elseif ($inv->isPending())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                Pendente
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                Falhou
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if ($inv->isPaid())
                                            <span class="text-xs text-emerald-400 font-medium">Recibo Quitado</span>
                                        @else
                                            <span class="text-xs text-slate-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pt-4">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="py-12 text-center space-y-2">
                    <svg class="w-10 h-10 text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h4 class="text-sm font-bold text-white">Nenhuma fatura encontrada</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Sua organização ainda não gerou cobranças no ciclo atual. Escolha um dos planos corporativos acima para iniciar a capacitação da sua equipe.</p>
                </div>
            @endif
        </div>

        <!-- Modal de Confirmação de Cancelamento -->
        @if ($showCancelModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="bg-[#0F172A] border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-lg font-bold text-white">Cancelar Assinatura Corporativa</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Tem certeza de que deseja cancelar a assinatura da sua empresa? O acesso dos colaboradores aos cursos e trilhas será bloqueado ao final do período pago vigente.
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button wire:click="closeCancelModal" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition-colors">
                            Voltar
                        </button>
                        <button wire:click="cancelSubscription" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-colors">
                            Confirmar Cancelamento
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
