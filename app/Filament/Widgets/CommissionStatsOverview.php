<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommissionStatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $todayCommission = Order::whereDate('created_at', today())
            ->sum('platform_commission');

        $thisMonthCommission = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('platform_commission');

        $lastMonthCommission = Order::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('platform_commission');

        return [
            Stat::make('Komisi Hari Ini', 'Rp ' . number_format($todayCommission, 0, ',', '.'))
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),
            Stat::make('Komisi Bulan Ini', 'Rp ' . number_format($thisMonthCommission, 0, ',', '.'))
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),
            Stat::make('Komisi Bulan Lalu', 'Rp ' . number_format($lastMonthCommission, 0, ',', '.'))
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),
        ];
    }
}