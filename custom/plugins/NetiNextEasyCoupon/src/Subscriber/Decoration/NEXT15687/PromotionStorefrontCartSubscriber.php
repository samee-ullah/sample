<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber\Decoration\NEXT15687;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\AbstractCartProcessor;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Subscriber\Storefront\StorefrontCartSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;

class PromotionStorefrontCartSubscriber extends StorefrontCartSubscriber
{
    /**
     * @var StorefrontCartSubscriber
     */
    private $storefrontCartSubscriber;

    public function __construct(
        CartService              $cartService,
        RequestStack             $requestStack,
        StorefrontCartSubscriber $storefrontCartSubscriber
    ) {
        parent::__construct($cartService, $requestStack);

        $this->storefrontCartSubscriber = $storefrontCartSubscriber;
    }

    public function onLineItemAdded(BeforeLineItemAddedEvent $event): void
    {
        $cart                    = $event->getCart();
        $easyCouponsReferenceIds =
            $cart->getLineItems()->filterType(AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE)->getReferenceIds();
        if (\in_array($event->getLineItem()->getReferencedId(), $easyCouponsReferenceIds, true)) {
            return;
        }

        $this->storefrontCartSubscriber->onLineItemAdded($event);
    }
}
