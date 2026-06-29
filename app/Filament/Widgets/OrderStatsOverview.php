<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayCount = Order::whereDate('created_at', today())->count();

        $thisMonthCount = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonthCount = Order::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        return [
            Stat::make('Order Hari Ini', $todayCount)
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),
            Stat::make('Order Bulan Ini', $thisMonthCount)
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),
            Stat::make('Order Bulan Lalu', $lastMonthCount)
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),
        ];
    }
}