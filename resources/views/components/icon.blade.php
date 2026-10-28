@props(['name'])

<svg {{ $attributes->merge([
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'stroke-width' => '1.5',
    'viewBox' => '0 0 24 24',
    'aria-hidden' => 'true',
]) }}>
    @switch($name)
        @case('book-open')
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            @break

        @case('map')
            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
            <line x1="8" x2="8" y1="2" y2="18"></line>
            <line x1="16" x2="16" y1="6" y2="22"></line>
            @break

        @case('layout-grid')
            <rect height="7" width="7" x="3" y="3"></rect>
            <rect height="7" width="7" x="14" y="3"></rect>
            <rect height="7" width="7" x="14" y="14"></rect>
            <rect height="7" width="7" x="3" y="14"></rect>
            @break

        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" x2="9" y1="12" y2="12"></line>
            @break

        @case('clock')
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
            @break

        @case('check-circle')
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
            @break

        @case('bar-chart')
            <line x1="18" x2="18" y1="20" y2="10"></line>
            <line x1="12" x2="12" y1="20" y2="4"></line>
            <line x1="6" x2="6" y1="20" y2="14"></line>
            @break

        @case('message-circle')
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            @break

        @case('alert-triangle')
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" x2="12" y1="9" y2="13"></line>
            <line x1="12" x2="12.01" y1="17" y2="17"></line>
            @break

        @case('search')
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
            @break

        @case('plus')
            <line x1="12" x2="12" y1="5" y2="19"></line>
            <line x1="5" x2="19" y1="12" y2="12"></line>
            @break

        @case('x')
            <line x1="18" x2="6" y1="6" y2="18"></line>
            <line x1="6" x2="18" y1="6" y2="18"></line>
            @break
    @endswitch
</svg>

