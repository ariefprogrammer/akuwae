<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// use Filament\Forms;
// use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
// use Filament\Tables;
// use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Login')
                    ->description('Data ini dipakai driver untuk login ke aplikasi')
                    ->schema([
                        Forms\Components\TextInput::make('user.phone_number')
                            ->label('Nomor HP')
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                table: 'users',
                                column: 'phone_number',
                                ignorable: fn ($record) => $record?->user,
                            ),

                        Forms\Components\TextInput::make('pin')
                            ->label('PIN (6 digit)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->maxLength(6)
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText(fn (string $context) => $context === 'edit'
                                ? 'Kosongkan jika tidak ingin mengubah PIN'
                                : null),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Driver')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('vehicle_type')
                            ->label('Jenis Kendaraan')
                            ->options([
                                'motor' => 'Motor',
                                'mobil' => 'Mobil',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('vehicle_plate')
                            ->label('Plat Nomor')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\Select::make('verification_status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Toggle::make('is_online')
                            ->label('Online?')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone_number')
                    ->label('No. HP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Kendaraan'),

                Tables\Columns\TextColumn::make('vehicle_plate')
                    ->label('Plat Nomor'),

                Tables\Columns\BadgeColumn::make('verification_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Driver $record) => $record->verification_status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Driver')
                    ->modalDescription('Yakin ingin menyetujui driver ini?')
                    ->action(fn (Driver $record) => $record->update(['verification_status' => 'approved'])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Driver $record) => $record->verification_status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Driver')
                    ->modalDescription('Yakin ingin menolak driver ini?')
                    ->action(fn (Driver $record) => $record->update(['verification_status' => 'rejected'])),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Akun Login')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.phone_number')
                            ->label('Nomor HP'),

                        Infolists\Components\TextEntry::make('user.status')
                            ->label('Status Akun')
                            ->badge(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Data Driver')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama Lengkap'),

                        Infolists\Components\TextEntry::make('vehicle_type')
                            ->label('Jenis Kendaraan'),

                        Infolists\Components\TextEntry::make('vehicle_plate')
                            ->label('Plat Nomor'),

                        Infolists\Components\TextEntry::make('verification_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending'  => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            }),

                        Infolists\Components\IconEntry::make('is_online')
                            ->label('Online?')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Terdaftar Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Dokumen Driver')
                    ->description('Dokumen identitas yang diunggah driver saat onboarding')
                    ->schema([
                        Infolists\Components\TextEntry::make('document.ktp_number')
                            ->label('Nomor KTP')
                            ->placeholder('Belum diisi'),

                        Infolists\Components\TextEntry::make('document.sim_number')
                            ->label('Nomor SIM')
                            ->placeholder('Belum diisi'),

                        Infolists\Components\ImageEntry::make('document.stnk_photo')
                            ->label('Foto STNK')
                            ->height(200)
                            ->placeholder('Belum diunggah'),

                        Infolists\Components\ImageEntry::make('document.selfie_ktp_photo')
                            ->label('Foto Selfie + KTP')
                            ->height(200)
                            ->placeholder('Belum diunggah'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Status Realtime')
                    ->description('Data ini diperbarui otomatis oleh aplikasi driver, bukan oleh admin')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_latitude')
                            ->label('Latitude Saat Ini'),

                        Infolists\Components\TextEntry::make('current_longitude')
                            ->label('Longitude Saat Ini'),

                        Infolists\Components\TextEntry::make('last_activity_at')
                            ->label('Aktivitas Terakhir')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum ada aktivitas'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
