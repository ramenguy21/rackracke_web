<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\Cancelled;
use Spatie\ModelStates\Transition;

class ToCancelled extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Cancelled($this->order);
        $this->order->save();

        return $this->order;
    }
}
