<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-8 sm:p-12 text-center shadow-2xl relative overflow-hidden">
                <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/30 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20 mb-4">
                    Não Processado
                </span>

                <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">
                    Não Foi Possível Concluir o Pagamento
                </h1>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-xl mx-auto">
                    Falha ao processar a cobrança com o Mercado Pago. Verifique os dados do cartão de crédito informados ou selecione a opção de pagamento instantâneo via Pix.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ url('/planos') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 transition-all shadow-lg shadow-indigo-600/20">
                        Tentar Novamente
                    </a>
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                        Voltar ao Painel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
