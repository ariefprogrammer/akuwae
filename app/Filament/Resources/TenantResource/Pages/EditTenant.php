<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

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

            // 2. Bersihkan data yang bukan kolom Tenant
            unset($data['user'], $data['pin']);

            // 3. Update profil Tenant
            $record->update($data);

            return $record;
        });
    }
}