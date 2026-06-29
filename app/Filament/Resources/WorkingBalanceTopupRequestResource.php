<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkingBalanceTopupRequestResource\Pages;
use App\Models\WorkingBalance;
use App\Models\WorkingBalanceTopupRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class WorkingBalanceTopupRequestResource extends Resource
{
    protected static ?string $model = WorkingBalanceTopupRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Top Up Saldo Kerja';
    protected static ?string $modelLabel = 'Permintaan Top Up';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.phone_number')
                ->label('No. HP')
                ->disabled(),
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->prefix('Rp')
                ->disabled()
                ->required(),
            Forms\Components\FileUpload::make('proof_photo')
                ->image()
                ->disabled()
                ->disk('public'),
            Forms\Components\Select::make('status')
                ->options([
                    'pending'  => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ])
                ->required(),
        ]);
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
                    ->state(fn (WorkingBalanceTopupRequest $record) => match ($record->user?->role) {
                        'driver' => 'Driver',
                        'tenant' => 'Tenant',
                        default  => '-',
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Driver' ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('proof_photo')
                    ->label('Bukti')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/icons/icon-192x192.png')),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    }),
                Tables\Columns\TextColumn::make('approver.phone_number')
                    ->label('Disetujui Oleh')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WorkingBalanceTopupRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Top Up')
                    ->modalDescription(fn (WorkingBalanceTopupRequest $record) =>
                        "Setujui top up Rp " . number_format($record->amount, 0, ',', '.') . " untuk " . $record->user?->phone_number . "?"
                    )
                    ->action(function (WorkingBalanceTopupRequest $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                        ]);

                        $balance = WorkingBalance::getOrCreateFor($record->user_id);
                        $balance->topup(
                            $record->amount,
                            "Top up disetujui admin (request #{$record->id})"
                        );

                        Notification::make()
                            ->title('Top up berhasil disetujui')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WorkingBalanceTopupRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan Top Up')
                    ->action(function (WorkingBalanceTopupRequest $record) {
                        $record->update([
                            'status'      => 'rejected',
                            'approved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Permintaan top up ditolak')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkingBalanceTopupRequests::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}