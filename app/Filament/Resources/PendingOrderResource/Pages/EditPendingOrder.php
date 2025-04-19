<?php

namespace App\Filament\Resources\PendingOrderResource\Pages;

use App\Filament\Resources\PendingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingOrder extends EditRecord
{
    protected static string $resource = PendingOrderResource::class;

    protected function authorizeAccess(): void
    {
            abort(403, 'Employees are not allowed to create or edit orders.');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
