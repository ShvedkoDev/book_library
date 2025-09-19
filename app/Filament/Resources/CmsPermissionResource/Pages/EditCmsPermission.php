<?php

namespace App\Filament\Resources\CmsPermissionResource\Pages;

use App\Filament\Resources\CmsPermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCmsPermission extends EditRecord
{
    protected static string $resource = CmsPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
