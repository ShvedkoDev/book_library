<?php

namespace App\Filament\Resources\CmsRoleResource\Pages;

use App\Filament\Resources\CmsRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCmsRoles extends ListRecords
{
    protected static string $resource = CmsRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
