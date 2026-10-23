<x-app-layout title="Dashboard — ALVORA">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                    Olá, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Ambiente corporativo de capacitação &bull; 
                    <span class="text-emerald-400 font-semibold">{{ (auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin()) ? 'Instrutor' : auth()->user()->role?->label() }}</span>
                    @if(auth()->user()->company)
                        &bull; {{ auth()->user()->company->name }}
                    @endif
                </p>
            </div>

            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isInstructor())
                <a href="{{ route('employees.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-colors flex items-center gap-1.5 shadow-md shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Gerenciar Colaboradores
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Quick Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Status da Plataforma</span>
                <div class="text-2xl font-bold text-white mt-1 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Ativo
                </div>
                <span class="text-[11px] text-slate-500 mt-1 block">Conexão segura multi-tenant</span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Mecanismo Anti-Evasão</span>
                <div class="text-2xl font-bold text-indigo-400 mt-1">Calibrado</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Score de risco pronto para cálculo</span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Plano Contratado</span>
                <div class="text-2xl font-bold text-white mt-1 capitalize">
                    {{ auth()->user()->company?->plan ?? 'Plataforma Global' }}
                </div>
                <span class="text-[11px] text-slate-500 mt-1 block">
                    {{ auth()->user()->company ? auth()->user()->company->max_users . ' licenças disponíveis' : 'Acesso irrestrito Alvora' }}
                </span>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Tutor IA Groq</span>
                <div class="text-2xl font-bold text-emerald-400 mt-1">Pronto</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Latência ultra-baixa</span>
            </div>
        </div>

        <!-- Seção de Ações / Boas-Vindas -->
        <div class="p-8 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950/40 border border-slate-800">
            <h3 class="text-lg font-bold text-white mb-2">Fundação da Plataforma Inicializada</h3>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                Você está conectado com sucesso ao ambiente da <strong>ALVORA</strong>. 
                Nas próximas fases, você poderá gerenciar cursos, trilhas corporativas e acompanhar a sala de situação de retenção com intervenções inteligentes.
            </p>
        </div>
    </div>
</x-app-layout>
