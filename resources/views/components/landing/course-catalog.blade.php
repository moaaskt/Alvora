@props(['showcaseCourses' => collect()])

<section id="cursos" aria-labelledby="courses-heading" class="reveal-section py-16 sm:py-24 bg-edtech-bg" style="--reveal-delay: 160ms;">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-16">

        <!-- Section Header with Link (plain text, no eyebrow pill) -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 sm:mb-12 gap-4">
            <div class="max-w-2xl">
                <h2 id="courses-heading" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-edtech-text tracking-tight">
                    Catálogo de Cursos &amp; Programas Executivos
                </h2>
                <p class="text-sm sm:text-base text-edtech-muted mt-2">
                    Cursos estruturados com acompanhamento analítico em tempo real e certificação oficial para equipes de alta performance.
                </p>
            </div>
            <a href="#apresentacao" class="inline-flex items-center gap-1.5 text-sm font-bold text-edtech-blue hover:text-edtech-blue-hover transition-colors shrink-0">
                <span>Solicitar catálogo completo</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        @if(isset($showcaseCourses) && $showcaseCourses->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach($showcaseCourses as $index => $course)
                    @php
                        $coverThemes = [
                            ['category' => 'Programa Executivo', 'area' => 'Liderança & Estratégia'],
                            ['category' => 'Certificação Avançada', 'area' => 'Inteligência & Analytics'],
                            ['category' => 'Formação de Gestores', 'area' => 'Gestão de Pessoas & Cultura'],
                        ];
                        $meta = $coverThemes[$index % count($coverThemes)];
                    @endphp

                    <article class="group bg-edtech-surface rounded-[16px] border border-edtech-border shadow-sm hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div>
                            <!-- Banner / Cover Top with overflow-hidden and zoom 1.05 on hover -->
                            <div class="h-44 overflow-hidden relative bg-edtech-navy">
                                <div class="w-full h-full bg-gradient-to-br from-edtech-navy via-edtech-navy-soft to-edtech-navy p-5 flex flex-col justify-between text-white transition-transform duration-300 group-hover:scale-105">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-medium text-slate-300">{{ $meta['category'] }}</span>
                                        <img src="{{ asset('images/logo-symbol.png') }}"
                                             width="24"
                                             height="19"
                                             loading="lazy"
                                             alt="Alvora"
                                             class="h-4 w-auto opacity-85 shrink-0">
                                    </div>
                                    <div>
                                        <p class="text-xs text-edtech-blue-border font-medium mb-1">{{ $meta['area'] }}</p>
                                        <h3 class="text-lg font-bold text-white leading-tight line-clamp-2">{{ $course->title }}</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="p-6">
                                <!-- Instructor Avatar & Name -->
                                @if($course->instructor)
                                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-edtech-border-light">
                                        <div class="w-10 h-10 rounded-full overflow-hidden border border-edtech-border bg-edtech-surface shrink-0 flex items-center justify-center">
                                            <img src="{{ asset('images/logo-symbol-avatar.png') }}"
                                                 width="40"
                                                 height="40"
                                                 loading="lazy"
                                                 alt="Alvora"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-edtech-text truncate">{{ $course->instructor->name }}</div>
                                            <div class="text-xs text-edtech-muted">Especialista Corporativo Alvora</div>
                                        </div>
                                    </div>
                                @endif

                                @if($course->description)
                                    <p class="text-sm text-edtech-muted leading-relaxed line-clamp-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($course->description), 160) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer with Duration & Action Link -->
                        <div class="px-6 py-4 bg-edtech-bg border-t border-edtech-border-light flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-edtech-muted">
                                <svg class="w-4 h-4 text-edtech-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>
                                    {{ $course->lessons_count ?? 1 }} aulas
                                    @if(!empty($course->total_duration_minutes) && $course->total_duration_minutes > 0)
                                        · {{ $course->total_duration_minutes }} min
                                    @endif
                                </span>
                            </div>
                            <a href="#apresentacao" class="text-xs font-bold text-edtech-blue hover:text-edtech-blue-hover flex items-center gap-1">
                                <span>Capacitar Equipe</span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <!-- Estado Vazio Elegante (Fallback) -->
            <div class="p-8 sm:p-12 text-center rounded-2xl border border-dashed border-edtech-border bg-edtech-surface max-w-xl mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-edtech-blue-light text-edtech-blue flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-lg font-bold text-edtech-text">Catálogo em Atualização Curatorial</h3>
                <p class="text-xs sm:text-sm text-edtech-muted leading-relaxed">
                    Novos treinamentos corporativos estão sendo preparados pelo corpo docente. Entre em contato para conhecer as opções sob medida para o seu setor.
                </p>
            </div>
        @endif

    </div>
</section>

