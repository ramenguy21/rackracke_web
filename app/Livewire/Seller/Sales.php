<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', data: ['active' => 'Sales'])]
class Sales extends Component
{
    public string $activeTab = 'cancelled'; // cancelled | transit | delivered

    public function render()
    {
        $seller  = auth('seller')->user();
        $listingIds = $seller->listings()->pluck('id');

        $awaiting = Order::whereIn('listing_id', $listingIds)
            ->whereHas('state', fn ($q) => $q->where('state', 'Placed'))
            ->with('listing')
            ->latest()
            ->get();

        $tabOrders = match ($this->activeTab) {
            'transit'   => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['Procuring', 'OutForDelivery'])
                ->with('listing')->latest()->get(),
            'delivered' => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['Delivered', 'Collected', 'Settled'])
                ->with('listing')->latest()->get(),
            default     => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['Cancelled', 'DeliveryFailed', 'ReturnedToSeller', 'Refunded'])
                ->with('listing')->latest()->get(),
        };

        return view('livewire.seller.sales', [
            'awaiting'  => $awaiting,
            'tabOrders' => $tabOrders,
            'city'      => $seller->city,
        ]);
    }
}
