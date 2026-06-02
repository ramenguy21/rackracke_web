<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerEntryResource\Pages;
use App\Models\LedgerEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;

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
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => 'Rs. ' . number_format($state ?? 0))
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'paid_out' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state === 'paid_out' ? 'Paid out' : 'Owed'),
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
            ->filters([
                SelectFilter::make('status')
                    ->options(['owed' => 'Owed', 'paid_out' => 'Paid out'])
                    ->default('owed'),
                SelectFilter::make('seller')
                    ->relationship('seller', 'shop_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('mark_paid_out')
                    ->label('Mark paid out')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (LedgerEntry $entry) => $entry->isOwed())
                    ->requiresConfirmation()
                    ->action(fn (LedgerEntry $entry) => $entry->update([
                        'status'      => 'paid_out',
                        'paid_out_at' => now(),
                    ])),
            ])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(route('admin.export.ledger'))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkAction::make('bulk_mark_paid_out')
                    ->label('Mark all paid out')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark selected entries as paid out?')
                    ->modalDescription('This records that the seller has been paid. This cannot be undone.')
                    ->action(function (Collection $records) {
                        $records
                            ->filter(fn (LedgerEntry $e) => $e->isOwed())
                            ->each(fn (LedgerEntry $e) => $e->update([
                                'status'      => 'paid_out',
                                'paid_out_at' => now(),
                            ]));
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerEntries::route('/'),
        ];
    }
}
