<?php

namespace App\Services\ContentBlocks;

use Filament\Forms;

class TextBlock extends AbstractContentBlock
{
    public function getName(): string
    {
        return 'Text Block';
    }

    public function getDescription(): string
    {
        return 'Rich text content with formatting options';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function getCategory(): string
    {
        return 'Content';
    }

    protected function getType(): string
    {
        return 'text';
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->maxLength(255),

                    Forms\Components\RichEditor::make('content')
                        ->label('Text Content')
                        ->required()
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'blockquote',
                            'codeBlock',
                        ]),

                    Forms\Components\Select::make('heading_level')
                        ->label('Title Heading Level')
                        ->options([
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ])
                        ->default('h2')
                        ->visible(fn ($get) => !empty($get('title'))),
                ]),
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'heading_level' => 'nullable|in:h1,h2,h3,h4,h5,h6',
        ];
    }

    public function render(array $content, array $settings = []): string
    {
        $classes = $this->generateBlockClasses($settings);
        $styles = $this->generateBlockStyles($settings);

        $html = '<div class="' . $classes . '"';
        if ($styles) {
            $html .= ' style="' . $styles . '"';
        }
        $html .= '>';

        if (!empty($content['title'])) {
            $headingLevel = $content['heading_level'] ?? 'h2';
            $html .= '<' . $headingLevel . ' class="block-title">' . e($content['title']) . '</' . $headingLevel . '>';
        }

        $html .= '<div class="block-content">' . $content['content'] . '</div>';
        $html .= '</div>';

        return $html;
    }
}
