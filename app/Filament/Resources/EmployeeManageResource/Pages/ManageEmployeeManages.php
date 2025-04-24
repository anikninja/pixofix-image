<?php

namespace App\Filament\Resources\EmployeeManageResource\Pages;

use App\Filament\Resources\EmployeeManageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Enums\RolesEnum;
use App\Models\User;
use Filament\Notifications\Notification;

class ManageEmployeeManages extends ManageRecords
{
    protected static string $resource = EmployeeManageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function afterCreate(): void
    {        
        if ($user = User::find($this->record->user_id)) {
            // Remove the User role if the user has it
            $user->removeRole(RolesEnum::User);
            // Assign the Employee role to the user
            $user->assignRole(RolesEnum::Employee);
            // Notify the user about the role change
            Notification::make()
                ->title('Role Change Notification')
                ->body('Your role has been changed to Employee.')
                ->icon('heroicon-o-user')
                ->sendToDatabase($user, isEventDispatched: true);
        }
    }
    protected function afterDelete(): void
    {
        if ($user = User::find($this->record->user_id)) {
            // Remove the Employee role from the user
            $user->removeRole(RolesEnum::Employee);
            // Assign the User role back to the user
            $user->assignRole(RolesEnum::User);
            // Notify the user about the role change
            Notification::make()
                ->title('Role Change Notification')
                ->body('Your role has been changed to User.')
                ->icon('heroicon-o-user')
                ->sendToDatabase($user, isEventDispatched: true);
        }
    }
}
