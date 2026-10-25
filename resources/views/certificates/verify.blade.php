<x-guest-layout title="Validação de Certificado — ALVORA">
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
        <div class="bg-gray-900/80 border border-white/10 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl {{ $certificate ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }} mx-auto flex items-center justify-center">
                    @if ($certificate)
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @endif
                </div>

                @if ($certificate)
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Documento Oficial Autêntico ✓
                    </span>
                    <h1 class="text-2xl font-bold text-white">Certificado Válido e Registrado</h1>
                    <p class="text-xs text-gray-400">
                        Os registros da plataforma confirmam a emissão e a autenticidade deste certificado corporativo.
                    </p>
                @else
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        Código Não Localizado ✕
                    </span>
                    <h1 class="text-2xl font-bold text-white">Certificado Inválido ou Inexistente</h1>
                    <p class="text-xs text-gray-400">
                        O código informado não corresponde a nenhum documento emitido pela plataforma ALVORA.
                    </p>
                @endif
            </div>

            @if ($certificate)
                <div class="mt-8 space-y-4 pt-6 border-t border-white/5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl bg-gray-950/60 border border-white/5">
                            <span class="text-gray-500 uppercase tracking-wider block font-semibold">Colaborador / Aluno</span>
                            <span class="text-base font-bold text-white mt-1 block">{{ $certificate->user?->name ?? 'Colaborador' }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-950/60 border border-white/5">
                            <span class="text-gray-500 uppercase tracking-wider block font-semibold">Empresa Contratante</span>
                            <span class="text-base font-bold text-white mt-1 block">{{ $certificate->company?->name ?? 'Empresa Cliente' }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-950/60 border border-white/5 sm:col-span-2">
                            <span class="text-gray-500 uppercase tracking-wider block font-semibold">Curso Concluído</span>
                            <span class="text-base font-bold text-indigo-400 mt-1 block">{{ $certificate->course?->title ?? 'Curso Concluído' }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-950/60 border border-white/5">
                            <span class="text-gray-500 uppercase tracking-wider block font-semibold">Carga Horária Registrada</span>
                            <span class="text-sm font-semibold text-emerald-400 mt-1 block">{{ $certificate->formattedWorkload() }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-950/60 border border-white/5">
                            <span class="text-gray-500 uppercase tracking-wider block font-semibold">Data da Emissão</span>
                            <span class="text-sm font-semibold text-gray-300 mt-1 block">{{ $certificate->issued_at?->format('d/m/Y \à\s H:i') ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-indigo-950/20 border border-indigo-500/20 text-center">
                        <span class="text-[11px] text-gray-400">Código de Autenticação Único:</span>
                        <div class="font-mono text-sm font-bold text-indigo-300 mt-0.5 tracking-wider">
                            {{ $certificate->validation_code }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition">
                    &larr; Conhecer a plataforma ALVORA
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
