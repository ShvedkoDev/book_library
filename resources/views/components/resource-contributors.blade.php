@props(['contributors' => collect()])

@if($contributors->count() > 0)
<section {{ $attributes->merge(['class' => 'contributors-section']) }}>
    <h2 class="contributors-title">Resource Contributors</h2>
    <div class="contributors-grid">
        @foreach($contributors as $contributor)
        <div class="contributor-card">
            @if($contributor->logo)
            <img src="{{ Storage::url($contributor->logo) }}"
                 alt="{{ $contributor->name }}"
                 class="contributor-logo">
            @endif
            <h3 class="contributor-name">{{ $contributor->name }}</h3>
            @if($contributor->organization)
            <p class="contributor-org">{{ $contributor->organization }}</p>
            @endif
            @if($contributor->description)
            <p class="contributor-description">{{ $contributor->description }}</p>
            @endif
            @if($contributor->website_url)
            <a href="{{ $contributor->website_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="contributor-website">
                Visit Website →
            </a>
            @endif
        </div>
        @endforeach
    </div>
</section>

@push('styles')
<style>
    .contributors-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 2px solid #e5e7eb;
    }

    .contributors-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
    }

    .contributors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .contributor-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.2s ease;
    }

    .contributor-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .contributor-logo {
        width: 100%;
        max-width: 150px;
        height: auto;
        margin: 0 auto 1rem auto;
        display: block;
        border-radius: 4px;
    }

    .contributor-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .contributor-org {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .contributor-description {
        font-size: 0.9rem;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .contributor-website {
        display: inline-block;
        color: #0369a1;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .contributor-website:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .contributors-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@endif
