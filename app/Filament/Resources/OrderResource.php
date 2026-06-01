<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\States\Order\CodConfirm;
use App\States\Order\Collected;
use App\States\Order\DeliveryFailed;
use App\States\Order\OutForDelivery;
use App\States\Order\Placed;
use App\States\Order\Procuring;
use App\States\Order\ReturnedToSeller;
use App\States\Order\Settled;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('listing.title')
                    ->label('Item')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('listing.seller.shop_name')
                    ->label('Seller')
                    ->searchable(),
                TextColumn::make('state')
                    ->label('State')
                    ->badge()
                    ->formatStateUsing(fn ($state) => class_basename((string) $state)),
                TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => $state === 'cod' ? 'warning' : 'success'),
                TextColumn::make('sale_price_pkr')
                    ->label('Sale price')
                    ->formatStateUsing(fn ($state) => 'Rs. ' . number_format($state)),
                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // COD: confirm buyer before procuring
                Action::make('confirm_cod')
                    ->label('Confirm COD')
                    ->color('warning')
                    ->visible(fn (Order $order) => $order->state instanceof Placed && $order->isCod())
                    ->action(fn (Order $order) => $order->state->transitionTo(CodConfirm::class)),

                // Move to Procuring (prepaid: writes ledger; COD: does not)
                Action::make('start_procuring')
                    ->label('Start procuring')
                    ->color('primary')
                    ->visible(fn (Order $order) => $order->state instanceof Placed && $order->isPrepaid()
                        || $order->state instanceof CodConfirm)
                    ->action(fn (Order $order) => $order->state->transitionTo(Procuring::class)),

                Action::make('out_for_delivery')
                    ->label('Out for delivery')
                    ->color('primary')
                    ->visible(fn (Order $order) => $order->state instanceof Procuring)
                    ->action(fn (Order $order) => $order->state->transitionTo(OutForDelivery::class)),

                // Prepaid: delivered (no cash step)
                Action::make('delivered')
                    ->label('Delivered')
                    ->color('success')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery && $order->isPrepaid())
                    ->action(fn (Order $order) => $order->state->transitionTo(\App\States\Order\Delivered::class)),

                // COD: collected (cash in hand — writes ledger)
                Action::make('collected')
                    ->label('Collected (cash received)')
                    ->color('success')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery && $order->isCod())
                    ->action(fn (Order $order) => $order->state->transitionTo(Collected::class)),

                Action::make('settle')
                    ->label('Settle')
                    ->visible(fn (Order $order) => $order->state instanceof \App\States\Order\Delivered
                        || $order->state instanceof Collected)
                    ->action(fn (Order $order) => $order->state->transitionTo(Settled::class)),

                Action::make('procurement_failed')
                    ->label('Procurement failed')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof Procuring)
                    ->action(fn (Order $order) => $order->state->transitionTo(\App\States\Order\ProcurementFailed::class)),

                Action::make('delivery_failed')
                    ->label('Delivery failed')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery)
                    ->action(fn (Order $order) => $order->state->transitionTo(DeliveryFailed::class)),

                Action::make('returned_to_seller')
                    ->label('Return to seller')
                    ->color('warning')
                    ->visible(fn (Order $order) => $order->state instanceof DeliveryFailed)
                    ->action(fn (Order $order) => $order->state->transitionTo(ReturnedToSeller::class)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
