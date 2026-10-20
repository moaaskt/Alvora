<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0F172A] border border-slate-800 rounded-2xl p-8 sm:p-12 text-center shadow-2xl relative overflow-hidden">
                <div class="w-16 h-16 bg-amber-500/10 border border-amber-500/30 rounded-full flex items-center justify-center mx-auto mb-6 text-amber-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-4">
                    Aguardando Compensação
                </span>

                <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">
                    Pagamento em Análise
                </h1>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-xl mx-auto">
                    Recebemos o registro do seu pedido. Caso tenha pago via Pix ou Boleto, a confirmação ocorre assim que o Mercado Pago liquidar a transação e seus acessos serão liberados automaticamente.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                        Voltar ao Painel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
