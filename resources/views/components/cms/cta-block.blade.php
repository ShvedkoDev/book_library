@props(['content', 'settings'])

@php
    $title = $content['title'] ?? '';
    $text = $content['text'] ?? '';
    $buttonText = $content['button_text'] ?? 'Learn More';
    $buttonUrl = $content['button_url'] ?? '#';
    $buttonTarget = $content['button_target'] ?? '_self';
    $style = $settings['style'] ?? 'primary';
    $alignment = $settings['alignment'] ?? 'center';
    $size = $settings['size'] ?? 'medium';
    $backgroundColor = $settings['background_color'] ?? '';
    $textColor = $settings['text_color'] ?? '';
    $fullWidth = $settings['full_width'] ?? false;
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['cta-block', 'my-8', 'p-6', 'rounded-lg'];

    // Style classes
    switch($style) {
        case 'primary':
            $containerClasses[] = 'bg-blue-600 text-white';
            $buttonClass = 'bg-white text-blue-600 hover:bg-gray-100';
            break;
        case 'secondary':
            $containerClasses[] = 'bg-gray-100 text-gray-900 border border-gray-300';
            $buttonClass = 'bg-blue-600 text-white hover:bg-blue-700';
            break;
        case 'success':
            $containerClasses[] = 'bg-green-600 text-white';
            $buttonClass = 'bg-white text-green-600 hover:bg-gray-100';
            break;
        case 'warning':
            $containerClasses[] = 'bg-orange-500 text-white';
            $buttonClass = 'bg-white text-orange-500 hover:bg-gray-100';
            break;
        case 'danger':
            $containerClasses[] = 'bg-red-600 text-white';
            $buttonClass = 'bg-white text-red-600 hover:bg-gray-100';
            break;
        case 'dark':
            $containerClasses[] = 'bg-gray-900 text-white';
            $buttonClass = 'bg-white text-gray-900 hover:bg-gray-100';
            break;
        case 'light':
            $containerClasses[] = 'bg-white text-gray-900 border border-gray-200';
            $buttonClass = 'bg-blue-600 text-white hover:bg-blue-700';
            break;
        case 'gradient':
            $containerClasses[] = 'bg-gradient-to-r from-blue-500 to-purple-600 text-white';
            $buttonClass = 'bg-white text-blue-600 hover:bg-gray-100';
            break;
        default:
            $containerClasses[] = 'bg-blue-600 text-white';
            $buttonClass = 'bg-white text-blue-600 hover:bg-gray-100';
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

    // Size classes
    switch($size) {
        case 'small':
            $containerClasses[] = 'p-4';
            $titleClass = 'text-lg font-semibold mb-2';
            $textClass = 'text-sm mb-4';
            $buttonClass .= ' px-4 py-2 text-sm';
            break;
        case 'large':
            $containerClasses[] = 'p-8';
            $titleClass = 'text-3xl font-bold mb-4';
            $textClass = 'text-lg mb-6';
            $buttonClass .= ' px-8 py-4 text-lg';
            break;
        default:
            $titleClass = 'text-2xl font-bold mb-3';
            $textClass = 'text-base mb-5';
            $buttonClass .= ' px-6 py-3 text-base';
    }

    if ($fullWidth) {
        $containerClasses[] = 'w-full';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build inline styles
    $styles = [];
    if ($backgroundColor) {
        $styles[] = "background-color: {$backgroundColor}";
        // Remove default background classes if custom color is set
        $containerClasses = array_filter($containerClasses, function($class) {
            return !str_contains($class, 'bg-');
        });
    }
    if ($textColor) {
        $styles[] = "color: {$textColor}";
        // Remove default text color classes if custom color is set
        $containerClasses = array_filter($containerClasses, function($class) {
            return !str_contains($class, 'text-');
        });
    }

    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';

    // Additional button classes
    $buttonClass .= ' inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500';
@endphp

<div class="{{ implode(' ', $containerClasses) }}"
     @if($elementId) id="{{ $elementId }}" @endif
     @if($styleAttr) {!! $styleAttr !!} @endif>

    @if($title)
        <h3 class="{{ $titleClass }}">{{ $title }}</h3>
    @endif

    @if($text)
        <div class="{{ $textClass }}">
            @if(strip_tags($text) !== $text)
                {!! $text !!}
            @else
                {!! nl2br(e($text)) !!}
            @endif
        </div>
    @endif

    @if($buttonText && $buttonUrl)
        <a href="{{ $buttonUrl }}"
           target="{{ $buttonTarget }}"
           class="{{ $buttonClass }}"
           @if($buttonTarget === '_blank') rel="noopener noreferrer" @endif>
            {{ $buttonText }}
            @if($buttonTarget === '_blank')
                <i class="fas fa-external-link-alt ml-2 text-xs"></i>
            @endif
        </a>
    @endif
</div>