@props(['featuredInstructors' => collect()])

@if(isset($featuredInstructors) && $featuredInstructors->isNotEmpty())
<section id="docentes" aria-labelledby="instructors-heading" class="reveal-section py-16 sm:py-20 bg-edtech-surface border-t border-edtech-border" style="--reveal-delay: 200ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">

        <div class="max-w-2xl mb-10 sm:mb-12">
            <h2 id="instructors-heading" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-edtech-text tracking-tight">
                Líderes e Especialistas de Mercado
            </h2>
            <p class="text-sm sm:text-base text-edtech-muted mt-2">
                Profissionais com sólida trajetória em grandes empresas para desenvolver competências práticas nas equipes.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 max-w-4xl">
            @foreach($featuredInstructors as $inst)
                @php
                    $isAnalytics = str_contains($inst->name, 'Helena');
                    $roleSubtitle = $isAnalytics
                        ? 'Instrutora Executiva Alvora · Analytics & People Science'
                        : 'Instrutor Executivo Alvora · Liderança & Cultura';
                    $bioText = $isAnalytics
                        ? 'Doutora em Ciência de Dados aplicada a People Analytics, com publicações e consultorias estratégicas para o ecossistema corporate.'
                        : 'Mais de 15 anos acelerando lideranças executivas e estruturando modelos ágeis de gestão de pessoas em multinacionais.';
                    $specialtiesText = $isAnalytics
                        ? 'Modelos Preditivos · Business Intelligence'
                        : 'Liderança Situacional · Times Ágeis';
                @endphp

                <div class="group p-6 rounded-[20px] bg-edtech-bg border border-edtech-border flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 hover:border-edtech-blue-border hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-edtech-surface shadow-md shrink-0 bg-edtech-surface flex items-center justify-center">
                        <img src="{{ asset('images/logo-symbol-avatar.png') }}"
                             width="96"
                             height="96"
                             loading="lazy"
                             alt="Alvora"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </div>
                    <div class="text-center sm:text-left min-w-0">
                        <h3 class="text-lg font-bold text-edtech-text">{{ $inst->name }}</h3>
                        <p class="text-xs font-semibold text-edtech-blue mb-2">{{ $roleSubtitle }}</p>
                        <p class="text-sm text-edtech-muted leading-relaxed mb-3">
                            {{ $bioText }}
                        </p>
                        <p class="text-xs font-semibold text-edtech-text">
                            {{ $specialtiesText }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

