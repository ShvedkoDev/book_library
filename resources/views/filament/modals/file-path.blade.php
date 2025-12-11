<div class="space-y-4">
    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Relative Path (from storage/app/):</label>
        <div class="mt-1 flex items-center space-x-2">
            <code class="flex-1 p-2 bg-gray-100 dark:bg-gray-800 rounded text-sm font-mono">{{ $relativePath }}</code>
            <button
                type="button"
                onclick="navigator.clipboard.writeText('{{ $relativePath }}').then(() => {
                    const btn = this;
                    const originalText = btn.textContent;
                    btn.textContent = 'Copied!';
                    btn.classList.add('text-green-600');
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.classList.remove('text-green-600');
                    }, 2000);
                })"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
            >
                Copy
            </button>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Absolute Path (server):</label>
        <div class="mt-1 flex items-center space-x-2">
            <code class="flex-1 p-2 bg-gray-100 dark:bg-gray-800 rounded text-sm font-mono break-all">{{ $absolutePath }}</code>
            <button
                type="button"
                onclick="navigator.clipboard.writeText('{{ $absolutePath }}').then(() => {
                    const btn = this;
                    const originalText = btn.textContent;
                    btn.textContent = 'Copied!';
                    btn.classList.add('text-green-600');
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.classList.remove('text-green-600');
                    }, 2000);
                })"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
            >
                Copy
            </button>
        </div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <strong>Usage in Laravel:</strong><br>
            <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">Storage::download('{{ $relativePath }}')</code>
        </p>
    </div>
</div>
