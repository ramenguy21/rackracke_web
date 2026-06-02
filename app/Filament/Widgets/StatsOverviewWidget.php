<?php

namespace App\Filament\Widgets;

use App\Models\LedgerEntry;
use App\Models\Listing;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendingListings = Listing::where('status', 'pending_review')->count();

        $terminalStates = ['Settled', 'Refunded', 'ReturnedToSeller', 'Cancelled'];
        $activeOrders   = Order::whereNotIn('state', $terminalStates)->count();

        $totalOwed = LedgerEntry::where('status', 'owed')->sum('amount_owed_pkr');

        return [
            Stat::make('Pending review', $pendingListings)
                ->description('Listings awaiting approval')
                ->color($pendingListings > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-tag'),

            Stat::make('Active orders', $activeOrders)
                ->description('Orders not yet settled')
                ->color($activeOrders > 0 ? 'primary' : 'gray')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total owed', 'Rs. ' . number_format($totalOwed))
                ->description('Seller payouts pending')
                ->color($totalOwed > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
