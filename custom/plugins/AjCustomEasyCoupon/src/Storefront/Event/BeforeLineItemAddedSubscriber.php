<?php

namespace AjCustomEasyCoupon\Storefront\Event;

use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class BeforeLineItemAddedSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemAddedEvent::class => 'beforeLineItemAddedEmailRecipient',
        ];
    }

    public function beforeLineItemAddedEmailRecipient(BeforeLineItemAddedEvent $event)
    {
        $lineItem = $event->getLineItem();
        $isGiftVoucher = $lineItem->getPayloadValue('netiNextEasyCoupon');

        if ($isGiftVoucher) {
            // Grab Email from request
            $request = $this->requestStack->getCurrentRequest();
            $email = $request->get('custom_vouchers_email_field');
            // Store email in payload
            if ($email) {
                $lineItem->setPayloadValue('recipientEmail', $email);
            }
        }
    }
}
