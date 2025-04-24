<?php

namespace App\Filament\Resources\EmployeeOrderResource\Pages;

use App\Filament\Resources\EmployeeOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeOrder extends CreateRecord
{
    protected static string $resource = EmployeeOrderResource::class;

    protected function authorizeAccess(): void
    {
        abort(403, 'Employees are not allowed to create or edit orders.');
        
    }
}
