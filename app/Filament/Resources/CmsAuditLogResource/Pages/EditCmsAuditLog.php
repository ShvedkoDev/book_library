<?php

namespace App\Filament\Resources\CmsAuditLogResource\Pages;

use App\Filament\Resources\CmsAuditLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCmsAuditLog extends EditRecord
{
    protected static string $resource = CmsAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
