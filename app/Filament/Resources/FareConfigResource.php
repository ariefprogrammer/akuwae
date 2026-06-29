<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FareConfigResource\Pages;
use App\Models\FareConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FareConfigResource extends Resource
{
    protected static ?string $model = FareConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Konfigurasi Tarif';
    protected static ?string $modelLabel = 'Konfigurasi Tarif';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Layanan')
                ->schema([
                    Forms\Components\Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'antar'  => 'Tolong Antar',
                            'makan'  => 'Tolong Makan',
                            'custom' => 'Tolong Custom',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('vehicle_type')
                        ->label('Jenis Kendaraan')
                        ->options([
                            'motor' => 'Motor',
                            'mobil' => 'Mobil',
                        ])
                        ->required()
                        ->native(false)
                        ->unique(
                            table: 'fare_configs',
                            column: 'vehicle_type',
                            modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Forms\Get $get) {
                                return $rule->where('service_type', $get('service_type'));
                            },
                            ignoreRecord: true,
                        )
                        ->validationMessages([
                            'unique' => 'Kombinasi layanan dan kendaraan ini sudah ada.',
                        ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Komponen Tarif')
                ->schema([
                    Forms\Components\TextInput::make('base_fare')
                        ->label('Biaya Dasar')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->minValue(0),

                    Forms\Components\TextInput::make('per_km_fare')
                        ->label('Tarif per Km')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->minValue(0),

                    Forms\Components\TextInput::make('per_kg_fare')
                        ->label('Tarif per Kg')
                        ->helperText('Hanya berlaku untuk Tolong Custom')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->minValue(0),

                    Forms\Components\TextInput::make('platform_commission_pct')
                        ->label('Komisi Platform')
                        ->numeric()
                        ->suffix('%')
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.1),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Layanan')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'antar'  => 'Tolong Antar',
                        'makan'  => 'Tolong Makan',
                        'custom' => 'Tolong Custom',
                        default  => $state,
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'antar'  => 'info',
                        'makan'  => 'success',
                        'custom' => 'warning',
                        default  => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Kendaraan')
                    ->formatStateUsing(fn (string $state) => $state === 'motor' ? '🛵 Motor' : '🚗 Mobil')
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_fare')
                    ->label('Biaya Dasar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('per_km_fare')
                    ->label('Tarif/Km')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('per_kg_fare')
                    ->label('Tarif/Kg')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('platform_commission_pct')
                    ->label('Komisi')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('service_type')
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Layanan')
                    ->options([
                        'antar'  => 'Tolong Antar',
                        'makan'  => 'Tolong Makan',
                        'custom' => 'Tolong Custom',
                    ]),
                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->label('Kendaraan')
                    ->options([
                        'motor' => 'Motor',
                        'mobil' => 'Mobil',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFareConfigs::route('/'),
            'create' => Pages\CreateFareConfig::route('/create'),
            'edit'   => Pages\EditFareConfig::route('/{record}/edit'),
        ];
    }
}