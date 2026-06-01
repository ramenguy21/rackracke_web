<?php

namespace App\Livewire\Seller;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Reviews'])]
class Reviews extends Component
{
    public function render()
    {
        // Reviews are buyer-synced (read-only to seller).
        // Placeholder until buyer-side review sync is wired.
        return view('livewire.seller.reviews', [
            'reviews'     => collect(),
            'avgRating'   => null,
            'totalReviews' => 0,
        ]);
    }
}
