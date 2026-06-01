<?php

namespace App\States\Order\Transitions;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\States\Order\Collected;
use Spatie\ModelStates\Transition;

/**
 * COD only: cash is physically in hand at the doorstep.
 * This is the moment the ledger entry is written for COD orders.
 */
class ToCollected extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Collected($this->order);
        $this->order->save();

        LedgerEntry::create([
            'seller_id'       => $this->order->listing->seller_id,
            'order_id'        => $this->order->id,
            'amount_owed_pkr' => $this->order->sellerNet(),
            'status'          => 'owed',
            'credited_at'     => now(),
        ]);

        return $this->order;
    }
}
