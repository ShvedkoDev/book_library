@props(['content', 'settings'])

@php
    $code = $content['code'] ?? '';
    $language = $content['language'] ?? 'text';
    $filename = $content['filename'] ?? '';
    $showLineNumbers = $settings['show_line_numbers'] ?? false;
    $theme = $settings['theme'] ?? 'default';
    $copyButton = $settings['copy_button'] ?? true;
    $wrapLines = $settings['wrap_lines'] ?? true;
    $maxHeight = $settings['max_height'] ?? '';
    $cssClass = $settings['css_class'] ?? '';
    $elementId = $settings['id'] ?? '';

    // Build container classes
    $containerClasses = ['code-block', 'mb-6', 'rounded-lg', 'overflow-hidden'];

    // Theme classes
    switch($theme) {
        case 'dark':
            $containerClasses[] = 'bg-gray-900';
            $headerClass = 'bg-gray-800 text-gray-200';
            $codeClass = 'bg-gray-900 text-green-400';
            break;
        case 'light':
            $containerClasses[] = 'bg-gray-50 border border-gray-200';
            $headerClass = 'bg-gray-100 text-gray-700 border-b border-gray-200';
            $codeClass = 'bg-gray-50 text-gray-800';
            break;
        case 'terminal':
            $containerClasses[] = 'bg-black';
            $headerClass = 'bg-gray-800 text-green-400';
            $codeClass = 'bg-black text-green-400 font-mono';
            break;
        default:
            $containerClasses[] = 'bg-white border border-gray-200';
            $headerClass = 'bg-gray-50 text-gray-700 border-b border-gray-200';
            $codeClass = 'bg-white text-gray-800';
    }

    if ($cssClass) {
        $containerClasses[] = $cssClass;
    }

    // Build code wrapper classes
    $codeWrapperClasses = ['relative'];
    if ($maxHeight) {
        $codeWrapperClasses[] = 'overflow-y-auto';
    }

    // Build pre classes
    $preClasses = ['p-4', 'overflow-x-auto', $codeClass];
    if (!$wrapLines) {
        $preClasses[] = 'whitespace-pre';
    } else {
        $preClasses[] = 'whitespace-pre-wrap break-words';
    }

    // Generate unique ID for copy functionality
    $blockId = $elementId ?: 'code-block-' . uniqid();

    // Language-specific highlighting classes
    $languageClass = '';
    if ($language && $language !== 'text') {
        $languageClass = "language-{$language}";
    }

    // Split code into lines for line numbers
    $codeLines = explode("\n", $code);
    $lineCount = count($codeLines);
@endphp

@if($code)
    <div class="{{ implode(' ', $containerClasses) }}"
         @if($elementId) id="{{ $elementId }}" @endif>

        @if($filename || $copyButton || $language !== 'text')
            <div class="{{ $headerClass }} px-4 py-2 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    @if($filename)
                        <span class="text-sm font-medium">
                            <i class="fas fa-file-code mr-2"></i>{{ $filename }}
                        </span>
                    @endif
                    @if($language && $language !== 'text')
                        <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded uppercase font-mono">
                            {{ $language }}
                        </span>
                    @endif
                </div>

                @if($copyButton)
                    <button type="button"
                            class="copy-code-btn text-xs hover:bg-gray-200 px-2 py-1 rounded transition-colors"
                            data-clipboard-target="#{{ $blockId }}-code"
                            title="Copy to clipboard">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                @endif
            </div>
        @endif

        <div class="{{ implode(' ', $codeWrapperClasses) }}"
             @if($maxHeight) style="max-height: {{ $maxHeight }}" @endif>

            @if($showLineNumbers)
                <div class="flex">
                    <!-- Line numbers -->
                    <div class="{{ $codeClass }} py-4 px-2 border-r border-gray-300 text-right text-sm leading-6 select-none">
                        @for($i = 1; $i <= $lineCount; $i++)
                            <div class="text-gray-500">{{ $i }}</div>
                        @endfor
                    </div>

                    <!-- Code content -->
                    <div class="flex-1">
                        <pre class="{{ implode(' ', $preClasses) }} pl-4 {{ $languageClass }}"><code id="{{ $blockId }}-code" class="block {{ $languageClass }}">{{ $code }}</code></pre>
                    </div>
                </div>
            @else
                <pre class="{{ implode(' ', $preClasses) }} {{ $languageClass }}"><code id="{{ $blockId }}-code" class="block {{ $languageClass }}">{{ $code }}</code></pre>
            @endif
        </div>
    </div>

    @if($copyButton)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Simple clipboard functionality
                const copyBtn = document.querySelector('[data-clipboard-target="#{{ $blockId }}-code"]');
                if (copyBtn) {
                    copyBtn.addEventListener('click', function() {
                        const codeElement = document.getElementById('{{ $blockId }}-code');
                        const textArea = document.createElement('textarea');
                        textArea.value = codeElement.textContent;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);

                        // Visual feedback
                        const originalHTML = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
                        copyBtn.classList.add('bg-green-100', 'text-green-700');

                        setTimeout(() => {
                            copyBtn.innerHTML = originalHTML;
                            copyBtn.classList.remove('bg-green-100', 'text-green-700');
                        }, 2000);
                    });
                }
            });
        </script>
        @endpush
    @endif

    @push('styles')
    <style>
        /* Code syntax highlighting - basic styles */
        .language-javascript .token.keyword,
        .language-js .token.keyword,
        .language-php .token.keyword {
            color: #007acc;
            font-weight: bold;
        }

        .language-javascript .token.string,
        .language-js .token.string,
        .language-php .token.string {
            color: #d14;
        }

        .language-javascript .token.comment,
        .language-js .token.comment,
        .language-php .token.comment {
            color: #6a737d;
            font-style: italic;
        }

        .language-javascript .token.function,
        .language-js .token.function,
        .language-php .token.function {
            color: #6f42c1;
        }

        .language-html .token.tag {
            color: #22863a;
        }

        .language-html .token.attr-name {
            color: #6f42c1;
        }

        .language-html .token.attr-value {
            color: #032f62;
        }

        .language-css .token.property {
            color: #d73a49;
        }

        .language-css .token.selector {
            color: #6f42c1;
        }

        .language-bash .token.function {
            color: #005cc5;
        }

        .language-json .token.property {
            color: #d73a49;
        }

        .language-json .token.string {
            color: #032f62;
        }

        /* Terminal theme specifics */
        .code-block .bg-black code {
            font-family: 'Courier New', 'Consolas', monospace;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .code-block pre {
                font-size: 14px;
            }

            .code-block .px-4 {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* Custom scrollbar for code blocks */
        .code-block pre::-webkit-scrollbar {
            height: 8px;
        }

        .code-block pre::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .code-block pre::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .code-block pre::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
    @endpush

@else
    <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
        <i class="fas fa-code text-4xl mb-2"></i>
        <p>No code provided</p>
    </div>
@endif