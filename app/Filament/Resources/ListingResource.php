<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('shopify_product_id')
                    ->label('Shopify ID')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                // Approve → marks live in portal. Then manually add to Shopify + link ID below.
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Listing $l) => $l->status === 'pending_review')
                    ->requiresConfirmation()
                    ->modalDescription('This will mark the listing as live. Then manually create the product in Shopify and link the Shopify product ID using "Link Shopify ID".')
                    ->action(function (Listing $listing) {
                        $listing->update(['status' => 'live', 'rejection_note' => null]);
                        Notification::make()->title('Listing approved and set live')->success()->send();
                    }),

                // Reject back to seller with a note
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Listing $l) => $l->status === 'pending_review')
                    ->form([
                        Textarea::make('rejection_note')
                            ->label('Rejection note for seller')
                            ->helperText('The seller will see this on their listing page.')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Listing $listing, array $data) {
                        $listing->update(['status' => 'draft', 'rejection_note' => $data['rejection_note']]);
                        Notification::make()->title('Listing rejected')->warning()->send();
                    }),

                // Link Shopify product ID after manually creating it in Shopify
                Action::make('link_shopify')
                    ->label('Link Shopify ID')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->visible(fn (Listing $l) => $l->status === 'live')
                    ->form([
                        TextInput::make('shopify_product_id')
                            ->label('Shopify product ID')
                            ->helperText('Copy from the Shopify admin product URL: /products/{id}')
                            ->required(),
                        TextInput::make('collection_handle')
                            ->label('Collection handle')
                            ->helperText('The seller\'s collection handle on Shopify (e.g. studio-karma)'),
                    ])
                    ->fillForm(fn (Listing $l) => [
                        'shopify_product_id' => $l->shopify_product_id,
                        'collection_handle'  => $l->collection_handle,
                    ])
                    ->action(function (Listing $listing, array $data) {
                        $listing->update([
                            'shopify_product_id' => $data['shopify_product_id'] ?: null,
                            'collection_handle'  => $data['collection_handle'] ?: null,
                        ]);
                        Notification::make()->title('Shopify product linked')->success()->send();
                    }),

                // Withdraw a live listing
                Action::make('withdraw')
                    ->label('Withdraw')
                    ->color('danger')
                    ->icon('heroicon-o-archive-box')
                    ->visible(fn (Listing $l) => $l->status === 'live')
                    ->requiresConfirmation()
                    ->modalDescription('This marks the listing as withdrawn in the portal. Remember to also archive the product in Shopify manually.')
                    ->action(function (Listing $listing) {
                        $listing->update(['status' => 'withdrawn']);
                        Notification::make()->title('Listing withdrawn — archive in Shopify manually')->warning()->send();
                    }),
            ])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(route('admin.export.listings'))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
        ];
    }
}
