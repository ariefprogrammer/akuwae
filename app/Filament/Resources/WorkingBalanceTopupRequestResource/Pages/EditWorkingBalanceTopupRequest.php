<?php

namespace App\Filament\Resources\WorkingBalanceTopupRequestResource\Pages;

use App\Filament\Resources\WorkingBalanceTopupRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkingBalanceTopupRequest extends EditRecord
{
    protected static string $resource = WorkingBalanceTopupRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
