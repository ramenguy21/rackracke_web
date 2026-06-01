<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerEntryResource\Pages;
use App\Models\LedgerEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Ledger';
    protected static ?string $modelLabel = 'Ledger entry';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seller.shop_name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.listing.title')
                    ->label('Item')
                    ->limit(40),
                TextColumn::make('amount_owed_pkr')
                    ->label('Amount owed')
                    ->formatStateUsing(fn ($state) => 'Rs. ' . number_format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'paid_out' ? 'success' : 'warning'),
                TextColumn::make('credited_at')
                    ->label('Credited')
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('paid_out_at')
                    ->label('Paid out')
                    ->dateTime('M j, Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('credited_at', 'desc')
            ->actions([
                Action::make('mark_paid_out')
                    ->label('Mark paid out')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (LedgerEntry $entry) => $entry->isOwed())
                    ->requiresConfirmation()
                    ->action(fn (LedgerEntry $entry) => $entry->update([
                        'status'     => 'paid_out',
                        'paid_out_at' => now(),
                    ])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerEntries::route('/'),
        ];
    }
}
