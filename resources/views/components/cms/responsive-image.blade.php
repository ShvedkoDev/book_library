@props([
    'media',
    'conversion' => '',
    'alt' => '',
    'class' => '',
    'lazy' => true,
    'sizes' => '100vw',
    'showCaption' => false,
])

@php
    if (!$media) {
        return;
    }

    $mediaService = app('cms.media');
    $sources = $mediaService->getResponsiveImageSources($media);
    $altText = $alt ?: $media->getCustomProperty('alt', '');
    $title = $media->getCustomProperty('title', $media->name);
    $caption = $media->getCustomProperty('caption', '');
@endphp

<figure {{ $attributes->merge(['class' => 'media-figure ' . $class]) }}>
    <picture class="media-picture">
        {{-- WebP sources for modern browsers --}}
        @foreach($sources as $name => $source)
            @if(isset($source['format']) && $source['format'] === 'webp')
                <source
                    srcset="{{ $source['url'] }}"
                    type="image/webp"
                    @if(isset($source['width']))
                        media="(max-width: {{ $source['width'] }}px)"
                    @endif
                >
            @endif
        @endforeach

        {{-- Regular sources for fallback --}}
        @foreach($sources as $name => $source)
            @if(!isset($source['format']) || $source['format'] !== 'webp')
                <source
                    srcset="{{ $source['url'] }}"
                    @if(isset($source['width']))
                        media="(max-width: {{ $source['width'] }}px)"
                    @endif
                >
            @endif
        @endforeach

        {{-- Fallback img tag --}}
        <img
            src="{{ $conversion ? $media->getUrl($conversion) : $media->getUrl() }}"
            alt="{{ $altText }}"
            title="{{ $title }}"
            @if($lazy)
                loading="lazy"
            @endif
            sizes="{{ $sizes }}"
            class="media-image w-full h-auto"
            @if($media->getCustomProperty('width'))
                width="{{ $media->getCustomProperty('width') }}"
            @endif
            @if($media->getCustomProperty('height'))
                height="{{ $media->getCustomProperty('height') }}"
            @endif
        />
    </picture>

    @if($showCaption && $caption)
        <figcaption class="media-caption mt-2 text-sm text-gray-600">
            {{ $caption }}
        </figcaption>
    @endif
</figure>

@push('styles')
<style>
.media-figure {
    margin: 0;
}

.media-picture {
    display: block;
    position: relative;
    overflow: hidden;
}

.media-image {
    transition: transform 0.3s ease;
}

.media-image:hover {
    transform: scale(1.02);
}

.media-caption {
    font-style: italic;
    line-height: 1.4;
}

/* Responsive image utilities */
.media-figure.rounded .media-image {
    border-radius: 0.5rem;
}

.media-figure.shadow .media-image {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.media-figure.bordered .media-image {
    border: 2px solid #e5e7eb;
}

/* Loading placeholder */
.media-image[loading="lazy"] {
    background: linear-gradient(90deg, #f0f0f0 25%, transparent 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: loading 1.4s ease infinite;
}

@keyframes loading {
    0% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}
</style>
@endpush