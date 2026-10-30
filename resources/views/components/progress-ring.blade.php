@props(['percent' => 0, 'alpineModel' => null])

@php
    $pct = max(0, min(100, (int) $percent));
    $radius = 9.5;
    $circumference = 59.7;
    $offset = round($circumference * (1 - ($pct / 100)), 1);
@endphp

<svg {{ $attributes->merge([
    'width' => '24',
    'height' => '24',
    'viewBox' => '0 0 24 24',
    'role' => 'img',
    'aria-label' => 'Aula atual',
    'class' => 'shrink-0',
]) }}>
    {{-- Background Track (fundo cinza/azul claro da circunferência) --}}
    <circle cx="12" cy="12" r="{{ $radius }}" stroke="var(--color-edtech-blue-border, #BFDBFE)" stroke-width="2.5" fill="none" class="stroke-edtech-blue-border" />

    {{-- Progress Arc (gira no sentido horário a partir das 12h conforme o vídeo avança) --}}
    @if ($alpineModel)
        <circle cx="12" cy="12" r="{{ $radius }}"
                stroke="var(--color-edtech-blue, #2563EB)"
                stroke-width="2.5"
                stroke-linecap="round"
                fill="none"
                stroke-dasharray="{{ $circumference }}"
                :stroke-dashoffset="{{ $circumference }} * (1 - (Math.min(100, Math.max(0, {{ $alpineModel }})) / 100))"
                :style="{ strokeDashoffset: ({{ $circumference }} * (1 - (Math.min(100, Math.max(0, {{ $alpineModel }})) / 100))) + 'px' }"
                transform="rotate(-90 12 12)"
                class="stroke-edtech-blue transition-all duration-300" />
    @else
        @if ($pct > 0)
            <circle cx="12" cy="12" r="{{ $radius }}"
                    stroke="var(--color-edtech-blue, #2563EB)"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    fill="none"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    style="stroke-dashoffset: {{ $offset }}px;"
                    transform="rotate(-90 12 12)"
                    class="stroke-edtech-blue transition-all duration-300" />
        @endif
    @endif

    {{-- Center Solid Play Triangle --}}
    <polygon points="10.5,8.5 15.5,12 10.5,15.5" class="fill-edtech-blue" />
</svg>
