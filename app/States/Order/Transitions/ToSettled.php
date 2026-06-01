<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\Settled;
use Spatie\ModelStates\Transition;

class ToSettled extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Settled($this->order);
        $this->order->save();

        return $this->order;
    }
}
