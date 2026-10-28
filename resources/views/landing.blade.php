<x-guest-layout
    title="Alvora — Capacitação Corporativa com IA Preditiva"
    description="O Alvora calcula continuamente o Score de Risco de Evasão de cada colaborador e dispara intervenções pedagógicas automatizadas antes que a desistência aconteça."
    theme="editorial"
    :hideNav="true"
>
    <!-- TOP HEADER / NAVBAR -->
    <x-landing.navbar />

    <!-- HERO SECTION -->
    <x-landing.hero />

    <!-- FEATURE BAND -->
    <x-landing.feature-band />

    <!-- WHY ALVORA (COMPARISON SECTION) -->
    <x-landing.why-alvora />

    <!-- COURSE CATALOG SECTION -->
    <x-landing.course-catalog :showcaseCourses="$showcaseCourses ?? collect()" />

    <!-- INSTRUCTORS SECTION -->
    <x-landing.instructors :featuredInstructors="$featuredInstructors ?? collect()" />

    <!-- LEARNING TRACKS SECTION -->
    <x-landing.tracks :featuredTracks="$featuredTracks ?? collect()" />

    <!-- DEMO REQUEST CTA SECTION -->
    <x-landing.demo-cta />

    <!-- DARK NAVY FOOTER -->
    <x-landing.footer />

    <!-- Staggered Section Fade-Up Observer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('.reveal-section');
            if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                sections.forEach(s => s.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px'
            });

            sections.forEach(section => observer.observe(section));
        });
    </script>
</x-guest-layout>
