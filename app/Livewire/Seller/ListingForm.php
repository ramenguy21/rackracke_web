<?php

namespace App\Livewire\Seller;

use App\Models\Listing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-bare')]
class ListingForm extends Component
{

    public ?Listing $listing = null;

    public string $title       = '';
    public string $description = '';
    public string $condition   = '';
    public string $price       = '';
    public string $category    = '';
    public array  $photos      = [];  // stored paths after upload

    public int $commissionPct = 8;

    public function mount(?Listing $listing = null): void
    {
        if ($listing?->exists) {
            abort_if($listing->seller_id !== auth('seller')->id(), 403);
            $this->listing     = $listing;
            $this->title       = $listing->title;
            $this->description = $listing->description ?? '';
            $this->condition   = $listing->condition;
            $this->price       = (string) $listing->price_pkr;
            $this->photos      = $listing->photos ?? [];
        }
    }

    // Called from Alpine to sync photo paths (order/removal/new uploads)
    public function setPhotos(array $paths): void
    {
        $this->photos = array_values($paths);
    }

    public function netAmount(): int
    {
        $p = (int) preg_replace('/\D/', '', $this->price);
        return $p ? $p - (int) round($p * $this->commissionPct / 100) : 0;
    }

    public function saveDraft(): void
    {
        $this->save('draft');
    }

    public function publish(): void
    {
        $this->save('pending_review');
    }

    private function save(string $status): void
    {
        $this->validate([
            'title'     => 'required|string|max:200',
            'price'     => 'required|numeric|min:1',
            'condition' => 'required|string',
        ]);

        $data = [
            'seller_id'   => auth('seller')->id(),
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'condition'   => $this->condition,
            'price_pkr'   => (int) preg_replace('/\D/', '', $this->price),
            'photos'      => $this->photos,
            'status'      => $status,
        ];

        if ($this->listing?->exists) {
            $this->listing->update($data);
        } else {
            $this->listing = Listing::create($data);
        }

        $this->redirect(route('seller.listings.show', $this->listing), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.listing-form');
    }
}
