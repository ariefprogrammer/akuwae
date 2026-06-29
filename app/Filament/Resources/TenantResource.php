<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Login')
                    ->description('Data ini dipakai tenant untuk login ke aplikasi')
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

                Forms\Components\Section::make('Data Tenant')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('category')
                            ->options([
                                'Makanan Berat'     => 'Makanan Berat',
                                'Minuman'           => 'Minuman',
                                'Snack & Jajanan'   => 'Snack & Jajanan',
                                'Dessert'           => 'Dessert',
                                'Bakery'            => 'Bakery',
                                'Lainnya'           => 'Lainnya',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric(),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Toko')
                            ->image()
                            ->directory('tenants'),

                        Forms\Components\Select::make('verification_status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Toggle::make('is_open')
                            ->label('Buka?')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone_number')
                    ->label('No. HP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category'),

                Tables\Columns\BadgeColumn::make('verification_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_open')
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

                Infolists\Components\Section::make('Data Tenant')
                    ->schema([
                        Infolists\Components\TextEntry::make('store_name')
                            ->label('Nama Toko'),

                        Infolists\Components\TextEntry::make('category'),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('latitude'),
                        Infolists\Components\TextEntry::make('longitude'),

                        Infolists\Components\ImageEntry::make('photo')
                            ->label('Foto Toko'),

                        Infolists\Components\TextEntry::make('verification_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending'  => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            }),

                        Infolists\Components\IconEntry::make('is_open')
                            ->label('Buka?')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Terdaftar Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
