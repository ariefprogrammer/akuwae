<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // 1. Update data User (phone_number wajib, pin opsional)
            $userUpdate = [
                'phone_number' => $data['user']['phone_number'],
            ];

            if (filled($data['pin'] ?? null)) {
                $userUpdate['pin'] = Hash::make($data['pin']);
            }

            $record->user->update($userUpdate);

            // 2. Bersihkan data yang bukan kolom Driver
            unset($data['user'], $data['pin']);

            // 3. Update profil Driver
            $record->update($data);

            return $record;
        });
    }
}