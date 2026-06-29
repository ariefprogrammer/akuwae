<?php

namespace App\Filament\Resources\FareConfigResource\Pages;

use App\Filament\Resources\FareConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFareConfig extends EditRecord
{
    protected static string $resource = FareConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
