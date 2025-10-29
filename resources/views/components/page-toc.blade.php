@props(['sections' => []])

@if(count($sections) > 0)
<aside {{ $attributes->merge(['class' => 'toc-sidebar']) }}>
    <h3>Table of Contents</h3>
    <ul class="toc-list">
        @foreach($sections as $section)
        <li>
            <a href="{{ $section['url'] }}" class="toc-link">
                {{ $section['heading'] }}
            </a>
        </li>
        @endforeach
    </ul>
</aside>

@push('styles')
<style>
    .toc-sidebar {
        position: sticky;
        top: 2rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .toc-sidebar h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 1rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .toc-list li {
        margin-bottom: 0.5rem;
    }

    .toc-list a {
        display: block;
        color: #4b5563;
        text-decoration: none;
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .toc-list a:hover {
        background: #f3f4f6;
        color: #1f2937;
        transform: translateX(4px);
    }

    .toc-list a.active {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .toc-sidebar {
            position: static;
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Smooth scroll for TOC links
    document.querySelectorAll('.toc-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Update URL without scrolling
                history.pushState(null, null, '#' + targetId);
            }
        });
    });

    // Highlight active section in TOC based on scroll position
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('.page-body h2[id]');
        const tocLinks = document.querySelectorAll('.toc-link');

        let currentSection = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 100) {
                currentSection = section.getAttribute('id');
            }
        });

        tocLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
            }
        });
    });

    // Handle direct anchor links on page load
    if (window.location.hash) {
        setTimeout(() => {
            const targetId = window.location.hash.substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 100);
    }
</script>
@endpush
@endif
