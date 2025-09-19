<?php

namespace App\Filament\Resources\CmsContentWorkflowResource\Pages;

use App\Filament\Resources\CmsContentWorkflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCmsContentWorkflows extends ListRecords
{
    protected static string $resource = CmsContentWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
