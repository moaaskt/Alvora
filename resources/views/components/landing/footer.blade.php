<footer class="bg-edtech-navy text-white pt-14 sm:pt-16 pb-12 border-t border-edtech-navy-soft">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12 pb-12 sm:pb-14 border-b border-edtech-navy-soft">

            <!-- COL 1: Brand & Overview (dark background logo) -->
            <div>
                <div class="mb-5">
                    <a href="{{ url('/') }}" class="inline-flex items-center focus:outline-none focus:ring-2 focus:ring-edtech-blue/40 rounded-lg">
                        <img src="{{ asset('images/logo-horizontal-dark.png') }}"
                             width="158"
                             height="36"
                             loading="lazy"
                             alt="Alvora"
                             class="h-8 sm:h-9 w-auto shrink-0">
                    </a>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed mb-6">
                    Plataforma de capacitação corporativa orientada à retenção com inteligência preditiva ativa. Projetada para empresas que investem estrategicamente no desenvolvimento de seus talentos.
                </p>
                <div class="flex items-center gap-2 text-xs text-edtech-blue-border">
                    <svg class="w-4 h-4 text-edtech-blue shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Conformidade com LGPD &amp; Criptografia Ponta a Ponta</span>
                </div>
            </div>

            <!-- COL 2: Catálogo B2B -->
            <div>
                <h4 class="text-xs font-bold text-slate-300 mb-4">Catálogo B2B</h4>
                <ul class="space-y-3 text-sm text-slate-400">
                    <li><a href="{{ url('/#cursos') }}" class="hover:text-white transition-colors">Cursos em Destaque</a></li>
                    <li><a href="{{ url('/#trilhas') }}" class="hover:text-white transition-colors">Trilhas por Departamento</a></li>
                    <li><a href="{{ url('/#docentes') }}" class="hover:text-white transition-colors">Corpo Docente</a></li>
                    <li><a href="{{ url('/#diferenciais') }}" class="hover:text-white transition-colors">Score Preditivo de Evasão</a></li>
                    <li><a href="{{ url('/#diferenciais') }}" class="hover:text-white transition-colors">Tutor IA Contextual</a></li>
                </ul>
            </div>

            <!-- COL 3: Empresa -->
            <div>
                <h4 class="text-xs font-bold text-slate-300 mb-4">Empresa</h4>
                <ul class="space-y-3 text-sm text-slate-400">
                    <li><a href="{{ url('/planos') }}" class="hover:text-white transition-colors">Planos Corporativos</a></li>
                    <li><a href="{{ url('/#apresentacao') }}" class="hover:text-white transition-colors">Solicitar Apresentação</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Termos de Serviço</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Privacidade de Dados</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Segurança da Informação</a></li>
                </ul>
            </div>

            <!-- COL 4: Acesso Corporativo -->
            <div>
                <h4 class="text-xs font-bold text-slate-300 mb-4">Acesso Corporativo</h4>
                <p class="text-sm text-slate-400 mb-4">
                    Sua empresa já é parceira Alvora? Acesse o portal do colaborador ou gestor de RH.
                </p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl bg-edtech-navy-soft hover:bg-edtech-blue text-white text-sm font-bold transition-colors border border-alvora-border">
                    Entrar na Plataforma
                </a>
            </div>

        </div>

        <!-- BOTTOM ROW -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <div>
                &copy; {{ date('Y') }} ALVORA Educação Corporativa S.A. Todos os direitos reservados.
            </div>
            <div>
                Desenvolvimento de liderança e alta performance com inteligência ativa.
            </div>
        </div>
    </div>
</footer>

