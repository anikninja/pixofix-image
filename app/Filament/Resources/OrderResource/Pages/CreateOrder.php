<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Employee;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        // Notify all employees about the new order
        $this->notifyEmployees();
        
    }
    private function notifyEmployees()
    {
        $recipient =  Employee::findEmployee($this->record->employee_id ?? null); ;
        $notification = Notification::make()
            ->title($this->record->employee_id ? 'New Order Assigned' : 'New Order Created')
            ->body('Order: ' . $this->record->order_number . ($this->record->employee_id ? ' has been assigned to you.' : ' has been created.'))
            ->icon($this->record->employee_id ? 'heroicon-o-user' : 'heroicon-o-check-circle');

        return $notification->sendToDatabase($recipient, isEventDispatched: true);
    }
}
