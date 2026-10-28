@php
    // Caminho único para troca da foto principal do Hero
    $heroImagePath = asset('images/landing/hero.webp');
@endphp

<section aria-labelledby="hero-heading" class="reveal-section relative pt-10 sm:pt-12 lg:pt-16 pb-20 lg:pb-28 overflow-visible bg-edtech-bg" style="--reveal-delay: 0ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

            <!-- LEFT COLUMN: Content -->
            <div class="lg:col-span-7 flex flex-col items-start pr-0 lg:pr-6">
                <h1 id="hero-heading" class="text-3xl sm:text-5xl lg:text-[58px] leading-[1.14] lg:leading-[1.12] font-bold text-edtech-text tracking-tight mb-6">
                    Capacitação corporativa de alto impacto com <span class="text-edtech-blue">previsão ativa</span> de retenção.
                </h1>

                <p class="text-base sm:text-lg text-edtech-muted leading-relaxed mb-8 max-w-2xl font-normal">
                    O Alvora calcula continuamente o Score de Risco de Evasão de cada colaborador e dispara intervenções pedagógicas automatizadas antes que a desistência aconteça.
                </p>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 sm:gap-4 w-full sm:w-auto mb-10">
                    <a href="#apresentacao" class="inline-flex items-center justify-center gap-2 bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-base px-7 py-4 rounded-xl transition-all shadow-md shadow-edtech-blue/30 focus:outline-none focus:ring-2 focus:ring-edtech-blue/50">
                        <span>Solicitar Demonstração</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a href="{{ url('/planos') }}" class="inline-flex items-center justify-center gap-2 bg-edtech-surface hover:bg-edtech-bg text-edtech-text font-semibold text-base px-6 py-4 rounded-xl border border-edtech-border shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-edtech-blue/30">
                        <svg class="w-5 h-5 text-edtech-blue shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Conhecer planos corporativos</span>
                    </a>
                </div>

                <!-- Inline trust row -->
                <div class="flex flex-wrap items-center gap-5 sm:gap-8 pt-2">
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-edtech-text leading-tight">+78%</span>
                        <span class="text-xs text-edtech-muted mt-0.5">conclusão estimada</span>
                    </div>
                    <div class="w-px h-8 bg-edtech-border" aria-hidden="true"></div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-edtech-text leading-tight">&lt; 3s</span>
                        <span class="text-xs text-edtech-muted mt-0.5">resposta do tutor IA</span>
                    </div>
                    <div class="w-px h-8 bg-edtech-border" aria-hidden="true"></div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-edtech-text leading-tight">100%</span>
                        <span class="text-xs text-edtech-muted mt-0.5">conformidade LGPD</span>
                    </div>
                </div>

                <!-- Small slate footnote -->
                <p class="text-xs text-edtech-muted mt-3">
                    Estimativas baseadas em simulação do modelo preditivo.
                </p>
            </div>

            <!-- RIGHT COLUMN: Visual Hero with Photo & Plain Floating Cards -->
            <div class="lg:col-span-5 relative flex justify-center lg:justify-end mt-4 lg:mt-0">
                <div class="relative w-full max-w-[340px] sm:max-w-[420px] lg:max-w-[460px] aspect-[4/4.8]">

                    <!-- Rounded Gradient Backdrop Shape -->
                    <div class="absolute inset-0 rounded-[32px] bg-gradient-to-br from-edtech-blue via-edtech-blue-dark to-edtech-navy overflow-hidden shadow-2xl">
                        <!-- Subtle decorative line pattern inside gradient -->
                        <svg class="absolute inset-0 w-full h-full opacity-20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <defs>
                                <pattern id="hero-grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"></path>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#hero-grid-pattern)"></rect>
                            <circle cx="80%" cy="20%" r="140" fill="none" stroke="white" stroke-width="1.5" opacity="0.3"></circle>
                            <circle cx="20%" cy="80%" r="180" fill="none" stroke="white" stroke-width="1.5" opacity="0.2"></circle>
                        </svg>
                    </div>

                    <!-- Corporate Person Photo (single defined raster path) -->
                    <img src="{{ $heroImagePath }}"
                         width="920"
                         height="1104"
                         fetchpriority="high"
                         alt="Colaboradora corporativa usando Alvora com laptop"
                         class="relative z-10 w-full h-full object-cover rounded-[32px] transform scale-[1.02] -translate-y-1">

                    <!-- Overlapping plain floating UI card 1: Score de Risco -->
                    <div class="hero-float-1 absolute -top-4 -left-2 sm:-top-5 sm:-left-6 z-20 bg-edtech-surface/95 backdrop-blur-md border border-edtech-border shadow-xl p-3.5 sm:p-4 rounded-2xl w-[185px] sm:w-[210px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-edtech-muted">Score de Risco</span>
                            <span class="text-xs font-semibold text-alvora-emerald">Baixo</span>
                        </div>
                        <div class="flex items-baseline gap-1 mb-1.5">
                            <span class="text-xl font-bold text-edtech-text">15</span>
                            <span class="text-xs text-edtech-muted">/ 100 pts</span>
                        </div>
                        <div class="w-full bg-edtech-border-light rounded-full h-1.5 overflow-hidden">
                            <div class="bg-alvora-emerald h-1.5 rounded-full" style="width: 15%"></div>
                        </div>
                    </div>

                    <!-- Overlapping plain floating UI card 2: Intervenção enviada -->
                    <div class="hero-float-2 absolute top-[42%] -right-2 sm:-right-6 z-20 bg-edtech-surface/95 backdrop-blur-md border border-edtech-border shadow-xl p-3 sm:p-3.5 rounded-2xl max-w-[195px] sm:max-w-[220px]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-edtech-blue-light border border-edtech-blue-border text-edtech-blue flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-edtech-text leading-tight">Intervenção enviada</div>
                                <div class="text-[10px] text-edtech-muted font-medium">Micro-revisão no Slack</div>
                            </div>
                        </div>
                    </div>

                    <!-- Overlapping plain floating UI card 3: Mini chart Conclusão -->
                    <div class="hero-float-3 absolute -bottom-4 -left-2 sm:-bottom-5 sm:-left-4 z-20 bg-edtech-surface/95 backdrop-blur-md border border-edtech-border shadow-xl p-3.5 sm:p-4 rounded-2xl w-[200px] sm:w-[220px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-edtech-muted">Conclusão Alvo</span>
                            <span class="text-xs font-bold text-edtech-blue">+78%</span>
                        </div>
                        <div class="flex items-end gap-1.5 h-8 pt-1">
                            <div class="w-3 bg-edtech-border rounded-t h-3"></div>
                            <div class="w-3 bg-edtech-border rounded-t h-4"></div>
                            <div class="w-3 bg-slate-300 rounded-t h-5"></div>
                            <div class="w-3 bg-edtech-blue-border rounded-t h-6"></div>
                            <div class="w-3 bg-edtech-blue rounded-t h-8"></div>
                            <div class="w-3 bg-edtech-blue rounded-t h-7"></div>
                            <div class="w-3 bg-edtech-blue rounded-t h-8"></div>
                            <span class="text-[10px] font-semibold text-alvora-emerald ml-auto leading-none">Meta atingida</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

