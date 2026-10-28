@props(['featuredTracks' => collect()])

@if(isset($featuredTracks) && $featuredTracks->isNotEmpty())
<section id="trilhas" aria-labelledby="tracks-heading" class="reveal-section py-16 sm:py-20 bg-edtech-bg border-t border-edtech-border" style="--reveal-delay: 220ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">

        <div class="max-w-2xl mb-10 sm:mb-12">
            <h2 id="tracks-heading" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-edtech-text tracking-tight">
                Caminhos de Carreira por Departamento
            </h2>
            <p class="text-sm sm:text-base text-edtech-muted mt-2 leading-relaxed">
                Sequências pedagógicas organizadas para acelerar o desenvolvimento de competências específicas da sua empresa.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach($featuredTracks as $track)
                <div class="group p-6 rounded-2xl bg-edtech-surface border border-edtech-border flex flex-col justify-between hover:border-edtech-blue-border hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300">
                    <div>
                        @if($track->department)
                            <p class="text-xs font-semibold text-edtech-blue mb-2">
                                {{ $track->department }}
                            </p>
                        @endif
                        <h3 class="text-lg font-bold text-edtech-text group-hover:text-edtech-blue transition-colors">{{ $track->title }}</h3>
                        @if($track->description)
                            <p class="text-xs text-edtech-muted mt-2 line-clamp-2 leading-relaxed">{{ $track->description }}</p>
                        @endif
                    </div>
                    <div class="pt-4 mt-4 border-t border-edtech-border-light text-xs text-edtech-muted flex items-center justify-between">
                        <span>{{ $track->courses_count ?? 0 }} cursos</span>
                        <span class="font-semibold text-edtech-text">{{ $track->is_mandatory ? 'Obrigatória' : 'Eletiva' }}</span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

