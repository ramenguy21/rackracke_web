<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\Delivered;
use Spatie\ModelStates\Transition;

class ToDelivered extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new Delivered($this->order);
        $this->order->save();

        return $this->order;
    }
}
