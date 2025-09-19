<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class CodeBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Code Block';
    }

    public function getDescription(): string
    {
        return 'Syntax highlighted code with language selection';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-code-bracket';
    }

    public function getCategory(): string
    {
        return 'Content';
    }

    protected function getType(): string
    {
        return 'code';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Code Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Code Title')
                        ->maxLength(255)
                        ->helperText('Optional title for the code block'),

                    Forms\Components\Select::make('language')
                        ->label('Programming Language')
                        ->options([
                            'php' => 'PHP',
                            'javascript' => 'JavaScript',
                            'typescript' => 'TypeScript',
                            'html' => 'HTML',
                            'css' => 'CSS',
                            'python' => 'Python',
                            'java' => 'Java',
                            'cpp' => 'C++',
                            'csharp' => 'C#',
                            'ruby' => 'Ruby',
                            'go' => 'Go',
                            'rust' => 'Rust',
                            'sql' => 'SQL',
                            'json' => 'JSON',
                            'xml' => 'XML',
                            'yaml' => 'YAML',
                            'markdown' => 'Markdown',
                            'bash' => 'Bash/Shell',
                            'dockerfile' => 'Dockerfile',
                            'nginx' => 'Nginx Config',
                            'apache' => 'Apache Config',
                            'plaintext' => 'Plain Text',
                        ])
                        ->default('php')
                        ->searchable(),

                    Forms\Components\Textarea::make('code')
                        ->label('Code')
                        ->required()
                        ->rows(15)
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'font-mono']),

                    Forms\Components\TextInput::make('filename')
                        ->label('Filename')
                        ->maxLength(255)
                        ->helperText('Optional filename to display'),
                ]),

            Forms\Components\Section::make('Code Display Settings')
                ->schema([
                    Forms\Components\Toggle::make('show_line_numbers')
                        ->label('Show Line Numbers')
                        ->default(true),

                    Forms\Components\Toggle::make('highlight_lines')
                        ->label('Enable Line Highlighting')
                        ->default(false)
                        ->live(),

                    Forms\Components\TextInput::make('highlighted_lines')
                        ->label('Highlighted Lines')
                        ->helperText('Comma-separated line numbers (e.g., 1,3-5,10)')
                        ->visible(fn ($get) => $get('highlight_lines')),

                    Forms\Components\Toggle::make('copy_button')
                        ->label('Show Copy Button')
                        ->default(true),

                    Forms\Components\Toggle::make('wrap_lines')
                        ->label('Wrap Long Lines')
                        ->default(false),

                    Forms\Components\Select::make('theme')
                        ->label('Color Theme')
                        ->options([
                            'default' => 'Default',
                            'dark' => 'Dark',
                            'light' => 'Light',
                            'github' => 'GitHub',
                            'monokai' => 'Monokai',
                            'dracula' => 'Dracula',
                            'tomorrow' => 'Tomorrow',
                            'solarized-dark' => 'Solarized Dark',
                            'solarized-light' => 'Solarized Light',
                        ])
                        ->default('default'),

                    Forms\Components\Select::make('font_size')
                        ->label('Font Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                        ])
                        ->default('medium'),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'language' => 'required|string',
            'code' => 'required|string',
            'filename' => 'nullable|string|max:255',
            'show_line_numbers' => 'boolean',
            'highlight_lines' => 'boolean',
            'highlighted_lines' => 'nullable|string',
            'copy_button' => 'boolean',
            'wrap_lines' => 'boolean',
            'theme' => 'string',
            'font_size' => 'in:small,medium,large',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $codeClasses = ['code-block'];
        $codeClasses[] = 'language-' . ($content['language'] ?? 'plaintext');
        $codeClasses[] = 'theme-' . ($content['theme'] ?? 'default');
        $codeClasses[] = 'font-' . ($content['font_size'] ?? 'medium');

        if (!empty($content['show_line_numbers'])) {
            $codeClasses[] = 'line-numbers';
        }

        if (!empty($content['wrap_lines'])) {
            $codeClasses[] = 'wrap-lines';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        // Code block header
        if (!empty($content['title']) || !empty($content['filename']) || !empty($content['copy_button'])) {
            $html .= '<div class="code-header">';
            
            if (!empty($content['title'])) {
                $html .= '<h4 class="code-title">' . e($content['title']) . '</h4>';
            }
            
            if (!empty($content['filename'])) {
                $html .= '<span class="code-filename">' . e($content['filename']) . '</span>';
            }
            
            $html .= '<div class="code-header-actions">';
            $html .= '<span class="code-language">' . strtoupper($content['language'] ?? 'TEXT') . '</span>';
            
            if (!empty($content['copy_button'])) {
                $html .= '<button type="button" class="copy-code-btn" data-clipboard-target="#code-' . uniqid() . '">Copy</button>';
            }
            
            $html .= '</div></div>';
        }

        // Code content
        $codeId = 'code-' . uniqid();
        $html .= '<div class="code-container">';
        $html .= '<pre class="' . implode(' ', $codeClasses) . '" id="' . $codeId . '"><code>';

        $codeLines = explode("\n", $content['code']);
        $highlightedLines = [];
        
        if (!empty($content['highlight_lines']) && !empty($content['highlighted_lines'])) {
            $highlightedLines = $this->parseHighlightedLines($content['highlighted_lines']);
        }

        foreach ($codeLines as $lineNumber => $line) {
            $lineNum = $lineNumber + 1;
            $lineClass = '';
            
            if (in_array($lineNum, $highlightedLines)) {
                $lineClass = ' class="highlighted-line"';
            }
            
            $html .= '<span class="code-line"' . $lineClass . '>' . htmlspecialchars($line) . '</span>';
            if ($lineNumber < count($codeLines) - 1) {
                $html .= "\n";
            }
        }

        $html .= '</code></pre>';
        $html .= '</div></div>';

        return $html;
    }

    private function parseHighlightedLines(string $highlightedLines): array
    {
        $lines = [];
        $parts = explode(',', $highlightedLines);
        
        foreach ($parts as $part) {
            $part = trim($part);
            
            if (strpos($part, '-') !== false) {
                // Range like "3-5"
                list($start, $end) = explode('-', $part, 2);
                $start = (int) trim($start);
                $end = (int) trim($end);
                
                for ($i = $start; $i <= $end; $i++) {
                    $lines[] = $i;
                }
            } else {
                // Single line
                $lines[] = (int) $part;
            }
        }
        
        return array_unique($lines);
    }
}
