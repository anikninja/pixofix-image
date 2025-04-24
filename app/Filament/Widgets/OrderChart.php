<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Order;
use App\Enums\RolesEnum;
use Illuminate\Support\Facades\Auth;

class OrderChart extends ChartWidget
{
    // Removed HasRoles as it should be applied to the User model, not here.
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Order Chart';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    protected static ?string $maxWidth = '100%';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Admin->value);
    }
        
    protected function getType(): string
    {
        return 'bar';
    }
    protected function getFilters(): ?array
    {
        return [
            'by_month' => 'Default',
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'year' => 'This year',
        ];
    }
    public function getDescription(): ?string
    {
        $activeFilter = $this->filter;
        return match ($activeFilter) {
            'today' => 'Number of Orders today',
            'week' => 'Number of Orders this week',
            'month' => 'Number of Orders this month',
            'year' => 'Number of Orders this year',
            default => 'Number of Orders per month',
        };
    }
    protected function getData(): array
    {
        // Apply the active filter if it exists
        $activeFilter = $this->filter;

        $start = match ($activeFilter) {
            'by_month' => now()->subYear(),
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subYear(),
        };

        // Get the data for the chart
        $data = Trend::model(Order::class)
            ->between(
            start: $start,
            end: now(),
            )
            ->dateColumn('order_date')
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => match ($activeFilter) {
                'by_month' => \Carbon\Carbon::parse($value->date)->format('M y'),
                'today' => \Carbon\Carbon::parse($value->date)->format('H:i'),
                'week' => \Carbon\Carbon::parse($value->date)->format('D'),
                'month' => \Carbon\Carbon::parse($value->date)->format('d M'),
                'year' => \Carbon\Carbon::parse($value->date)->format('M Y'),
                default => \Carbon\Carbon::parse($value->date)->format('M y'),
            }),
        ];
    }

    
}
