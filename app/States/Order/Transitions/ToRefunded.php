<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\Refunded;
use Spatie\ModelStates\Transition;

class ToRefunded extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Refunded($this->order);
        $this->order->save();

        return $this->order;
    }
}
