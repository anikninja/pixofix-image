<?php

namespace App\Filament\Resources\EmployeeOrderResource\Pages;

use App\Filament\Resources\EmployeeOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeOrders extends ListRecords
{
    protected static string $resource = EmployeeOrderResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
