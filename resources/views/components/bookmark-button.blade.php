@props(['book', 'isBookmarked' => false])

<div class="bookmark-button-container" {{ $attributes }}>
    <form action="{{ route('library.bookmark', $book) }}" method="POST" class="bookmark-form">
        @csrf
        <button type="submit" class="bookmark-button {{ $isBookmarked ? 'bookmarked' : '' }}">
            <i class="{{ $isBookmarked ? 'fas' : 'fal' }} fa-bookmark"></i>
            <span>{{ $isBookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
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
    padding: 0.5rem 1.25rem;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    width: 100%;
    margin: 0!important;
    font-size: 14px;
    line-height: 1.2;
    background-color: #fdf4d1;
    color: #333;
}

.bookmark-button:hover {
    background: #e6ddb8;
    color: #333;
}

.bookmark-button.bookmarked {
    background: #fdf4d1;
    color: #333;
}

.bookmark-button.bookmarked:hover {
    background: #e6ddb8;
}

.bookmark-button i {
    font-size: 1.1rem;
    transition: all 0.3s;
    background-color: #f0f0f0;
    border-color: #f0f0f0;
}

.bookmark-button.bookmarked i {
    color: #c9d3e0;
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
// AJAX bookmark form submission without page reload
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bookmark-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent page reload

            const button = this.querySelector('.bookmark-button');
            const icon = button.querySelector('i');
            const span = button.querySelector('span');
            const isCurrentlyBookmarked = button.classList.contains('bookmarked');

            // Add loading state
            this.classList.add('loading');

            // Submit via AJAX
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading state
                this.classList.remove('loading');

                // Toggle bookmark state based on response
                if (data.bookmarked) {
                    button.classList.add('bookmarked');
                    icon.classList.remove('fal');
                    icon.classList.add('fas');
                    span.textContent = 'Bookmarked';
                } else {
                    button.classList.remove('bookmarked');
                    icon.classList.remove('fas');
                    icon.classList.add('fal');
                    span.textContent = 'Bookmark';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.classList.remove('loading');
            });
        });
    });
});
</script>
