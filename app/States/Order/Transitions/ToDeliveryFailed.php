<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\DeliveryFailed;
use Spatie\ModelStates\Transition;

class ToDeliveryFailed extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new DeliveryFailed($this->order);
        $this->order->save();

        return $this->order;
    }
}
