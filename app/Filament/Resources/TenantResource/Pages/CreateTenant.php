<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'phone_number' => $data['user']['phone_number'],
                'pin'          => Hash::make($data['pin']),
                'role'         => 'tenant',
                'status'       => 'active',
            ]);

            unset($data['user'], $data['pin']);

            $data['user_id']           = $user->id;
            $data['operational_hours'] = $data['operational_hours'] ?? [];

            return Tenant::create($data);
        });
    }
}