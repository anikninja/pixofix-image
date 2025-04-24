<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Order;
use App\Enums\RolesEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderItems;
use Filament\Tables\Columns\ImageColumn;


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
        // If the user is an employee, filter orders by employee_id
        if (Auth::user()->hasRole(RolesEnum::Employee->value) && Auth::user()->employee) {
            return Order::query()
                ->where('employee_id', Auth::user()->employee->id)
                ->latest('order_date');
        }
        // Default query for other users
        return Order::query()->whereRaw('1 = 0');
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
                ImageColumn::make('images')
                        ->getStateUsing(function ($record) {
                            return OrderItems::where('order_id', $record->id)->pluck('upload');
                        })
                        ->default('https://fakeimg.pl/600x400?text=No+Image&font=bebas')
                        ->size(40)
                        ->circular()
                        ->stacked()
                        ->limit(3)
                        ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'claimed' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(function (string $state) {
                        return ucfirst($state);
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->dateTimeTooltip()
                    ->alignRight(),
            ]);
    }
}
