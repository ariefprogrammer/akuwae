<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Driver;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    protected function handleRecordCreation(array $data): Driver
    {
        return DB::transaction(function () use ($data) {
            // 1. Buat akun User dulu
            $user = User::create([
                'phone_number' => $data['user']['phone_number'],
                'pin'          => Hash::make($data['pin']),
                'role'         => 'driver',
                'status'       => 'active',
            ]);

            // 2. Bersihkan data yang bukan kolom Driver
            unset($data['user'], $data['pin']);

            // 3. Hubungkan ke user yang baru dibuat
            $data['user_id'] = $user->id;

            // 4. Buat profil Driver
            return Driver::create($data);
        });
    }
}