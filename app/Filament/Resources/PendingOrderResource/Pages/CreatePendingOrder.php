<?php

namespace App\Filament\Resources\PendingOrderResource\Pages;

use App\Filament\Resources\PendingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingOrder extends CreateRecord
{
    protected static string $resource = PendingOrderResource::class;

    protected function authorizeAccess(): void
    {
        abort(403, 'Employees are not allowed to create orders.');
        
    }
}
