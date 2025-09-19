@props(['content', 'settings'])

@php
    $url = $content['url'] ?? '';
    $alt = $content['alt'] ?? '';
    $caption = $content['caption'] ?? '';
    $width = $settings['width'] ?? '';
    $height = $settings['height'] ?? '';
    $alignment = $settings['alignment'] ?? 'left';
    $size = $settings['size'] ?? 'medium';
    $style = $settings['style'] ?? 'default';
    $linkUrl = $settings['link_url'] ?? '';
    $linkTarget = $settings['link_target'] ?? '_self';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['image-block', 'mb-6'];

    // Alignment classes
    switch($alignment) {
        case 'center':
            $containerClasses[] = 'text-center';
            break;
        case 'right':
            $containerClasses[] = 'text-right';
            break;
        case 'full':
            $containerClasses[] = 'w-full';
            break;
        default:
            $containerClasses[] = 'text-left';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build image classes
    $imageClasses = ['block'];

    // Size classes
    switch($size) {
        case 'small':
            $imageClasses[] = 'max-w-xs';
            break;
        case 'medium':
            $imageClasses[] = 'max-w-md';
            break;
        case 'large':
            $imageClasses[] = 'max-w-lg';
            break;
        case 'xl':
            $imageClasses[] = 'max-w-xl';
            break;
        case 'full':
            $imageClasses[] = 'w-full';
            break;
        case 'auto':
            $imageClasses[] = 'max-w-full h-auto';
            break;
    }

    // Style classes
    switch($style) {
        case 'rounded':
            $imageClasses[] = 'rounded-lg';
            break;
        case 'circle':
            $imageClasses[] = 'rounded-full';
            break;
        case 'shadow':
            $imageClasses[] = 'shadow-lg rounded-lg';
            break;
        case 'border':
            $imageClasses[] = 'border border-gray-300 rounded-lg';
            break;
        case 'polaroid':
            $imageClasses[] = 'bg-white p-2 shadow-lg transform rotate-1 hover:rotate-0 transition-transform';
            break;
    }

    // Alignment for images
    if ($alignment === 'center') {
        $imageClasses[] = 'mx-auto';
    } elseif ($alignment === 'right') {
        $imageClasses[] = 'ml-auto';
    }

    // Build inline styles
    $styles = [];
    if ($width) {
        $styles[] = "width: {$width}";
    }
    if ($height) {
        $styles[] = "height: {$height}";
    }

    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';
@endphp

@if($url)
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif>

        @if($linkUrl)
            <a href="{{ $linkUrl }}" target="{{ $linkTarget }}" class="inline-block">
        @endif

        <figure class="@if($style === 'polaroid') inline-block @endif">
            <img src="{{ $url }}"
                 alt="{{ $alt }}"
                 class="{{ implode(' ', $imageClasses) }}"
                 @if($styleAttr) {!! $styleAttr !!} @endif
                 loading="lazy"
                 decoding="async">

            @if($caption)
                <figcaption class="mt-2 text-sm text-gray-600 @if($alignment === 'center') text-center @elseif($alignment === 'right') text-right @endif">
                    {{ $caption }}
                </figcaption>
            @endif
        </figure>

        @if($linkUrl)
            </a>
        @endif
    </div>
@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-image text-4xl mb-2"></i>
        <p>No image specified</p>
    </div>
@endif