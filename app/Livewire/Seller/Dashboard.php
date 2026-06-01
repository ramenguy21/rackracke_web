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
        $seller   = auth('seller')->user();
        $all      = $seller->listings()->latest()->get();

        // Build counts from the already-loaded collection — no extra queries
        $counts = [
            'all'    => $all->count(),
            'live'   => $all->where('status', 'live')->count(),
            'drafts' => $all->where('status', 'draft')->count(),
            'sold'   => $all->where('status', 'sold')->count(),
        ];

        $listings = match ($this->activeTab) {
            'live'   => $all->where('status', 'live')->values(),
            'drafts' => $all->where('status', 'draft')->values(),
            'sold'   => $all->where('status', 'sold')->values(),
            default  => $all,
        };

        $pendingPayout   = $seller->pendingPayoutTotal();
        $revenueThisWeek = $seller->ledgerEntries()
            ->where('credited_at', '>=', Carbon::now()->startOfWeek())
            ->sum('amount_owed_pkr');

        return view('livewire.seller.dashboard', [
            'seller'          => $seller,
            'listings'        => $listings,
            'counts'          => $counts,
            'pendingPayout'   => $pendingPayout,
            'revenueThisWeek' => (int) $revenueThisWeek,
        ]);
    }
}
