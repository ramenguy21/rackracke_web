<?php

namespace App\Livewire\Seller;

use App\Models\Listing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Rack'])]
class ListingDetail extends Component
{
    public Listing $listing;

    public bool $showDeleteConfirm = false;

    public function mount(Listing $listing): void
    {
        abort_if($listing->seller_id !== auth('seller')->id(), 403);
        $this->listing = $listing;
    }

    public function delete(): void
    {
        $this->listing->delete();
        $this->redirect(route('seller.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.listing-detail');
    }
}
