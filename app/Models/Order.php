<?php

namespace App\Models;

use App\States\Order\OrderState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\ModelStates\HasStates;

class Order extends Model
{
    use HasFactory, HasUuids, HasStates;

    protected $fillable = [
        'listing_id',
        'shopify_order_id',
        'payment_type',
        'state',
        'sale_price_pkr',
        'take_rate_pct',
        'buyer_contact',
    ];

    protected function casts(): array
    {
        return [
            'state' => OrderState::class,
            'sale_price_pkr' => 'integer',
            'take_rate_pct' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(LedgerEntry::class);
    }

    public function isPrepaid(): bool
    {
        return $this->payment_type === 'prepaid';
    }

    public function isCod(): bool
    {
        return $this->payment_type === 'cod';
    }

    public function sellerNet(): int
    {
        return $this->sale_price_pkr - (int) round($this->sale_price_pkr * $this->take_rate_pct / 100);
    }
}
