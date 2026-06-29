<?php

namespace App\Filament\Resources\WorkingBalanceResource\Pages;

use App\Filament\Resources\WorkingBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkingBalance extends EditRecord
{
    protected static string $resource = WorkingBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
