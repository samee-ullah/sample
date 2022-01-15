<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemGroupBuilder;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

// @see \Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor
abstract class AbstractCartProcessor implements CartProcessorInterface
{
    public const EASY_COUPON_DATA_KEY                  = 'easy-coupons';

    public const EASY_COUPON_DATA_KEY_REDEMPTION_ORDER = 'easy-coupon-redemption-order';

    public const EASY_COUPON_LINE_ITEM_TYPE            = 'easy-coupon';

    protected int $redemptionOrder;

    /**
     * @var LineItemGroupBuilder
     */
    private $groupBuilder;

    /**
     * @var CartCalculator
     */
    private $cartCalculator;

    public function __construct(
        CartCalculator $cartCalculator,
        LineItemGroupBuilder $groupBuilder
    ) {
        $this->cartCalculator = $cartCalculator;
        $this->groupBuilder   = $groupBuilder;
    }

    /**
     * @param CartDataCollection  $data
     * @param Cart                $original
     * @param Cart                $toCalculate
     * @param SalesChannelContext $context
     * @param CartBehavior        $behavior
     *
     * @throws EasyCouponError
     */
    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        // if there is no collected promotion we may return - nothing to calculate!
        if (!$data->has(self::EASY_COUPON_DATA_KEY)) {
            return;
        }

        $redemptionOrder = $data->get(self::EASY_COUPON_DATA_KEY_REDEMPTION_ORDER);
        if (!is_array($redemptionOrder)) {
            return;
        }

        // always make sure we have
        // the line item group builder for our
        // line item group rule inside the cart data
        $toCalculate->getData()->set(LineItemGroupBuilder::class, $this->groupBuilder);

        if (
            'recalculation' === $original->getName()
            && $behavior->hasPermission(PromotionProcessor::SKIP_PROMOTION)
        ) {
            $items = $original->getLineItems()->filterType(self::EASY_COUPON_LINE_ITEM_TYPE);

            foreach ($items as $item) {
                if ($this->redemptionOrder !== $redemptionOrder[$item->getId()]) {
                    continue;
                }

                if ($item->getPriceDefinition() instanceof PercentagePriceDefinition) {
                    $calculatedItem = $this->cartCalculator->calculateLineItem($item, $toCalculate, $context);

                    if (null !== $calculatedItem) {
                        continue; // skip adding because it was added in the recalculation
                    }
                }

                $toCalculate->add($item);
            }

            return;
        }

        // if we are in recalculation,
        // we must not re-add any promotions. just leave it as it is.
        if ($behavior->hasPermission(PromotionProcessor::SKIP_PROMOTION)) {
            return;
        }

        /** @var LineItemCollection $discountLineItems */
        $discountLineItems = $data->get(self::EASY_COUPON_DATA_KEY);

        $discountLineItems = $discountLineItems->filter(function($lineItem) use ($redemptionOrder) {
            return $this->redemptionOrder === $redemptionOrder[$lineItem->getId()];
        });

        // calculate the whole cart with the
        // new list of created promotion discount line items
        $this->cartCalculator->calculate(
            new LineItemCollection($discountLineItems),
            $original,
            $toCalculate,
            $context,
            $behavior
        );
    }
}
