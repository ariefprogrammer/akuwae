<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardOverview; 
use App\Filament\Widgets\OrderStatsOverview; 
use App\Filament\Widgets\OrderChart; 
use App\Filament\Widgets\CommissionStatsOverview; 
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            DashboardOverview::class,
            OrderStatsOverview::class,
            CommissionStatsOverview::class,
            OrderChart::class,
        ];
    }

    
}