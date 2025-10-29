<?php

namespace App\Filament\Resources\ResourceContributorResource\Pages;

use App\Filament\Resources\ResourceContributorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageResourceContributors extends ManageRecords
{
    protected static string $resource = ResourceContributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
