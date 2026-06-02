<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\CodConfirm;
use App\States\Order\Collected;
use App\States\Order\DeliveryFailed;
use App\States\Order\OutForDelivery;
use App\States\Order\Placed;
use App\States\Order\Procuring;
use App\States\Order\ReturnedToSeller;
use App\States\Order\Settled;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

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
                TextColumn::make('buyer_contact')
                    ->label('Buyer contact')
                    ->placeholder('—')
                    ->copyable()
                    ->limit(30),
                TextColumn::make('state')
                    ->label('State')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match (class_basename((string) $state)) {
                        'Placed'             => 'Placed',
                        'CodConfirm'         => 'COD confirm',
                        'Procuring'          => 'Procuring',
                        'OutForDelivery'     => 'Out for delivery',
                        'Delivered'          => 'Delivered',
                        'Collected'          => 'Collected',
                        'Settled'            => 'Settled',
                        'ProcurementFailed'  => 'Procurement failed',
                        'DeliveryFailed'     => 'Delivery failed',
                        'Refunded'           => 'Refunded',
                        'ReturnedToSeller'   => 'Returned to seller',
                        'Cancelled'          => 'Cancelled',
                        default              => class_basename((string) $state),
                    })
                    ->color(fn ($state) => match (class_basename((string) $state)) {
                        'Placed', 'CodConfirm'                       => 'warning',
                        'Procuring', 'OutForDelivery'                => 'primary',
                        'Delivered', 'Collected', 'Settled'          => 'success',
                        'ProcurementFailed', 'DeliveryFailed',
                        'Refunded', 'ReturnedToSeller', 'Cancelled'  => 'danger',
                        default                                       => 'gray',
                    }),
                TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => $state === 'cod' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('sale_price_pkr')
                    ->label('Sale price')
                    ->formatStateUsing(fn ($state) => 'Rs. ' . number_format($state)),
                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('state')
                    ->label('State')
                    ->options([
                        'Placed'            => 'Placed',
                        'CodConfirm'        => 'COD confirm',
                        'Procuring'         => 'Procuring',
                        'OutForDelivery'    => 'Out for delivery',
                        'Delivered'         => 'Delivered',
                        'Collected'         => 'Collected',
                        'Settled'           => 'Settled',
                        'ProcurementFailed' => 'Procurement failed',
                        'DeliveryFailed'    => 'Delivery failed',
                        'Refunded'          => 'Refunded',
                        'ReturnedToSeller'  => 'Returned to seller',
                        'Cancelled'         => 'Cancelled',
                    ]),
                SelectFilter::make('payment_type')
                    ->options(['prepaid' => 'Prepaid', 'cod' => 'COD']),
            ])
            ->actions([
                // Cancel before any work has started (both payment types)
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Order $order) => $order->state instanceof Placed)
                    ->requiresConfirmation()
                    ->modalHeading('Cancel this order?')
                    ->modalDescription('This will cancel the order. No ledger entry has been written yet.')
                    ->action(fn (Order $order) => $order->state->transitionTo(Cancelled::class)),

                // COD: confirm buyer before procuring
                Action::make('confirm_cod')
                    ->label('Confirm COD')
                    ->color('warning')
                    ->visible(fn (Order $order) => $order->state instanceof Placed && $order->isCod())
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(CodConfirm::class)),

                // COD: cancel after confirm (buyer backed out)
                Action::make('cancel_cod')
                    ->label('Cancel')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof CodConfirm)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(Cancelled::class)),

                // Move to Procuring
                Action::make('start_procuring')
                    ->label('Start procuring')
                    ->color('primary')
                    ->visible(fn (Order $order) => ($order->state instanceof Placed && $order->isPrepaid())
                        || $order->state instanceof CodConfirm)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(Procuring::class)),

                Action::make('procurement_failed')
                    ->label('Procurement failed')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof Procuring)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(\App\States\Order\ProcurementFailed::class)),

                Action::make('out_for_delivery')
                    ->label('Out for delivery')
                    ->color('primary')
                    ->visible(fn (Order $order) => $order->state instanceof Procuring)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(OutForDelivery::class)),

                // Prepaid: delivered (no cash step)
                Action::make('delivered')
                    ->label('Delivered')
                    ->color('success')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery && $order->isPrepaid())
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(\App\States\Order\Delivered::class)),

                // COD: collected (cash in hand — writes ledger)
                Action::make('collected')
                    ->label('Cash collected')
                    ->color('success')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery && $order->isCod())
                    ->requiresConfirmation()
                    ->modalDescription('Confirm that cash has been physically collected from the buyer. This will write the seller\'s ledger entry.')
                    ->action(fn (Order $order) => $order->state->transitionTo(Collected::class)),

                Action::make('delivery_failed')
                    ->label('Delivery failed')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof OutForDelivery)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(DeliveryFailed::class)),

                Action::make('returned_to_seller')
                    ->label('Return to seller')
                    ->color('warning')
                    ->visible(fn (Order $order) => $order->state instanceof DeliveryFailed)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(ReturnedToSeller::class)),

                Action::make('settle')
                    ->label('Settle')
                    ->color('success')
                    ->visible(fn (Order $order) => $order->state instanceof \App\States\Order\Delivered
                        || $order->state instanceof Collected)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(Settled::class)),

                Action::make('refund')
                    ->label('Refund')
                    ->color('danger')
                    ->visible(fn (Order $order) => $order->state instanceof \App\States\Order\ProcurementFailed)
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->state->transitionTo(\App\States\Order\Refunded::class)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
