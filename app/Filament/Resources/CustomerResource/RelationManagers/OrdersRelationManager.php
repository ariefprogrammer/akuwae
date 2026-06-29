<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Riwayat Order';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('service_type')
                    ->label('Jenis')
                    ->colors([
                        'info'    => 'antar',
                        'success' => 'makan',
                        'warning' => 'custom',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success'   => 'completed',
                        'secondary' => 'cancelled',
                        'warning'   => fn ($state): bool => ! in_array($state, ['completed', 'cancelled']),
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed'      => 'Selesai',
                        'cancelled'      => 'Dibatalkan',
                        'finding_driver' => 'Cari Driver',
                        'processing'     => 'Driver Menuju',
                        'preparing'      => 'Disiapkan',
                        'ready'          => 'Siap Diambil',
                        'pickup'         => 'Diambil Driver',
                        'delivering'     => 'Diantar',
                        'arrived'        => 'Driver Tiba',
                        'waiting_tenant' => 'Menunggu Toko',
                        'item_mismatch'  => 'Cek Perubahan',
                        default          => $state,
                    }),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                    ]),

                Tables\Columns\TextColumn::make('total_fare')
                    ->label('Total')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'finding_driver' => 'Cari Driver',
                        'processing'     => 'Driver Menuju',
                        'preparing'      => 'Disiapkan',
                        'ready'          => 'Siap Diambil',
                        'pickup'         => 'Diambil Driver',
                        'delivering'     => 'Diantar',
                        'arrived'        => 'Driver Tiba',
                        'waiting_tenant' => 'Menunggu Toko',
                        'item_mismatch'  => 'Cek Perubahan',
                        'completed'      => 'Selesai',
                        'cancelled'      => 'Dibatalkan',
                    ]),
            ])
            ->headerActions([])  // read-only: tidak ada tombol "Create" — order dibuat dari sisi app, bukan admin
            ->actions([])        // tidak ada Edit/Delete — ini cuma riwayat
            ->bulkActions([]);
    }
}