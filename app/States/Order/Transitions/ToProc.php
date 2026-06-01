<?php

namespace App\States\Order\Transitions;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\States\Order\Procuring;
use Spatie\ModelStates\Transition;

/**
 * Transitions an order into Procuring.
 *
 * For PREPAID orders: writes the ledger entry here — cash is already banked.
 * For COD orders: does NOT write a ledger entry — cash is not yet collected.
 */
class ToProc extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Procuring($this->order);
        $this->order->save();

        if ($this->order->isPrepaid()) {
            LedgerEntry::create([
                'seller_id'       => $this->order->listing->seller_id,
                'order_id'        => $this->order->id,
                'amount_owed_pkr' => $this->order->sellerNet(),
                'status'          => 'owed',
                'credited_at'     => now(),
            ]);
        }

        return $this->order;
    }
}
