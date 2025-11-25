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
                <svg class="share-icon" style="color: #25D366;" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span>WhatsApp</span>
            </a>

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
               target="_blank"
               class="share-option"
               onclick="trackShare('facebook')"
               data-share-method="facebook">
                <svg class="share-icon" style="color: #1877F2;" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Facebook</span>
            </a>

            <!-- Twitter / X -->
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}"
               target="_blank"
               class="share-option"
               onclick="trackShare('twitter')"
               data-share-method="twitter">
                <svg class="share-icon" style="color: #000000;" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                <span>X (Twitter)</span>
            </a>
        </div>
    </div>
</div>

<style>
.share-button-container {
    position: relative;
    display: block;
    width: 100%;
}

.share-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    color: #555;
    transition: all 0.3s;
    width: 100%;
    margin: 0!important;
    font-size: 13px;
    line-height: 2;
    background-color: #f0f0f0;
    color: #333;
}

.share-button:hover {
    background: #e9ecef;
    border-color: #1d496a;
    color: #1d496a;
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
    color: #1d496a;
    display: inline-block;
}

.share-option .share-icon {
    width: 20px;
    height: 20px;
    min-width: 20px;
    display: inline-block;
    vertical-align: middle;
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
