@props(['content', 'settings'])

@php
    $style = $settings['style'] ?? 'line';
    $thickness = $settings['thickness'] ?? 'medium';
    $color = $settings['color'] ?? '#e5e7eb';
    $spacing = $settings['spacing'] ?? 'medium';
    $width = $settings['width'] ?? 'full';
    $alignment = $settings['alignment'] ?? 'center';
    $text = $content['text'] ?? '';
    $icon = $content['icon'] ?? '';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['divider-block'];

    // Spacing classes
    switch($spacing) {
        case 'small':
            $containerClasses[] = 'my-4';
            break;
        case 'large':
            $containerClasses[] = 'my-12';
            break;
        case 'xl':
            $containerClasses[] = 'my-16';
            break;
        default:
            $containerClasses[] = 'my-8';
    }

    // Alignment classes
    switch($alignment) {
        case 'left':
            $containerClasses[] = 'text-left';
            break;
        case 'right':
            $containerClasses[] = 'text-right';
            break;
        default:
            $containerClasses[] = 'text-center';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build divider classes
    $dividerClasses = [];

    // Thickness classes
    switch($thickness) {
        case 'thin':
            $dividerClasses[] = 'border-t';
            break;
        case 'thick':
            $dividerClasses[] = 'border-t-4';
            break;
        case 'extra-thick':
            $dividerClasses[] = 'border-t-8';
            break;
        default:
            $dividerClasses[] = 'border-t-2';
    }

    // Width classes
    switch($width) {
        case 'small':
            $dividerClasses[] = 'w-16';
            if ($alignment === 'center') $dividerClasses[] = 'mx-auto';
            elseif ($alignment === 'right') $dividerClasses[] = 'ml-auto';
            break;
        case 'medium':
            $dividerClasses[] = 'w-32';
            if ($alignment === 'center') $dividerClasses[] = 'mx-auto';
            elseif ($alignment === 'right') $dividerClasses[] = 'ml-auto';
            break;
        case 'large':
            $dividerClasses[] = 'w-64';
            if ($alignment === 'center') $dividerClasses[] = 'mx-auto';
            elseif ($alignment === 'right') $dividerClasses[] = 'ml-auto';
            break;
        default:
            $dividerClasses[] = 'w-full';
    }

    // Build inline styles for custom color
    $borderStyle = $color ? "border-color: {$color}" : '';
@endphp

<div class="{{ implode(' ', $containerClasses) }}"
     @if($elementId) id="{{ $elementId }}" @endif>

    @switch($style)
        @case('line')
            <hr class="{{ implode(' ', $dividerClasses) }}"
                @if($borderStyle) style="{{ $borderStyle }}" @endif>
            @break

        @case('dots')
            <div class="flex items-center justify-{{ $alignment === 'left' ? 'start' : ($alignment === 'right' ? 'end' : 'center') }}">
                <div class="flex space-x-2">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                </div>
            </div>
            @break

        @case('asterisk')
            <div class="text-2xl text-gray-400">
                * * *
            </div>
            @break

        @case('wave')
            <div class="text-2xl text-gray-400">
                ~ ~ ~ ~ ~
            </div>
            @break

        @case('diamond')
            <div class="text-lg text-gray-400">
                ◊ ◊ ◊
            </div>
            @break

        @case('text')
            @if($text)
                <div class="relative">
                    <hr class="{{ implode(' ', $dividerClasses) }}"
                        @if($borderStyle) style="{{ $borderStyle }}" @endif>
                    <div class="absolute inset-0 flex justify-{{ $alignment === 'left' ? 'start' : ($alignment === 'right' ? 'end' : 'center') }} items-center">
                        <span class="bg-white px-4 text-sm text-gray-500 font-medium">{{ $text }}</span>
                    </div>
                </div>
            @else
                <hr class="{{ implode(' ', $dividerClasses) }}"
                    @if($borderStyle) style="{{ $borderStyle }}" @endif>
            @endif
            @break

        @case('icon')
            @if($icon)
                <div class="relative">
                    <hr class="{{ implode(' ', $dividerClasses) }}"
                        @if($borderStyle) style="{{ $borderStyle }}" @endif>
                    <div class="absolute inset-0 flex justify-{{ $alignment === 'left' ? 'start' : ($alignment === 'right' ? 'end' : 'center') }} items-center">
                        <span class="bg-white px-3 text-gray-400">
                            <i class="{{ $icon }}"></i>
                        </span>
                    </div>
                </div>
            @else
                <hr class="{{ implode(' ', $dividerClasses) }}"
                    @if($borderStyle) style="{{ $borderStyle }}" @endif>
            @endif
            @break

        @case('gradient')
            <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent {{ $width === 'full' ? 'w-full' : ($width === 'large' ? 'w-64' : ($width === 'medium' ? 'w-32' : 'w-16')) }} {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }}"></div>
            @break

        @case('decorative')
            <div class="flex items-center justify-{{ $alignment === 'left' ? 'start' : ($alignment === 'right' ? 'end' : 'center') }}">
                <svg class="w-32 h-4 text-gray-300" viewBox="0 0 120 16" fill="currentColor">
                    <path d="M0 8h10l5-5 5 5h10l5-5 5 5h10l5-5 5 5h10l5-5 5 5h10l5-5 5 5h10l5-5 5 5h10" stroke="currentColor" stroke-width="1" fill="none"/>
                </svg>
            </div>
            @break

        @case('image')
            @if(isset($content['image_url']) && $content['image_url'])
                <div class="flex justify-{{ $alignment === 'left' ? 'start' : ($alignment === 'right' ? 'end' : 'center') }}">
                    <img src="{{ $content['image_url'] }}"
                         alt="{{ $content['image_alt'] ?? 'Divider' }}"
                         class="h-8 {{ $width === 'small' ? 'w-16' : ($width === 'medium' ? 'w-32' : ($width === 'large' ? 'w-64' : 'w-auto')) }}">
                </div>
            @else
                <hr class="{{ implode(' ', $dividerClasses) }}"
                    @if($borderStyle) style="{{ $borderStyle }}" @endif>
            @endif
            @break

        @default
            <hr class="{{ implode(' ', $dividerClasses) }}"
                @if($borderStyle) style="{{ $borderStyle }}" @endif>
    @endswitch
</div>