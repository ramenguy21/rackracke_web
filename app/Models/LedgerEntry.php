<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'seller_id',
        'order_id',
        'amount_owed_pkr',
        'status',
        'credited_at',
        'paid_out_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_owed_pkr' => 'integer',
            'credited_at' => 'datetime',
            'paid_out_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isOwed(): bool
    {
        return $this->status === 'owed';
    }
}
