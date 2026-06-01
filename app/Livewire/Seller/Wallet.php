<?php

namespace App\Livewire\Seller;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Wallet'])]
class Wallet extends Component
{
    public function render()
    {
        $seller = auth('seller')->user();

        $owed = $seller->ledgerEntries()
            ->where('status', 'owed')
            ->orderByDesc('credited_at')
            ->get();

        $paidOut = $seller->ledgerEntries()
            ->where('status', 'paid_out')
            ->orderByDesc('paid_out_at')
            ->get();

        return view('livewire.seller.wallet', [
            'seller'          => $seller,
            'pendingTotal'    => $owed->sum('amount_owed_pkr'),
            'owedEntries'     => $owed,
            'paidOutEntries'  => $paidOut,
            'lifetimePaidOut' => $paidOut->sum('amount_owed_pkr'),
        ]);
    }
}
