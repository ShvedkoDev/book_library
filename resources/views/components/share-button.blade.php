@props(['url', 'title', 'description' => ''])

<div class="share-button-container" {{ $attributes }}>
    <button type="button" class="share-button" onclick="toggleShareMenu(this)">
        <i class="fal fa-share-alt"></i>
        <span>Share</span>
    </button>

    <div class="share-menu" style="display: none;">
        <div class="share-menu-header">
            <span>Share this book</span>
            <button type="button" class="share-menu-close" onclick="closeShareMenu(this)">&times;</button>
        </div>

        <div class="share-options">
            <!-- Copy Link -->
            <button type="button" class="share-option" onclick="copyToClipboard('{{ $url }}', this)" data-share-method="clipboard">
                <i class="fal fa-link"></i>
                <span>Copy Link</span>
            </button>

            <!-- Email -->
            <a href="mailto:?subject={{ urlencode($title) }}&body={{ urlencode($description . ' ' . $url) }}"
               class="share-option"
               onclick="trackShare('email')"
               data-share-method="email">
                <i class="fal fa-envelope"></i>
                <span>Email</span>
            </a>

            <!-- WhatsApp -->
            <a href="https://wa.me/?text={{ urlencode($title . ' - ' . $url) }}"
               target="_blank"
               class="share-option"
               onclick="trackShare('whatsapp')"
               data-share-method="whatsapp">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
            </a>

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
               target="_blank"
               class="share-option"
               onclick="trackShare('facebook')"
               data-share-method="facebook">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
            </a>

            <!-- Twitter / X -->
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}"
               target="_blank"
               class="share-option"
               onclick="trackShare('twitter')"
               data-share-method="twitter">
                <i class="fab fa-twitter"></i>
                <span>Twitter</span>
            </a>
        </div>
    </div>
</div>

<style>
.share-button-container {
    position: relative;
    display: inline-block;
}

.share-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    color: #555;
    transition: all 0.3s;
    font-size: 0.95rem;
}

.share-button:hover {
    background: #e9ecef;
    border-color: #007cba;
    color: #007cba;
}

.share-button i {
    font-size: 1.1rem;
}

.share-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 250px;
    z-index: 1000;
}

.share-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
    font-weight: 600;
    color: #333;
}

.share-menu-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.share-menu-close:hover {
    background: #f0f0f0;
    color: #333;
}

.share-options {
    padding: 0.5rem;
}

.share-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none;
    color: #555;
    font-size: 0.95rem;
}

.share-option:hover {
    background: #f8f9fa;
}

.share-option i {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
    color: #007cba;
}

.share-option.copied {
    background: #d4edda;
    color: #155724;
}

.share-option.copied i {
    color: #155724;
}

@media (max-width: 768px) {
    .share-menu {
        left: auto;
        right: 0;
    }
}
</style>

<script>
function toggleShareMenu(button) {
    const container = button.closest('.share-button-container');
    const menu = container.querySelector('.share-menu');

    // Close all other share menus
    document.querySelectorAll('.share-menu').forEach(m => {
        if (m !== menu) {
            m.style.display = 'none';
        }
    });

    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function closeShareMenu(button) {
    const menu = button.closest('.share-menu');
    menu.style.display = 'none';
}

function copyToClipboard(url, button) {
    navigator.clipboard.writeText(url).then(() => {
        // Visual feedback
        const originalHTML = button.innerHTML;
        button.classList.add('copied');
        button.innerHTML = '<i class="fal fa-check"></i><span>Link Copied!</span>';

        setTimeout(() => {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
        }, 2000);

        trackShare('clipboard');
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy link. Please copy manually: ' + url);
    });
}

function trackShare(method) {
    // Send analytics tracking request
    // Extract book slug from URL (format: /library/book/{slug})
    const pathParts = window.location.pathname.split('/');
    const bookSlug = pathParts[pathParts.length - 1];

    // Try to find book ID from the page (it might be in a data attribute)
    const bookIdElement = document.querySelector('[data-book-id]');
    const bookId = bookIdElement ? bookIdElement.dataset.bookId : null;

    if (!bookId) {
        console.log('Book ID not found, share tracking skipped');
        return;
    }

    fetch(`/api/track-share`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            book_id: bookId,
            share_method: method,
            url: window.location.href
        })
    }).catch(err => {
        console.log('Share tracking failed:', err);
    });
}

// Close share menu when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.share-button-container')) {
        document.querySelectorAll('.share-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Close share menu on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.share-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});
</script>
