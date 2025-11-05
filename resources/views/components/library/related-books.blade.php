@props(['books', 'title', 'sectionId'])

@if($books->isNotEmpty())
    <div class="related-books" id="{{ $sectionId }}">
        <h3 class="section-title text-left">{{ $title }}</h3>
        <div class="books-scroll-container">
            <button class="scroll-arrow scroll-arrow-left" id="scroll-left-{{ $sectionId }}" onclick="scrollBooks('{{ $sectionId }}', 'left')" style="display: none;" disabled>
                <i class="fal fa-chevron-left"></i>
            </button>
            <div class="books-grid-scroll" id="books-grid-{{ $sectionId }}">
                @foreach($books as $relatedBook)
                    <a href="{{ route('library.show', $relatedBook->slug) }}" class="book-card">
                        <img src="{{ $relatedBook->getThumbnailUrl() }}" alt="{{ $relatedBook->title }}" class="book-card-cover">
                        <div class="book-card-title">{{ $relatedBook->title }}</div>
                        <div class="book-card-author">
                            @if($relatedBook->creators->count() > 1)
                                {{ $relatedBook->creators->first()->name }} et al.
                            @elseif($relatedBook->creators->count() === 1)
                                {{ $relatedBook->creators->first()->name }}
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <button class="scroll-arrow scroll-arrow-right" id="scroll-right-{{ $sectionId }}" onclick="scrollBooks('{{ $sectionId }}', 'right')" style="display: none;">
                <i class="fal fa-chevron-right"></i>
            </button>
        </div>
    </div>
@endif

<style>
.books-scroll-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
}

.books-grid-scroll {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
    padding: 0.5rem 0;
    flex: 1;
}

.books-grid-scroll::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.scroll-arrow {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #007cba;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}

.scroll-arrow:hover:not(:disabled) {
    background: #005a8a;
    transform: scale(1.1);
}

.scroll-arrow:active:not(:disabled) {
    transform: scale(0.95);
}

.scroll-arrow:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.5;
    box-shadow: none;
}

.scroll-arrow i {
    font-size: 1rem;
}

@media (max-width: 768px) {
    .scroll-arrow {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }

    .scroll-arrow i {
        font-size: 0.875rem;
    }
}
</style>

<script>
function scrollBooks(sectionId, direction) {
    const grid = document.getElementById('books-grid-' + sectionId);
    if (!grid) return;

    const scrollAmount = 300; // Pixels to scroll

    if (direction === 'left') {
        grid.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else {
        grid.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }

    // Update arrow visibility after scroll
    setTimeout(() => updateArrowVisibility(sectionId), 100);
}

function updateArrowVisibility(sectionId) {
    const grid = document.getElementById('books-grid-' + sectionId);
    const leftArrow = document.getElementById('scroll-left-' + sectionId);
    const rightArrow = document.getElementById('scroll-right-' + sectionId);

    if (!grid || !leftArrow || !rightArrow) return;

    // Check if content is scrollable
    const hasOverflow = grid.scrollWidth > grid.clientWidth;

    if (!hasOverflow) {
        // No overflow - hide both arrows
        leftArrow.style.display = 'none';
        rightArrow.style.display = 'none';
        return;
    }

    // Has overflow - show both arrows and enable/disable based on scroll position
    leftArrow.style.display = 'flex';
    rightArrow.style.display = 'flex';

    const scrollLeft = grid.scrollLeft;
    const maxScroll = grid.scrollWidth - grid.clientWidth;

    // Disable/enable left arrow
    if (scrollLeft <= 0) {
        leftArrow.disabled = true;
    } else {
        leftArrow.disabled = false;
    }

    // Disable/enable right arrow
    if (scrollLeft >= maxScroll - 1) {
        rightArrow.disabled = true;
    } else {
        rightArrow.disabled = false;
    }
}

// Initialize arrow visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update for all sections
    const sections = ['related-by-collection', 'related-by-language', 'related-by-creator'];
    sections.forEach(sectionId => {
        updateArrowVisibility(sectionId);

        // Add scroll event listener to update arrows while scrolling
        const grid = document.getElementById('books-grid-' + sectionId);
        if (grid) {
            grid.addEventListener('scroll', () => updateArrowVisibility(sectionId));
        }
    });

    // Update on window resize
    window.addEventListener('resize', () => {
        sections.forEach(sectionId => updateArrowVisibility(sectionId));
    });
});
</script>
