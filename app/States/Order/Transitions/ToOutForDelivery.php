<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\OutForDelivery;
use Spatie\ModelStates\Transition;

class ToOutForDelivery extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new OutForDelivery($this->order);
        $this->order->save();

        return $this->order;
    }
}
