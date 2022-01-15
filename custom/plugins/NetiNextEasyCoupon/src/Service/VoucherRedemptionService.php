<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\AbstractCartProcessor;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Extension\CartExtension;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\ItemBuilder;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Gateway\EasyCouponGateway;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Gateway\PromotionGatewayInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class VoucherRedemptionService
{
    /**
     * @var EasyCouponGateway
     */
    private $easyCouponGateway;

    /**
     * @var PromotionGatewayInterface
     */
    private $promotionGateway;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    public function __construct(
        EasyCouponGateway $easyCouponGateway,
        PromotionGatewayInterface $promotionGateway,
        CartService $cartService,
        ItemBuilder $itemBuilder,
        PluginConfig $pluginConfig
    ) {
        $this->cartService       = $cartService;
        $this->itemBuilder       = $itemBuilder;
        $this->easyCouponGateway = $easyCouponGateway;
        $this->promotionGateway  = $promotionGateway;
        $this->pluginConfig      = $pluginConfig;
    }

    /**
     * @param BeforeLineItemAddedEvent $event
     *
     * @throws EasyCouponError
     */
    public function addVoucherToCart(BeforeLineItemAddedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $item = $event->getLineItem();

        if (LineItem::PROMOTION_LINE_ITEM_TYPE !== $item->getType()) {
            return;
        }

        $code = $item->getReferencedId();

        if (null === $code || '' === $code) {
            return;
        }

        $cart            = $event->getCart();
        $cartEasyCoupons = $cart->getLineItems()->filterType(AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);

        // Check if the Easy Coupon already exists in cart
        foreach ($cartEasyCoupons as $easyCoupon) {
            if ($code === $easyCoupon->getReferencedId()) {
                // @TODO Create better exception message
                throw new EasyCouponError($code, 'Already in basket');
            }
        }

        $context         = $event->getSalesChannelContext();
        $isPromotionCode = $this->isPromotionCode($code, $context);
        $easyCoupon      = $this->getEasyCoupon($code, $context);

        if (!($easyCoupon instanceof EasyCouponEntity)) {
            return;
        }

        if ($isPromotionCode) {
            // @TODO Create better exception message
            throw new EasyCouponError($code, 'Code collision conflict');
        }

        /** @var CartExtension|null $extension */
        $extension = $this->getExtension($cart);

        if (null === $extension) {
            throw new EasyCouponError($code, 'Unknown error occured');
        }

        // Remove the placeholder item created in
        // \Shopware\Storefront\Controller\CartLineItemController::addPromotion
        $item->setRemovable(true);
        $cart->remove($item->getId());

        $extension->addVoucherCode($code);
        $cart->addExtension(CartExtension::KEY, $extension);

        $lineItem = $this->itemBuilder->buildPlaceholderItem($code);

        $this->cartService->add($cart, $lineItem, $context);
    }

    public function removeVoucherFromCart(Cart $cart, LineItem $lineItem): void
    {
        if (AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE !== $lineItem->getType()) {
            return;
        }

        $code = $lineItem->getReferencedId();

        if (empty($code)) {
            return;
        }

        $this->removeCode($code, $cart);
    }

    private function getExtension(Cart $cart): ?Struct
    {
        if (!$cart->hasExtension(CartExtension::KEY)) {
            $cart->addExtension(CartExtension::KEY, new CartExtension());
        }

        return $cart->getExtension(CartExtension::KEY);
    }

    private function isPromotionCode(string $code, SalesChannelContext $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_OR,
            [
                new EqualsAnyFilter('promotion.code', [ $code ]),
                new EqualsAnyFilter('promotion.individualCodes.code', [ $code ]),
            ]
        ));

        return $this->promotionGateway->get($criteria, $context)->count() > 0;
    }

    public function getEasyCoupon(string $code, SalesChannelContext $context): ?EasyCouponEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('code', [ $code ]));
        $criteria->addAssociations([ 'valueType', 'conditions' ]);

        return $this->easyCouponGateway->get($criteria, $context)->first();
    }

    private function removeCode(string $code, Cart $cart): void
    {
        /** @var CartExtension|null $extension */
        $extension = $this->getExtension($cart);

        if (null === $extension) {
            return;
        }

        $extension->removeCode($code);

        $cart->addExtension(CartExtension::KEY, $extension);
    }
}
