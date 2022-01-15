<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use Composer\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // @TODO Eventually deletable for production package
        // This only affects the storefront
        if ('frontend.checkout.promotion.add' !== $event->getRequest()->get('_route')) {
            return;
        }
    }
}
