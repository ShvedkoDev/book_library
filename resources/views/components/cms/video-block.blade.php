@props(['content', 'settings'])

@php
    $url = $content['url'] ?? '';
    $embedCode = $content['embed_code'] ?? '';
    $poster = $content['poster'] ?? '';
    $caption = $content['caption'] ?? '';
    $width = $settings['width'] ?? '100%';
    $height = $settings['height'] ?? '';
    $aspectRatio = $settings['aspect_ratio'] ?? '16:9';
    $autoplay = $settings['autoplay'] ?? false;
    $controls = $settings['controls'] ?? true;
    $muted = $settings['muted'] ?? false;
    $loop = $settings['loop'] ?? false;
    $responsive = $settings['responsive'] ?? true;
    $alignment = $settings['alignment'] ?? 'center';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['video-block', 'mb-6'];

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

    // Build video container classes
    $videoContainerClasses = ['video-container'];

    if ($responsive) {
        $videoContainerClasses[] = 'relative overflow-hidden rounded-lg';

        // Aspect ratio classes
        switch($aspectRatio) {
            case '16:9':
                $videoContainerClasses[] = 'aspect-video';
                break;
            case '4:3':
                $videoContainerClasses[] = 'aspect-[4/3]';
                break;
            case '1:1':
                $videoContainerClasses[] = 'aspect-square';
                break;
            case '21:9':
                $videoContainerClasses[] = 'aspect-[21/9]';
                break;
            default:
                $videoContainerClasses[] = 'aspect-video';
        }
    }

    // Alignment for video container
    if ($alignment === 'center') {
        $videoContainerClasses[] = 'mx-auto';
    } elseif ($alignment === 'right') {
        $videoContainerClasses[] = 'ml-auto';
    }

    // Build inline styles
    $styles = [];
    if (!$responsive && $width) {
        $styles[] = "width: {$width}";
    }
    if (!$responsive && $height) {
        $styles[] = "height: {$height}";
    }

    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';

    // Detect video type
    $videoType = 'unknown';
    $videoId = '';
    $embedUrl = '';

    if ($embedCode) {
        $videoType = 'embed';
    } elseif ($url) {
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
            $videoType = 'youtube';
            $videoId = $matches[1];
            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
            if ($autoplay) $embedUrl .= '?autoplay=1';
            if ($muted) $embedUrl .= ($autoplay ? '&' : '?') . 'mute=1';
            if ($loop) $embedUrl .= (strpos($embedUrl, '?') ? '&' : '?') . 'loop=1&playlist=' . $videoId;
        }
        // Vimeo
        elseif (preg_match('/vimeo\.com\/(?:.*\/)?(\d+)/i', $url, $matches)) {
            $videoType = 'vimeo';
            $videoId = $matches[1];
            $embedUrl = "https://player.vimeo.com/video/{$videoId}";
            $params = [];
            if ($autoplay) $params[] = 'autoplay=1';
            if ($muted) $params[] = 'muted=1';
            if ($loop) $params[] = 'loop=1';
            if (!empty($params)) $embedUrl .= '?' . implode('&', $params);
        }
        // Direct video file
        elseif (preg_match('/\.(mp4|webm|ogg|avi|mov)(\?.*)?$/i', $url)) {
            $videoType = 'direct';
        }
    }
@endphp

@if($url || $embedCode)
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif>

        <div class="{{ implode(' ', $videoContainerClasses) }}"
             @if($styleAttr && !$responsive) {!! $styleAttr !!} @endif>

            @switch($videoType)
                @case('youtube')
                @case('vimeo')
                    <iframe src="{{ $embedUrl }}"
                            class="w-full h-full @if($responsive) absolute inset-0 @endif"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy"
                            title="@if($caption) {{ $caption }} @else Video @endif">
                    </iframe>
                    @break

                @case('direct')
                    <video class="w-full h-full @if($responsive) absolute inset-0 @endif object-cover rounded-lg"
                           @if($controls) controls @endif
                           @if($autoplay) autoplay @endif
                           @if($muted) muted @endif
                           @if($loop) loop @endif
                           @if($poster) poster="{{ $poster }}" @endif
                           preload="metadata">
                        <source src="{{ $url }}" type="video/mp4">
                        <p>Your browser doesn't support HTML5 video. <a href="{{ $url }}">Download the video</a> instead.</p>
                    </video>
                    @break

                @case('embed')
                    <div class="w-full h-full @if($responsive) absolute inset-0 @endif">
                        {!! $embedCode !!}
                    </div>
                    @break

                @default
                    <!-- Fallback for unrecognized URLs -->
                    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                        <i class="fas fa-video text-4xl mb-2"></i>
                        <p>Unsupported video format</p>
                        @if($url)
                            <a href="{{ $url }}" target="_blank" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                View video in new tab
                            </a>
                        @endif
                    </div>
            @endswitch
        </div>

        @if($caption)
            <div class="mt-3 text-sm text-gray-600 @if($alignment === 'center') text-center @elseif($alignment === 'right') text-right @endif">
                {{ $caption }}
            </div>
        @endif
    </div>

    @if($videoType === 'direct')
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add video analytics or custom controls if needed
                const video = document.querySelector('#{{ $elementId ?: "video-" . uniqid() }} video');
                if (video) {
                    video.addEventListener('play', function() {
                        // Track video play event
                        console.log('Video started playing');
                    });

                    video.addEventListener('ended', function() {
                        // Track video completion
                        console.log('Video finished playing');
                    });
                }
            });
        </script>
        @endpush
    @endif

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-video text-4xl mb-2"></i>
        <p>No video URL or embed code provided</p>
    </div>
@endif