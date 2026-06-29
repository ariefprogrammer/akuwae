<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Driver;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    // Memastikan widget mengambil lebar penuh halaman dashboard
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // 1. Logika Data Customer
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $newCustomersCount = Customer::where('created_at', '>=', $thirtyDaysAgo)->count();

        // 2. Logika Data Driver
        $activeDriversCount = Driver::where('is_online', true)->count();

        return [
            // Card 1: Total Customer
            Stat::make('Total Customer', Customer::count())
                ->color('success')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),

            // Card 2: Customer Baru
            Stat::make('Customer Baru', $newCustomersCount)
                ->color('info')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),

            // Card 3: Total Driver
            Stat::make('Total Driver', Driver::count())
                ->color('success')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),

            // Card 4: Driver Aktif
            Stat::make('Driver Aktif', $activeDriversCount)
                ->color('warning')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                    'style' => '--col-span: 1; [&>div]:text-center [&>div]:w-full',
                ]),
        ];
    }

    // Mengatur agar semua card berada di satu baris (4 kolom)
    protected function getColumns(): int
    {
        return 4;
    }
}