<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Sales'])]
class Sales extends Component
{
    public string $activeTab = 'cancelled'; // cancelled | transit | delivered

    public function render()
    {
        $seller     = auth('seller')->user();
        $listingIds = $seller->listings()->pluck('id');

        // state is a cast column, not a relation — use where() directly
        $awaiting = Order::whereIn('listing_id', $listingIds)
            ->whereIn('state', ['Placed', 'CodConfirm', 'Procuring'])
            ->with('listing')
            ->latest()
            ->get();

        $tabOrders = match ($this->activeTab) {
            'transit'   => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['OutForDelivery'])
                ->with('listing')->latest()->get(),
            'delivered' => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['Delivered', 'Collected', 'Settled'])
                ->with('listing')->latest()->get(),
            default     => Order::whereIn('listing_id', $listingIds)
                ->whereIn('state', ['Cancelled', 'DeliveryFailed', 'ReturnedToSeller', 'Refunded', 'ProcurementFailed'])
                ->with('listing')->latest()->get(),
        };

        return view('livewire.seller.sales', [
            'awaiting'  => $awaiting,
            'tabOrders' => $tabOrders,
            'city'      => $seller->city,
        ]);
    }
}
