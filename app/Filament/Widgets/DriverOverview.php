<?php

namespace App\Filament\Widgets;

use App\Models\Driver; // Pastikan model Driver sudah di-import
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DriverOverview extends BaseWidget
{
    // Memastikan widget ini mengambil lebar penuh agar membentuk baris kedua secara vertikal di bawah customer
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeDriversCount = Driver::where('is_online', true)->count();

        return [
            Stat::make('Total Driver', Driver::count())
                ->color('success')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),

            Stat::make('Driver Aktif', $activeDriversCount)
                ->color('warning')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),
        ];
    }

    // Mengatur agar baris ini dibagi menjadi 2 kolom card
    protected function getColumns(): int
    {
        return 2;
    }
}