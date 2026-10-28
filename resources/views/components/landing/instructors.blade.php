@props(['featuredInstructors' => collect()])

@if(isset($featuredInstructors) && $featuredInstructors->isNotEmpty())
@php
    $count = $featuredInstructors->count();
    $gridClasses = $count < 3
        ? 'flex flex-col md:flex-row justify-center items-stretch gap-6 sm:gap-8 max-w-4xl mx-auto'
        : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8';
@endphp

<section id="docentes" aria-labelledby="instructors-heading" class="reveal-section py-16 sm:py-20 bg-edtech-surface border-t border-edtech-border" style="--reveal-delay: 200ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">

        <!-- Section Header (left-aligned) -->
        <div class="max-w-2xl mb-10 sm:mb-12 text-left">
            <h2 id="instructors-heading" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-edtech-text tracking-tight text-left">
                Líderes e Especialistas de Mercado
            </h2>
            <p class="text-base sm:text-lg text-edtech-muted mt-2 text-left">
                Profissionais com sólida trajetória em grandes empresas para desenvolver competências práticas nas equipes.
            </p>
        </div>

        <div class="{{ $gridClasses }}">
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

                    $initials = collect(explode(' ', $inst->name))
                        ->map(fn($part) => mb_substr($part, 0, 1))
                        ->take(2)
                        ->join('');
                @endphp

                <div class="group p-6 sm:p-8 rounded-[20px] bg-edtech-bg border border-edtech-border flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 hover:border-edtech-blue-border hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 w-full {{ $count < 3 ? 'max-w-lg' : '' }}">
                    <!-- Circular avatar with initials (navy background, white text) -->
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-edtech-navy text-white flex items-center justify-center font-bold text-xl sm:text-2xl border-2 border-edtech-surface shadow-md shrink-0 select-none" title="{{ $inst->name }}">
                        <span>{{ $initials ?: 'AV' }}</span>
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
