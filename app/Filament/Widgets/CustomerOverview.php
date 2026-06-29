<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerOverview extends BaseWidget
{
    // Memastikan widget ini mengambil lebar penuh (12 kolom) agar membentuk satu baris tersendiri
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Menghitung jumlah customer baru dalam 30 hari terakhir
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $newCustomersCount = Customer::where('created_at', '>=', $thirtyDaysAgo)->count();

        return [
            Stat::make('Total Customer', Customer::count())
                ->color('success')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),

            Stat::make('Customer Baru (30 Hari Terakhir)', $newCustomersCount)
                ->color('info')
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