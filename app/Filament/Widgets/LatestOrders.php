<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Order;
use App\Enums\RolesEnum;
use Illuminate\Support\Facades\Auth;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Latest Orders';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxWidth = '100%';
    protected static ?string $pollingInterval = '5s';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Employee->value);
    }
    
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()
            ->where('employee_id', null)
            ->where('status', 'pending')
            ->latest('order_date')
            ->orWhere('employee_id', Auth::user()->employee->id)
            ->latest('order_date');
    }    
    public function table(Table $table): Table
    {
        
        // Check if the user is an employee
        // if (Auth::user()->hasRole(RolesEnum::Employee->value)) {
        //     $query->where('employee_id', Auth::user()->employee->id);
        // }

        return $table
            ->query(
                $this->getTableQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'claimed' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->formatStateUsing(function (string $state) {
                        return ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                // Claim Order action
                Tables\Actions\Action::make('claim')
                    ->label('Claim Order')
                    ->action(function (Order $record): void {
                        if ($record->status !== 'pending') {
                            return;
                        }
                        $record->update(['status' => 'claimed', 'employee_id' => Auth::user()->employee->id]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record): string => 'Confirm Claim for Order "' . $record->order_number . '"')
                    ->modalSubheading('Are you sure you want to claim this order?')
                    ->modalButton('Yes, Claim')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->disabled(fn (Order $record): bool => $record->status !== 'pending')
                    ->hidden(fn (Order $record): bool => $record->status !== 'pending'),

                // Mark as Processing action
                Tables\Actions\Action::make('mark_processing')
                    ->label('Mark as Processing')
                    ->action(function (Order $record): void {
                        if ($record->status !== 'claimed') {
                            return;
                        }
                        $record->update(['status' => 'processing']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record): string => 'Confirm Mark as Processing for Order "' . $record->order_number . '"')
                    ->modalSubheading('Are you sure you want to mark this order as processing?')
                    ->modalButton('Yes, Mark as Processing')
                    ->color('info')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->disabled(fn (Order $record): bool => $record->status !== 'claimed')
                    ->hidden(fn (Order $record): bool => $record->status !== 'claimed'),
                // Mark as Completed action
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark as Completed')
                    ->action(function (Order $record): void {
                        if ($record->status !== 'processing') {
                            return;
                        }
                        $record->update(['status' => 'completed']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record): string => 'Confirm Mark as Completed for Order "' . $record->order_number . '"')
                    ->modalSubheading('Are you sure you want to mark this order as completed?')
                    ->modalButton('Yes, Mark as Completed')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->disabled(fn (Order $record): bool => $record->status !== 'processing')
                    ->hidden(fn (Order $record): bool => $record->status !== 'processing'),
                Tables\Actions\EditAction::make()
                    ->label('View'),
                    //->url(fn (Order $record): string => route('filament.resources.orders.view', $record)),
                
            ]);
    }
}
