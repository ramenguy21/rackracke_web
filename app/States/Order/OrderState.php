<?php

namespace App\States\Order;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Placed::class)

            // ── Prepaid branch ──────────────────────────────────────────
            // Placed → Procuring  (ledger write fires here for prepaid)
            ->allowTransition(Placed::class, Procuring::class, Transitions\ToProc::class)

            // Procuring → OutForDelivery
            ->allowTransition(Procuring::class, OutForDelivery::class, Transitions\ToOutForDelivery::class)

            // OutForDelivery → Delivered  (prepaid final step)
            ->allowTransition(OutForDelivery::class, Delivered::class, Transitions\ToDelivered::class)

            // Delivered → Settled
            ->allowTransition(Delivered::class, Settled::class, Transitions\ToSettled::class)

            // Procuring → ProcurementFailed
            ->allowTransition(Procuring::class, ProcurementFailed::class, Transitions\ToProcurementFailed::class)

            // ProcurementFailed → Refunded
            ->allowTransition(ProcurementFailed::class, Refunded::class, Transitions\ToRefunded::class)

            // ── COD branch ──────────────────────────────────────────────
            // Placed → Cancelled (admin cancels before procuring, either payment type)
            ->allowTransition(Placed::class, Cancelled::class, Transitions\ToCancelled::class)

            // Placed → CodConfirm
            ->allowTransition(Placed::class, CodConfirm::class, Transitions\ToCodConfirm::class)

            // CodConfirm → Procuring  (no ledger write; COD cash not yet collected)
            ->allowTransition(CodConfirm::class, Procuring::class, Transitions\ToProc::class)

            // CodConfirm → Cancelled
            ->allowTransition(CodConfirm::class, Cancelled::class, Transitions\ToCancelled::class)

            // OutForDelivery → Collected  (COD cash in hand; ledger write fires here)
            ->allowTransition(OutForDelivery::class, Collected::class, Transitions\ToCollected::class)

            // Collected → Settled
            ->allowTransition(Collected::class, Settled::class, Transitions\ToSettled::class)

            // OutForDelivery → DeliveryFailed  (no ledger entry ever written)
            ->allowTransition(OutForDelivery::class, DeliveryFailed::class, Transitions\ToDeliveryFailed::class)

            // DeliveryFailed → ReturnedToSeller
            ->allowTransition(DeliveryFailed::class, ReturnedToSeller::class, Transitions\ToReturnedToSeller::class);
    }
}
