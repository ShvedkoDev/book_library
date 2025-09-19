<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class QuoteBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Quote Block';
    }

    public function getDescription(): string
    {
        return 'Blockquote with author attribution and styling options';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public function getCategory(): string
    {
        return 'Content';
    }

    protected function getType(): string
    {
        return 'quote';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Quote Content')
                ->schema([
                    Forms\Components\Textarea::make('quote_text')
                        ->label('Quote Text')
                        ->required()
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('author_name')
                        ->label('Author Name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('author_title')
                        ->label('Author Title/Position')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('author_company')
                        ->label('Author Company/Organization')
                        ->maxLength(255),

                    Forms\Components\FileUpload::make('author_image')
                        ->label('Author Photo')
                        ->image()
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                ]),

            Forms\Components\Section::make('Quote Styling')
                ->schema([
                    Forms\Components\Select::make('quote_style')
                        ->label('Quote Style')
                        ->options([
                            'default' => 'Default',
                            'bordered' => 'Bordered',
                            'highlighted' => 'Highlighted',
                            'minimal' => 'Minimal',
                            'card' => 'Card Style',
                        ])
                        ->default('default'),

                    Forms\Components\Select::make('quote_size')
                        ->label('Quote Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                        ])
                        ->default('medium'),

                    Forms\Components\ColorPicker::make('quote_color')
                        ->label('Quote Accent Color')
                        ->default('#3B82F6'),

                    Forms\Components\Toggle::make('show_quote_marks')
                        ->label('Show Quote Marks')
                        ->default(true),

                    Forms\Components\Toggle::make('italic_text')
                        ->label('Italic Text')
                        ->default(true),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'quote_text' => 'required|string|max:1000',
            'author_name' => 'nullable|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'author_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'quote_style' => 'in:default,bordered,highlighted,minimal,card',
            'quote_size' => 'in:small,medium,large',
            'quote_color' => 'nullable|string',
            'show_quote_marks' => 'boolean',
            'italic_text' => 'boolean',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $quoteClasses = ['quote-block'];
        $quoteClasses[] = 'quote-' . ($content['quote_style'] ?? 'default');
        $quoteClasses[] = 'quote-size-' . ($content['quote_size'] ?? 'medium');

        if (!empty($content['italic_text'])) {
            $quoteClasses[] = 'italic';
        }

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        $html .= '<blockquote class="' . implode(' ', $quoteClasses) . '"';
        
        if (!empty($content['quote_color'])) {
            $html .= ' style="border-color: ' . e($content['quote_color']) . ';"';
        }
        
        $html .= '>';

        if (!empty($content['show_quote_marks'])) {
            $html .= '<span class="quote-mark quote-mark-open">&ldquo;</span>';
        }

        $html .= '<p class="quote-text">' . nl2br(e($content['quote_text'])) . '</p>';

        if (!empty($content['show_quote_marks'])) {
            $html .= '<span class="quote-mark quote-mark-close">&rdquo;</span>';
        }

        // Author attribution
        if (!empty($content['author_name']) || !empty($content['author_image'])) {
            $html .= '<footer class="quote-author">';
            
            if (!empty($content['author_image'])) {
                $html .= '<img src="' . e($content['author_image']) . '" alt="' . e($content['author_name'] ?? '') . '" class="author-photo">';
            }
            
            $html .= '<div class="author-info">';
            
            if (!empty($content['author_name'])) {
                $html .= '<cite class="author-name">' . e($content['author_name']) . '</cite>';
            }
            
            if (!empty($content['author_title'])) {
                $html .= '<span class="author-title">' . e($content['author_title']) . '</span>';
            }
            
            if (!empty($content['author_company'])) {
                $html .= '<span class="author-company">' . e($content['author_company']) . '</span>';
            }
            
            $html .= '</div></footer>';
        }

        $html .= '</blockquote></div>';

        return $html;
    }
}
