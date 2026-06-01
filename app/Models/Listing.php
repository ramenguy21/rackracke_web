<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'condition',
        'price_pkr',
        'photos',
        'shopify_product_id',
        'collection_handle',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'price_pkr' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isPushableToShopify(): bool
    {
        return $this->status === 'pending_review';
    }
}
