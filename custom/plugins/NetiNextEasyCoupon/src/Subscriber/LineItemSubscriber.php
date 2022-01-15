<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\CartDataDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemptionService;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LineItemSubscriber implements EventSubscriberInterface
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var VoucherRedemptionService
     */
    private $voucherRedemptionService;

    /** @var RequestStack  */
    private $requestStack;

    /** @var CartService  */
    private $cartService;

    public function __construct(
        VoucherRedemptionService $voucherRedemptionService,
        PluginConfig $pluginConfig,
        RequestStack $requestStack,
        CartService $cartService
    ) {
        $this->voucherRedemptionService = $voucherRedemptionService;
        $this->pluginConfig             = $pluginConfig;
        $this->requestStack             = $requestStack;
        $this->cartService              = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemAddedEvent::class => [
                [ 'onLineItemAdded', 5000 ],
            ],

            BeforeLineItemRemovedEvent::class => [
                [ 'onLineItemRemoved', 5000 ],
            ],

            AfterLineItemAddedEvent::class => [
                [ 'onAfterLineItemAdded', 5000 ],
            ],
        ];
    }

    /**
     * @param AfterLineItemAddedEvent $event
     */
    public function onAfterLineItemAdded(AfterLineItemAddedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $cart           = $event->getCart();
        $vouchersInCart = null;

        /** @var CartDataDefinition $cartDataDefinition */
        $cartDataDefinition = $cart->getData()->get('easy-coupon-codes');

        if (null !== $cartDataDefinition) {
            $vouchersInCart = $cartDataDefinition->getEasyCoupons();
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $voucherCodes = $request->getSession()->get('EasyCouponVoucherCodes');

        if (null === $voucherCodes) {
            return;
        }

        foreach ($voucherCodes as $code) {
            if (isset($vouchersInCart[$code])) {
                continue;
            }

            $itemBuilder = new PromotionItemBuilder();

            $lineItem = $itemBuilder->buildPlaceholderItem($code);
            $this->cartService->add($event->getCart(), $lineItem, $event->getSalesChannelContext());
        }
    }

    /**
     * @param BeforeLineItemAddedEvent $event
     *
     * @throws EasyCouponError
     */
    public function onLineItemAdded(BeforeLineItemAddedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->voucherRedemptionService->addVoucherToCart($event);
    }

    public function onLineItemRemoved(BeforeLineItemRemovedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->voucherRedemptionService->removeVoucherFromCart($event->getCart(), $event->getLineItem());
    }
}
