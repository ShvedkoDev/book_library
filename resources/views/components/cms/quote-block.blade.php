@props(['content', 'settings'])

@php
    $quote = $content['quote'] ?? '';
    $author = $content['author'] ?? '';
    $source = $content['source'] ?? '';
    $cite = $content['cite'] ?? '';
    $style = $settings['style'] ?? 'default';
    $size = $settings['size'] ?? 'medium';
    $alignment = $settings['alignment'] ?? 'center';
    $color = $settings['color'] ?? '';
    $backgroundColor = $settings['background_color'] ?? '';
    $showQuoteMarks = $settings['show_quote_marks'] ?? true;
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['quote-block', 'my-8'];

    // Style classes
    switch($style) {
        case 'bordered':
            $containerClasses[] = 'border-l-4 border-blue-500 pl-6 py-4';
            break;
        case 'card':
            $containerClasses[] = 'bg-white border border-gray-200 rounded-lg p-6 shadow-sm';
            break;
        case 'highlight':
            $containerClasses[] = 'bg-blue-50 border border-blue-200 rounded-lg p-6';
            break;
        case 'minimal':
            $containerClasses[] = 'border-t border-b border-gray-200 py-6';
            break;
        case 'modern':
            $containerClasses[] = 'relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8';
            break;
        case 'speech-bubble':
            $containerClasses[] = 'relative bg-white border border-gray-200 rounded-lg p-6 speech-bubble';
            break;
        default:
            $containerClasses[] = 'relative';
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
            $quoteClass = 'text-base italic leading-relaxed';
            $authorClass = 'text-sm font-medium text-gray-600 mt-3';
            $sourceClass = 'text-xs text-gray-500';
            break;
        case 'large':
            $quoteClass = 'text-2xl italic leading-relaxed';
            $authorClass = 'text-lg font-medium text-gray-600 mt-4';
            $sourceClass = 'text-base text-gray-500';
            break;
        case 'xl':
            $quoteClass = 'text-3xl italic leading-relaxed';
            $authorClass = 'text-xl font-medium text-gray-600 mt-6';
            $sourceClass = 'text-lg text-gray-500';
            break;
        default:
            $quoteClass = 'text-lg italic leading-relaxed';
            $authorClass = 'text-base font-medium text-gray-600 mt-4';
            $sourceClass = 'text-sm text-gray-500';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build inline styles
    $styles = [];
    if ($color) {
        $styles[] = "color: {$color}";
    }
    if ($backgroundColor) {
        $styles[] = "background-color: {$backgroundColor}";
        if (!in_array($style, ['card', 'highlight', 'modern'])) {
            $containerClasses[] = 'p-6 rounded-lg';
        }
    }

    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';
@endphp

@if($quote)
    <blockquote class="{{ implode(' ', $containerClasses) }}"
                @if($elementId) id="{{ $elementId }}" @endif
                @if($styleAttr) {!! $styleAttr !!} @endif
                @if($cite) cite="{{ $cite }}" @endif>

        @if($style === 'modern' && $showQuoteMarks)
            <div class="absolute top-4 left-4 text-6xl text-blue-200 leading-none">
                <i class="fas fa-quote-left"></i>
            </div>
        @endif

        @if($style === 'speech-bubble')
            <div class="speech-bubble-arrow"></div>
        @endif

        <div class="relative @if($style === 'modern') ml-12 @endif">
            @if($showQuoteMarks && !in_array($style, ['modern']))
                <span class="text-4xl text-gray-300 leading-none">
                    <i class="fas fa-quote-left"></i>
                </span>
            @endif

            <p class="{{ $quoteClass }} @if($showQuoteMarks && !in_array($style, ['modern'])) mt-2 @endif">
                @if(strip_tags($quote) !== $quote)
                    {!! $quote !!}
                @else
                    {{ $quote }}
                @endif
            </p>

            @if($showQuoteMarks && !in_array($style, ['modern']))
                <span class="text-4xl text-gray-300 leading-none float-right">
                    <i class="fas fa-quote-right"></i>
                </span>
                <div class="clear-both"></div>
            @endif
        </div>

        @if($author || $source)
            <footer class="{{ $authorClass }}">
                @if($author)
                    <cite class="not-italic">— {{ $author }}</cite>
                @endif
                @if($source)
                    @if($author), @endif
                    <span class="{{ $sourceClass }}">{{ $source }}</span>
                @endif
            </footer>
        @endif
    </blockquote>

    @if($style === 'speech-bubble')
        @push('styles')
        <style>
            .speech-bubble {
                position: relative;
            }

            .speech-bubble-arrow::before {
                content: '';
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;
                border-top: 10px solid white;
            }

            .speech-bubble-arrow::after {
                content: '';
                position: absolute;
                bottom: -11px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 11px solid transparent;
                border-right: 11px solid transparent;
                border-top: 11px solid #d1d5db;
            }

            @media (max-width: 768px) {
                .speech-bubble-arrow::before,
                .speech-bubble-arrow::after {
                    left: 30px;
                    transform: none;
                }
            }
        </style>
        @endpush
    @endif

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-quote-left text-4xl mb-2"></i>
        <p>No quote text provided</p>
    </div>
@endif