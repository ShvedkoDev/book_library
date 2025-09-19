<?php

namespace App\Services\ContentBlocks\Contracts;

interface ContentBlockInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getIcon(): string;
    public function getCategory(): string;
    public function getFormSchema(): array;
    public function getValidationRules(): array;
    public function getDefaultSettings(): array;
    public function render(array $content, array $settings = []): string;
    public function getPreviewTemplate(): string;
    public function validateData(array $data): array;
}
