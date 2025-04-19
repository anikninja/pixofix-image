<?php

namespace App\Filament\Resources\PendingOrderResource\Pages;

use App\Filament\Resources\PendingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingOrders extends ListRecords
{
    protected static string $resource = PendingOrderResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
