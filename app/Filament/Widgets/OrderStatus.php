<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Enums\RolesEnum;
use App\Models\Order;


class OrderStatus extends BaseWidget
{
    protected ?string $heading = "Order Status";
    protected ?string $description = "Total number of orders by status";
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Admin);
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Order Completed', Order::query()->where('status', 'completed')->count())
            ->chart(
                Order::query()
                    ->where('status', 'completed')
                    ->selectRaw('count(*) as count, DATE(updated_at) as date4')
                    ->groupBy('date4')
                    ->orderBy('date4', 'asc')
                    ->limit(10)
                    ->pluck('count', 'date4')
                    ->toArray(),
            )->color('success'),
            Stat::make('Order In-Process', Order::query()->where('status', 'processing')->count())
                ->chart(
                    Order::query()
                        ->where('status', 'processing')
                        ->selectRaw('count(*) as count, DATE(updated_at) as date3')
                        ->groupBy('date3')
                        ->orderBy('date3', 'asc')
                        ->limit(10)
                        ->pluck('count', 'date3')
                        ->toArray(),
                )->color('info'),
            Stat::make('Order Claimed', Order::query()->where('status', 'claimed')->count())
                ->chart(
                    Order::query()
                        ->where('status', 'claimed')
                        ->selectRaw('count(*) as count, DATE(updated_at) as date2')
                        ->groupBy('date2')
                        ->orderBy('date2', 'asc')
                        ->limit(10)
                        ->pluck('count', 'date2')
                        ->toArray(),
                )->color('gray'),
            Stat::make('Order Pending', Order::query()->where('status', 'pending')->count())
                ->chart(
                    Order::query()
                        ->where('status', 'pending')
                        ->selectRaw('count(*) as count, DATE(order_date) as date')
                        ->groupBy('date')
                        ->orderBy('date', 'asc')
                        ->limit(10)
                        ->pluck('count', 'date')
                        ->toArray(),
                )->color('warning'),
        ];
    }
}
