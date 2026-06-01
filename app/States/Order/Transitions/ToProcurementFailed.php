<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\ProcurementFailed;
use Spatie\ModelStates\Transition;

class ToProcurementFailed extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new ProcurementFailed($this->order);
        $this->order->save();

        return $this->order;
    }
}
