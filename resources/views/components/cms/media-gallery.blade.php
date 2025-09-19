@props([
    'media' => [],
    'layout' => 'grid',
    'columns' => 3,
    'lightbox' => true,
    'showCaptions' => false,
    'gap' => 'gap-4',
    'class' => '',
])

@php
    $mediaCollection = collect($media);
    $galleryId = 'gallery-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'media-gallery ' . $class]) }}>
    @if($layout === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-{{ min($columns, 6) }} {{ $gap }} gallery-grid">
            @foreach($mediaCollection as $index => $mediaItem)
                <div class="gallery-item group relative overflow-hidden rounded-lg">
                    @if($lightbox)
                        <a href="{{ $mediaItem->getUrl() }}"
                           data-lightbox="{{ $galleryId }}"
                           data-title="{{ $mediaItem->getCustomProperty('caption', $mediaItem->name) }}"
                           class="block relative">
                    @endif

                    <x-cms.responsive-image
                        :media="$mediaItem"
                        conversion="medium"
                        class="aspect-square object-cover transition-transform duration-300 group-hover:scale-105"
                        :show-caption="false"
                    />

                    @if($lightbox)
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </div>
                        </a>
                    @endif

                    @if($showCaptions)
                        @php $caption = $mediaItem->getCustomProperty('caption', '') @endphp
                        @if($caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                                <p class="text-white text-sm">{{ $caption }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

    @elseif($layout === 'masonry')
        <div class="columns-1 md:columns-{{ min($columns, 4) }} {{ $gap }} gallery-masonry">
            @foreach($mediaCollection as $index => $mediaItem)
                <div class="gallery-item break-inside-avoid mb-4 group relative overflow-hidden rounded-lg">
                    @if($lightbox)
                        <a href="{{ $mediaItem->getUrl() }}"
                           data-lightbox="{{ $galleryId }}"
                           data-title="{{ $mediaItem->getCustomProperty('caption', $mediaItem->name) }}"
                           class="block relative">
                    @endif

                    <x-cms.responsive-image
                        :media="$mediaItem"
                        conversion="large"
                        class="w-full h-auto transition-transform duration-300 group-hover:scale-105"
                        :show-caption="false"
                    />

                    @if($lightbox)
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </div>
                        </a>
                    @endif

                    @if($showCaptions)
                        @php $caption = $mediaItem->getCustomProperty('caption', '') @endphp
                        @if($caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                                <p class="text-white text-sm">{{ $caption }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

    @elseif($layout === 'carousel')
        <div class="gallery-carousel relative" x-data="{ currentSlide: 0, totalSlides: {{ $mediaCollection->count() }} }">
            <div class="overflow-hidden rounded-lg">
                <div class="flex transition-transform duration-300 ease-in-out"
                     x-bind:style="`transform: translateX(-${currentSlide * 100}%)`">
                    @foreach($mediaCollection as $index => $mediaItem)
                        <div class="w-full flex-shrink-0">
                            @if($lightbox)
                                <a href="{{ $mediaItem->getUrl() }}"
                                   data-lightbox="{{ $galleryId }}"
                                   data-title="{{ $mediaItem->getCustomProperty('caption', $mediaItem->name) }}">
                            @endif

                            <x-cms.responsive-image
                                :media="$mediaItem"
                                conversion="large"
                                class="w-full h-64 md:h-96 object-cover"
                                :show-caption="$showCaptions"
                            />

                            @if($lightbox)
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Navigation Controls --}}
            @if($mediaCollection->count() > 1)
                <button
                    @click="currentSlide = currentSlide > 0 ? currentSlide - 1 : totalSlides - 1"
                    class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <button
                    @click="currentSlide = currentSlide < totalSlides - 1 ? currentSlide + 1 : 0"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Dots Indicator --}}
                <div class="flex justify-center mt-4 space-x-2">
                    @foreach($mediaCollection as $index => $mediaItem)
                        <button
                            @click="currentSlide = {{ $index }}"
                            x-bind:class="currentSlide === {{ $index }} ? 'bg-blue-500' : 'bg-gray-300'"
                            class="w-3 h-3 rounded-full transition-colors duration-200">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

@if($lightbox)
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    @endpush

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script>
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'fitImagesInViewport': true,
            'imageFadeDuration': 300,
            'positionFromTop': 50
        });
    </script>
    @endpush
@endif

@push('styles')
<style>
.gallery-grid .gallery-item {
    position: relative;
    background: #f3f4f6;
}

.gallery-masonry .gallery-item {
    position: relative;
    background: #f3f4f6;
}

.gallery-carousel {
    position: relative;
}

/* Responsive grid adjustments */
@media (max-width: 640px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr !important;
    }
}

/* Loading state */
.gallery-item img[loading="lazy"] {
    background: linear-gradient(90deg, #f0f0f0 25%, transparent 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: loading 1.4s ease infinite;
}

@keyframes loading {
    0% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
</style>
@endpush