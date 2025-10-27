<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store whether to send email (for future implementation)
        $sendEmail = $data['send_credentials_email'] ?? false;

        // Generate random password if not provided
        if (empty($data['password'])) {
            $generatedPassword = Str::password(12, letters: true, numbers: true, symbols: true);
            $data['password'] = bcrypt($generatedPassword);

            // Display generated password to admin
            Notification::make()
                ->success()
                ->title('Password Generated')
                ->body('Generated password: ' . $generatedPassword . ' - Please provide this to the user.')
                ->persistent()
                ->send();
        }

        // Remove the checkbox from data (it's not a model field)
        unset($data['send_credentials_email']);

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User created successfully';
    }
}
