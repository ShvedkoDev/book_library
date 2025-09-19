@props(['content', 'settings'])

@php
    $text = $content['text'] ?? '';
    $style = $settings['style'] ?? 'paragraph';
    $alignment = $settings['alignment'] ?? 'left';
    $size = $settings['size'] ?? 'normal';
    $color = $settings['color'] ?? '';
    $backgroundColor = $settings['background_color'] ?? '';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build CSS classes
    $classes = ['text-block'];

    // Alignment classes
    switch($alignment) {
        case 'center':
            $classes[] = 'text-center';
            break;
        case 'right':
            $classes[] = 'text-right';
            break;
        case 'justify':
            $classes[] = 'text-justify';
            break;
        default:
            $classes[] = 'text-left';
    }

    // Size classes
    switch($size) {
        case 'small':
            $classes[] = 'text-sm';
            break;
        case 'large':
            $classes[] = 'text-lg';
            break;
        case 'xl':
            $classes[] = 'text-xl';
            break;
        case '2xl':
            $classes[] = 'text-2xl';
            break;
        default:
            $classes[] = 'text-base';
    }

    if ($cssClass) {
        $classes[] = $cssClass;
    }

    // Build inline styles
    $styles = [];
    if ($color) {
        $styles[] = "color: {$color}";
    }
    if ($backgroundColor) {
        $styles[] = "background-color: {$backgroundColor}";
        $classes[] = 'p-4 rounded-lg';
    }

    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';
@endphp

<div class="{{ implode(' ', $classes) }}"
     @if($elementId) id="{{ $elementId }}" @endif
     @if($styleAttr) {!! $styleAttr !!} @endif>

    @switch($style)
        @case('heading1')
            <h1 class="text-3xl font-bold mb-4">{!! $text !!}</h1>
            @break

        @case('heading2')
            <h2 class="text-2xl font-bold mb-3">{!! $text !!}</h2>
            @break

        @case('heading3')
            <h3 class="text-xl font-bold mb-2">{!! $text !!}</h3>
            @break

        @case('heading4')
            <h4 class="text-lg font-semibold mb-2">{!! $text !!}</h4>
            @break

        @case('heading5')
            <h5 class="text-base font-semibold mb-2">{!! $text !!}</h5>
            @break

        @case('heading6')
            <h6 class="text-sm font-semibold mb-2">{!! $text !!}</h6>
            @break

        @case('blockquote')
            <blockquote class="border-l-4 border-blue-500 pl-4 py-2 my-4 bg-blue-50 italic">
                {!! $text !!}
            </blockquote>
            @break

        @case('lead')
            <p class="text-xl leading-relaxed mb-4 font-medium text-gray-700">{!! $text !!}</p>
            @break

        @case('caption')
            <p class="text-sm text-gray-600 italic">{!! $text !!}</p>
            @break

        @case('highlight')
            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 my-4">
                <p class="text-yellow-800">{!! $text !!}</p>
            </div>
            @break

        @case('warning')
            <div class="bg-orange-100 border-l-4 border-orange-500 p-4 my-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-orange-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-orange-700">{!! $text !!}</p>
                    </div>
                </div>
            </div>
            @break

        @case('info')
            <div class="bg-blue-100 border-l-4 border-blue-500 p-4 my-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-blue-700">{!! $text !!}</p>
                    </div>
                </div>
            </div>
            @break

        @case('success')
            <div class="bg-green-100 border-l-4 border-green-500 p-4 my-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-700">{!! $text !!}</p>
                    </div>
                </div>
            </div>
            @break

        @case('error')
            <div class="bg-red-100 border-l-4 border-red-500 p-4 my-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-red-700">{!! $text !!}</p>
                    </div>
                </div>
            </div>
            @break

        @case('wysiwyg')
            <div class="prose prose-lg max-w-none">
                {!! $text !!}
            </div>
            @break

        @default
            @if(strip_tags($text) !== $text)
                <!-- HTML content -->
                <div class="prose prose-lg max-w-none">
                    {!! $text !!}
                </div>
            @else
                <!-- Plain text -->
                <p class="mb-4 leading-relaxed">{!! nl2br(e($text)) !!}</p>
            @endif
    @endswitch
</div>