<?php

namespace App\Filament\Resources\WorkingBalanceResource\Pages;

use App\Filament\Resources\WorkingBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkingBalances extends ListRecords
{
    protected static string $resource = WorkingBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
