<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use App\Services\ShopifyService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Listings';
    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seller.shop_name')->label('Seller')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('price_pkr')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => 'Rs. ' . number_format($state))
                    ->sortable(),
                TextColumn::make('condition')->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'live'           => 'success',
                        'sold'           => 'gray',
                        'pending_review' => 'warning',
                        'draft'          => 'gray',
                        default          => 'gray',
                    }),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Approve + push to Shopify
                Action::make('approve')
                    ->label('Approve & push live')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn (Listing $l) => $l->status === 'pending_review')
                    ->requiresConfirmation()
                    ->action(function (Listing $listing) {
                        app(ShopifyService::class)->pushListing($listing);
                        $listing->update(['status' => 'live']);
                    }),

                Action::make('withdraw')
                    ->label('Withdraw')
                    ->color('danger')
                    ->visible(fn (Listing $l) => $l->status === 'live')
                    ->requiresConfirmation()
                    ->action(fn (Listing $l) => $l->update(['status' => 'withdrawn'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
        ];
    }
}
