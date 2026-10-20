<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-8 sm:p-12 text-center shadow-2xl relative overflow-hidden">
                <!-- Background Glow -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-32 bg-emerald-500/10 blur-3xl pointer-events-none -z-10"></div>

                <!-- Success Icon -->
                <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 rounded-full flex items-center justify-center mx-auto mb-6 text-emerald-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <!-- Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-4">
                    Pagamento Aprovado
                </span>

                <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">
                    Assinatura Corporativa Ativada com Sucesso!
                </h1>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-xl mx-auto">
                    Seu plano corporativo no Mercado Pago foi confirmado. As vagas para os colaboradores do seu time e os recursos contratados já estão disponíveis para uso imediato.
                </p>

                @if(!empty($paymentId))
                    <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 mb-8 text-xs text-slate-400 max-w-md mx-auto flex justify-between">
                        <span>ID da Transação:</span>
                        <span class="font-mono text-slate-200">{{ $paymentId }}</span>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 transition-all shadow-lg shadow-indigo-600/20">
                        Acessar Painel Principal
                    </a>
                    @if(Route::has('company.billing'))
                        <a href="{{ route('company.billing') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                            Ver Histórico de Faturamento
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
