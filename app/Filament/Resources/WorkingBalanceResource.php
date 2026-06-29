<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkingBalanceResource\Pages;
use App\Models\WorkingBalance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkingBalanceResource extends Resource
{
    protected static ?string $model = WorkingBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Saldo Kerja';
    protected static ?string $modelLabel = 'Saldo Kerja';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.phone_number')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('userRole')
                    ->label('Role')
                    ->state(fn (WorkingBalance $record) => match ($record->user?->role) {
                        'driver' => 'Driver',
                        'tenant' => 'Tenant',
                        default  => '-',
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Driver' ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state < 20000 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('balance', 'asc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkingBalances::route('/'),
        ];
    }
}