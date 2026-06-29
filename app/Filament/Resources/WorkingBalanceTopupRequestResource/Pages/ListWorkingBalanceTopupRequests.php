<?php

namespace App\Filament\Resources\WorkingBalanceTopupRequestResource\Pages;

use App\Filament\Resources\WorkingBalanceTopupRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkingBalanceTopupRequests extends ListRecords
{
    protected static string $resource = WorkingBalanceTopupRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
