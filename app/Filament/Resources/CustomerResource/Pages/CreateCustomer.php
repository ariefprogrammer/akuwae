<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function handleRecordCreation(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'phone_number' => $data['user']['phone_number'],
                'pin'          => Hash::make($data['pin']),
                'role'         => 'customer',
                'status'       => 'active',
            ]);

            unset($data['user'], $data['pin']);

            $data['user_id'] = $user->id;

            return Customer::create($data);
        });
    }
}