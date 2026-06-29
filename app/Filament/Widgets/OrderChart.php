<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Order Bulan Ini';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $daysInMonth = now()->daysInMonth;

        $orders = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->get()
            ->groupBy(fn ($order) => $order->created_at->format('j'));

        $labels = [];
        $data   = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = $day;
            $data[]   = $orders->get((string) $day, collect())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Order',
                    'data'  => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize'  => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}