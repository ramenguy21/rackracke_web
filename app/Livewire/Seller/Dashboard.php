<?php

namespace App\Livewire\Seller;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Rack'])]
class Dashboard extends Component
{
    public string $activeTab = 'all'; // all | live | drafts | sold

    public function render()
    {
        $seller = auth('seller')->user();

        $listingsQuery = $seller->listings()->latest();

        $listings = match ($this->activeTab) {
            'live'   => (clone $listingsQuery)->where('status', 'live')->get(),
            'drafts' => (clone $listingsQuery)->where('status', 'draft')->get(),
            'sold'   => (clone $listingsQuery)->where('status', 'sold')->get(),
            default  => $listingsQuery->get(),
        };

        $counts = [
            'all'    => $seller->listings()->count(),
            'live'   => $seller->listings()->where('status', 'live')->count(),
            'drafts' => $seller->listings()->where('status', 'draft')->count(),
            'sold'   => $seller->listings()->where('status', 'sold')->count(),
        ];

        $pendingPayout = $seller->pendingPayoutTotal();

        $weekStart  = Carbon::now()->startOfWeek();
        $revenueThisWeek = $seller->ledgerEntries()
            ->where('credited_at', '>=', $weekStart)
            ->sum('amount_owed_pkr');

        return view('livewire.seller.dashboard', [
            'seller'          => $seller,
            'listings'        => $listings,
            'counts'          => $counts,
            'pendingPayout'   => $pendingPayout,
            'revenueThisWeek' => $revenueThisWeek,
        ]);
    }
}
