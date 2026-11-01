@props(['name'])

@php
    static $iconSvgCache = [];

    $cleanName = str_replace('bi-', '', $name);

    $aliases = [
        'book-open' => 'book',
        'map' => 'map',
        'layout-grid' => 'grid',
        'log-out' => 'box-arrow-right',
        'log-in' => 'box-arrow-in-right',
        'clock' => 'clock',
        'clipboard-check' => 'clipboard-check',
        'check-circle' => 'check-circle-fill',
        'bar-chart' => 'bar-chart',
        'message-circle' => 'chat-dots',
        'alert-triangle' => 'exclamation-triangle-fill',
        'search' => 'search',
        'plus' => 'plus-lg',
        'x' => 'x-lg',
        'arrow-left' => 'arrow-left',
        'chevron-left' => 'chevron-left',
        'chevron-right' => 'chevron-right',
        'play' => 'play-fill',
        'paperclip' => 'paperclip',
        'file-text' => 'file-earmark-text',
        'download' => 'download',
        'lock' => 'lock-fill',
        'info' => 'info-circle-fill',
        'pencil' => 'pencil-fill',
        'check' => 'check-lg',
        'x-circle' => 'x-circle-fill',
        'award' => 'award-fill',
        'rotate-ccw' => 'arrow-counterclockwise',
        'reactivate' => 'arrow-counterclockwise',
        'copy' => 'copy',
        'duplicate' => 'copy',
        'star' => 'star-fill',
        'trash' => 'trash3-fill',
        'trash-2' => 'trash3-fill',
        'refresh-cw' => 'arrow-repeat',
        'building' => 'building',
        'building-2' => 'buildings',
        'arrow-right' => 'arrow-right',
        'shield' => 'shield-check',
        'users' => 'people-fill',
        'user' => 'person-fill',
        'video' => 'camera-video-fill',
        'trending-up' => 'graph-up-arrow',
        'zap' => 'lightning-charge-fill',
        'cpu' => 'cpu-fill',
        'activity' => 'activity',
        'share-2' => 'share-fill',
        'help-circle' => 'question-circle-fill',
    ];

    $iconName = $aliases[$cleanName] ?? $cleanName;

    if (!isset($iconSvgCache[$iconName])) {
        $path = base_path("node_modules/bootstrap-icons/icons/{$iconName}.svg");
        if (file_exists($path)) {
            $raw = file_get_contents($path);
            if (preg_match('/<svg[^>]*>(.*)<\/svg>/is', $raw, $matches)) {
                $iconSvgCache[$iconName] = trim($matches[1]);
            } else {
                $iconSvgCache[$iconName] = null;
            }
        } else {
            $iconSvgCache[$iconName] = null;
        }
    }

    $inner = $iconSvgCache[$iconName];
@endphp

@if($inner)
    <svg {{ $attributes->merge([
        'fill' => 'currentColor',
        'viewBox' => '0 0 16 16',
        'aria-hidden' => 'true',
    ]) }}>
        {!! $inner !!}
    </svg>
@else
    <i {{ $attributes->merge(['class' => "bi bi-{$iconName} inline-block"]) }} aria-hidden="true"></i>
@endif
