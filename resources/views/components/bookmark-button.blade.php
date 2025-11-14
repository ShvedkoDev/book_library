@props(['book', 'isBookmarked' => false])

<div class="bookmark-button-container" {{ $attributes }}>
    <form action="{{ route('library.bookmark', $book) }}" method="POST" class="bookmark-form">
        @csrf
        <button type="submit" class="bookmark-button {{ $isBookmarked ? 'bookmarked' : '' }}">
            <i class="{{ $isBookmarked ? 'fas' : 'fal' }} fa-heart"></i>
            <span>{{ $isBookmarked ? 'Saved' : 'Save to Collection' }}</span>
        </button>
    </form>
</div>

<style>
.bookmark-button-container {
    display: block;
    width: 100%;
}

.bookmark-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    width: 100%;
    margin: 0!important;
    font-size: 14px;
    line-height: 1.2;
    background-color: #fff3cd;
    color: #856404;
}

.bookmark-button:hover {
    background: #ffe69c;
    color: #856404;
}

.bookmark-button.bookmarked {
    background: #fff3cd;
    color: #856404;
}

.bookmark-button.bookmarked:hover {
    background: #ffe69c;
}

.bookmark-button i {
    font-size: 1.1rem;
    transition: all 0.3s;
    color: #ff6b6b;
}

.bookmark-button.bookmarked i {
    color: #ff6b6b;
}

/* Loading state during form submission */
.bookmark-form.loading .bookmark-button {
    opacity: 0.6;
    pointer-events: none;
}

.bookmark-form.loading .bookmark-button i {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@media (max-width: 768px) {
    .bookmark-button span {
        display: none;
    }

    .bookmark-button {
        padding: 0.75rem 1rem;
    }

    .bookmark-button i {
        font-size: 1.3rem;
    }
}
</style>

<script>
// Add loading state to bookmark forms
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bookmark-form').forEach(form => {
        form.addEventListener('submit', function() {
            this.classList.add('loading');
        });
    });
});
</script>
