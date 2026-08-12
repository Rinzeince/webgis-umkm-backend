<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Admin Force Reset (editing another user)
        if (filled($data['admin_new_password'] ?? null)) {
            $data['password'] = Hash::make($data['admin_new_password']);
        }
        // 2. User Self Password Change (editing own account with toggle active)
        elseif (!empty($data['change_password_toggle']) && filled($data['self_new_password'] ?? null)) {
            $data['password'] = Hash::make($data['self_new_password']);
        }

        // Clean up virtual form fields that do not exist in the database table
        unset(
            $data['admin_new_password'],
            $data['admin_new_password_confirmation'],
            $data['change_password_toggle'],
            $data['self_current_password'],
            $data['self_new_password'],
            $data['self_new_password_confirmation'],
            $data['current_password'],
            $data['new_password'],
            $data['new_password_confirmation']
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()?->isAdmin()),
        ];
    }
}
