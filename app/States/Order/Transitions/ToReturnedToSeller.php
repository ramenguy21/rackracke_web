<?php

namespace App\States\Order\Transitions;

use App\Models\Order;
use App\States\Order\ReturnedToSeller;
use Spatie\ModelStates\Transition;

class ToReturnedToSeller extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        $this->order->state = new ReturnedToSeller($this->order);
        $this->order->save();

        return $this->order;
    }
}
