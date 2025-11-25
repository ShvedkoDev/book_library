<div class="space-y-4">
    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Found {{ count($sections) }} section(s) in this page content:
    </div>

    <div class="space-y-3">
        @foreach ($sections as $section)
            <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 text-sm font-medium">
                        {{ $loop->iteration }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $section['heading'] }}
                    </div>
                    <div class="mt-1 flex items-center gap-2">
                        <code class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 select-all">
                            #{{ $section['anchor'] }}
                        </code>
                        <button
                            type="button"
                            onclick="copyToClipboard('#{{ $section['anchor'] }}', this)"
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                            title="Copy anchor to clipboard"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span class="copy-text">Copy</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const copyText = button.querySelector('.copy-text');
        const originalText = copyText.textContent;

        // Show success feedback
        copyText.textContent = 'Copied!';
        button.classList.add('text-green-600', 'dark:text-green-400');

        // Reset after 2 seconds
        setTimeout(() => {
            copyText.textContent = originalText;
            button.classList.remove('text-green-600', 'dark:text-green-400');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}
</script>
