<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\RestoreAction::make(),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
                Actions\ForceDeleteAction::make(),
            ])->label('Actions'),
        ];
    }

    private function notifyAdminsAboutStatusChange(): void
    {
        $orderNumber = $this->record->order_number;
        $originalStatus = $this->record->getOriginal('status');
        $currentStatus = $this->record->status;

        if (!$this->record->isDirty('status')) {
            return;
        }

        $statusMessages = $this->getStatusMessages($orderNumber);
        $statusKey = "$originalStatus:$currentStatus";
        $body = $statusMessages[$statusKey] ?? "Order: $orderNumber status updated to $currentStatus.";

        $this->sendNotificationToAdmins($body);
    }

    private function getStatusMessages(string $orderNumber): array
    {
        return [
            'pending:Claimed' => "Order: $orderNumber has been claimed.",
            'Claimed:processing' => "Order: $orderNumber has been marked as processing.",
            'processing:completed' => "Order: $orderNumber has been completed.",
        ];
    }

    private function sendNotificationToAdmins(string $body): void
    {
        Notification::make()
            ->title('Order Status Updated')
            ->body($body)
            ->icon('heroicon-o-refresh')
            ->actions([
                Actions\Action::make('view')
                    ->label('View Order')
                    ->url($this->getResource()::getUrl('edit', ['record' => $this->record->id])),
            ])
            ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
    }

}
