<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Enums\RolesEnum;
use App\Models\Order;

class EmployeeOrderStatus extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = "My Order Status";
    protected int | string | array $columnSpan = 'full';
    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Employee->value);
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Order Completed', Order::query()
                ->where('status', 'completed')
                ->where('employee_id', Auth::user()->employee->id)
                ->count()
            ),
            Stat::make('Order Processing', Order::query()
                ->where('status', 'processing')
                ->where('employee_id', Auth::user()->employee->id)
                // ->whereHas('employee', function ($query) {
                //     $query->where('user_id', Auth::user()->id);
                // })
                ->count()
            ),
            Stat::make('Order Claimed', Order::query()->where('status', 'claimed')
            ->where('status', 'claimed')
                ->where('employee_id', Auth::user()->employee->id)
                ->count()
            ),
            Stat::make('Order Pending', Order::query()->where('status', 'pending')
                ->where('status', 'pending')
                ->where('employee_id', Auth::user()->employee->id)
                ->count()
            ),
        ];
    }
}
