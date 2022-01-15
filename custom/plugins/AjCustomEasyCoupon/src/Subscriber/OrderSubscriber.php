<?php

declare(strict_types=1);

namespace AjCustomEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Subscriber\OrderSubscriber as OrderSubscriberParent;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;

class OrderSubscriber extends OrderSubscriberParent
{
    public function sendVoucherActivateMailByOrderTransaction(StateMachineStateChangeEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->orderVoucherService->sendVoucherActivateMailByOrderTransaction(
            $event->getTransition()->getEntityId(),
            $event->getNextState()->getTechnicalName(),
            $event->getContext()
        );
    }
}
