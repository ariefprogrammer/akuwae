<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
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
use Filament\Notifications\Notification;
// use Filament\Tables;
// use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Login')
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
                            ->minLength(6)
                            ->maxLength(6)
                            ->required(fn (string $context) => $context === 'create')
                            ->visible(fn (string $context) => $context === 'create')
                            ->helperText('PIN awal saat akun dibuat. Untuk mengubah PIN setelahnya, gunakan tombol "Ubah PIN".'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Customer')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('customers'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone_number')
                    ->label('No. HP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.status')
                    ->label('Status Akun')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('changePin')
                    ->label('Ubah PIN')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('pin')
                            ->label('PIN Baru (6 digit)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(6)
                            ->maxLength(6)
                            ->required(),

                        Forms\Components\TextInput::make('pin_confirmation')
                            ->label('Konfirmasi PIN Baru')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(6)
                            ->maxLength(6)
                            ->required()
                            ->same('pin'),
                    ])
                    ->action(function (\App\Models\Customer $record, array $data) {
                        $record->user->update([
                            'pin' => Hash::make($data['pin']),
                        ]);

                        Notification::make()
                            ->title('PIN berhasil diubah')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Infolists\Components\Section::make('Data Customer')
                    ->schema([
                        // Baris 1: Foto Profil berada di tengah sendirian
                        Infolists\Components\Grid::make(1)
                            ->schema([
                                Infolists\Components\ImageEntry::make('photo')
                                    ->label('') // Tanpa label agar posisi bulatannya simetris di tengah
                                    ->circular()
                                    ->height(120) // Rasio 1:1 sebelum dibuat bulat
                                    ->width(120)  
                                    ->alignCenter(), // Posisi tengah
                            ]),

                        // Baris 2: Nama dan Tanggal dengan modifikasi CSS inline agar sangat rapat
                        Infolists\Components\Grid::make(1)
                            ->schema([
                                Infolists\Components\Group::make([
                                    // Nama Lengkap
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('') 
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large) 
                                        ->weight(\Filament\Support\Enums\FontWeight::Bold) 
                                        ->alignCenter()
                                        // Memaksa margin bawaan Filament di bawah teks ini menjadi 0
                                        ->extraAttributes(['style' => 'margin-bottom: 0px !important; padding-bottom: 0px !important;']), 

                                    // Terdaftar Pada
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('') 
                                        ->dateTime('d M Y H:i')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Small) 
                                        ->color('gray') 
                                        ->alignCenter()
                                        // Memaksa margin atas teks ini menjadi minimal/0
                                        ->extraAttributes(['style' => 'margin-top: -24px !important; padding-top: 0px !important;']), 
                                ]),
                            ])
                            // Menghilangkan gap bawaan grid pembungkus luar jika ada
                            ->extraAttributes(['style' => 'gap: -100px !important;']),
                    ]),

                Infolists\Components\Section::make('Akun Login')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.phone_number')
                            ->label('Nomor HP'),

                        Infolists\Components\TextEntry::make('user.status')
                            ->label('Status Akun')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
                    

                Infolists\Components\Section::make('Alamat Tersimpan')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('savedAddresses')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('label')
                                    ->label('Label')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('address_text')
                                    ->label('Alamat')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('latitude')
                                    ->label('Latitude'),

                                Infolists\Components\TextEntry::make('longitude')
                                    ->label('Longitude'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
