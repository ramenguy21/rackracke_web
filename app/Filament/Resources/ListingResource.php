<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use App\Services\ShopifyService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                ImageColumn::make('photo')
                    ->label('')
                    ->getStateUsing(fn (Listing $record) =>
                        !empty($record->photos)
                            ? Storage::disk('s3')->temporaryUrl($record->photos[0], now()->addHour())
                            : null
                    )
                    ->width(56)
                    ->height(72)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px']),

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
                        'pending_review' => 'warning',
                        'sold'           => 'gray',
                        'draft'          => 'gray',
                        'withdrawn'      => 'danger',
                        default          => 'gray',
                    }),
                TextColumn::make('rejection_note')
                    ->label('Rejection note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'          => 'Draft',
                        'pending_review' => 'Pending review',
                        'live'           => 'Live',
                        'sold'           => 'Sold',
                        'withdrawn'      => 'Withdrawn',
                    ]),
            ])
            ->actions([
                // Approve + push to Shopify
                Action::make('approve')
                    ->label('Approve & push live')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn (Listing $l) => $l->status === 'pending_review')
                    ->requiresConfirmation()
                    ->action(function (Listing $listing) {
                        try {
                            app(ShopifyService::class)->pushListing($listing);
                            $listing->update(['status' => 'live', 'rejection_note' => null]);
                            Notification::make()
                                ->title('Pushed to Shopify and set live')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Shopify push failed', [
                                'listing_id' => $listing->id,
                                'error'      => $e->getMessage(),
                            ]);
                            Notification::make()
                                ->title('Shopify push failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Reject back to seller with a note
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Listing $l) => $l->status === 'pending_review')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_note')
                            ->label('Rejection note for seller')
                            ->helperText('The seller will see this message on their listing page.')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Listing $listing, array $data) {
                        $listing->update([
                            'status'         => 'draft',
                            'rejection_note' => $data['rejection_note'],
                        ]);
                        Notification::make()
                            ->title('Listing rejected')
                            ->body('Seller will see the rejection note.')
                            ->warning()
                            ->send();
                    }),

                // Withdraw a live listing (archives on Shopify)
                Action::make('withdraw')
                    ->label('Withdraw')
                    ->color('danger')
                    ->icon('heroicon-o-archive-box')
                    ->visible(fn (Listing $l) => $l->status === 'live')
                    ->requiresConfirmation()
                    ->action(function (Listing $listing) {
                        try {
                            app(ShopifyService::class)->withdrawListing($listing);
                        } catch (\Throwable $e) {
                            Log::error('Shopify withdraw failed', [
                                'listing_id' => $listing->id,
                                'error'      => $e->getMessage(),
                            ]);
                        }
                        $listing->update(['status' => 'withdrawn']);
                        Notification::make()
                            ->title('Listing withdrawn')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('register_shopify_webhook')
                    ->label('Register Shopify webhook')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Register orders/paid webhook')
                    ->modalDescription('This registers the orders/paid webhook on Shopify pointing at this app. Safe to run multiple times — it checks for duplicates first.')
                    ->action(function () {
                        try {
                            $address = url('/api/webhooks/shopify/orders-paid');
                            $result  = app(ShopifyService::class)->registerWebhook($address);
                            Notification::make()
                                ->title('Webhook registered (ID: ' . ($result['id'] ?? 'existing') . ')')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Shopify webhook registration failed', ['error' => $e->getMessage()]);
                            Notification::make()
                                ->title('Webhook registration failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
        ];
    }
}
