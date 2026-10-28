@props(['featuredTracks' => collect()])

@if(isset($featuredTracks) && $featuredTracks->isNotEmpty())
@php
    $count = $featuredTracks->count();
    $gridClasses = $count < 3
        ? 'flex flex-col md:flex-row justify-center items-stretch gap-5 sm:gap-6 max-w-4xl mx-auto'
        : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6';
@endphp

<section id="trilhas" aria-labelledby="tracks-heading" class="reveal-section py-16 sm:py-20 bg-edtech-bg border-t border-edtech-border" style="--reveal-delay: 220ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">

        <!-- Section Header (left-aligned) -->
        <div class="max-w-2xl mb-10 sm:mb-12 text-left">
            <!-- Section H2: 36-40px, weight 600, letter-spacing -0.015em -->
            <h2 id="tracks-heading" class="text-3xl sm:text-[38px] lg:text-[40px] leading-[1.15] font-semibold text-edtech-text tracking-[-0.015em] text-left">
                Caminhos de Carreira por Departamento
            </h2>
            <p class="text-base sm:text-lg text-edtech-muted mt-2 leading-[1.6] text-left">
                Sequências pedagógicas organizadas para acelerar o desenvolvimento de competências específicas da sua empresa.
            </p>
        </div>

        <div class="{{ $gridClasses }}">
            @foreach($featuredTracks as $track)
                <div class="group p-6 sm:p-7 rounded-2xl bg-edtech-surface border border-edtech-border flex flex-col justify-between hover:border-edtech-blue-border hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 w-full {{ $count < 3 ? 'max-w-md' : '' }}">
                    <div>
                        @if($track->department)
                            <p class="text-[13px] font-semibold uppercase tracking-wider text-edtech-blue mb-2">
                                {{ $track->department }}
                            </p>
                        @endif
                        <!-- Card titles: 18-20px, weight 600 -->
                        <h3 class="text-[19px] font-semibold text-edtech-text group-hover:text-edtech-blue transition-colors">{{ $track->title }}</h3>
                        @if($track->description)
                            <p class="text-[15px] sm:text-base text-edtech-muted mt-2.5 line-clamp-2 leading-[1.6]">{{ $track->description }}</p>
                        @endif
                    </div>
                    <div class="pt-4 mt-5 border-t border-edtech-border-light text-[13px] text-edtech-muted flex items-center justify-between">
                        <span class="tabular-nums">{{ $track->courses_count ?? 0 }} cursos</span>
                        <span class="font-semibold text-edtech-text">{{ $track->is_mandatory ? 'Obrigatória' : 'Eletiva' }}</span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif
