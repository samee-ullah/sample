<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\Calculator\DiscountAbsoluteCalculator;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\Calculator\DiscountPercentageCalculator;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountCalculatorResult;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountLineItem;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\DiscountCalculatorNotFoundException;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartCalculator
{
    use CartInformationTrait;

    /**
     * @var AmountCalculator
     */
    private $amountCalculator;

    /**
     * @var AbsolutePriceCalculator
     */
    private $absolutePriceCalculator;

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    public function __construct(
        AmountCalculator $amountCalculator,
        AbsolutePriceCalculator $absolutePriceCalculator,
        PercentagePriceCalculator $percentagePriceCalculator
    ) {
        $this->amountCalculator          = $amountCalculator;
        $this->absolutePriceCalculator   = $absolutePriceCalculator;
        $this->percentagePriceCalculator = $percentagePriceCalculator;
    }

    /**
     * Calculates the cart including the new discount line items.
     * The calculation process will first determine the correct values for
     * the different discount line item types (percentage, absolute, ...) and then
     * recalculate the whole cart with these new items.
     *
     * @param LineItemCollection  $discountLineItems
     * @param Cart                $original
     * @param Cart                $calculated
     * @param SalesChannelContext $context
     * @param CartBehavior        $behaviour
     *
     * @throws EasyCouponError
     */
    public function calculate(
        LineItemCollection $discountLineItems,
        Cart $original,
        Cart $calculated,
        SalesChannelContext $context,
        CartBehavior $behaviour
    ): void {
        foreach ($discountLineItems as $discountItem) {
            $this->calculateLineItem($discountItem, $calculated, $context);
        }
    }

    /**
     * Calculates a single line item.
     *
     * @param LineItem            $item
     * @param Cart                $calculatedCart
     * @param SalesChannelContext $context
     *
     * @return LineItem|null
     * @throws EasyCouponError
     */
    public function calculateLineItem (LineItem $item, Cart $calculatedCart, SalesChannelContext $context): ?LineItem
    {
        $result = $this->calculateDiscount($item, $calculatedCart, $context);

        if (0.00 === abs($result->getPrice()->getTotalPrice())) {
            return null;
        }

        $item->setPrice($result->getPrice());
        $calculatedCart->add($item);

        $this->calculateCart($calculatedCart, $context);

        return $item;
    }

    /**
     * Calculates and returns the discount based on the settings of
     * the provided discount line item.
     *
     * @param LineItem            $lineItem
     * @param Cart                $calculatedCart
     * @param SalesChannelContext $salesChannelContext
     *
     * @return DiscountCalculatorResult
     * @throws EasyCouponError
     */
    private function calculateDiscount(
        LineItem $lineItem,
        Cart $calculatedCart,
        SalesChannelContext $salesChannelContext
    ): DiscountCalculatorResult {
        if (null === $lineItem->getPriceDefinition()) {
            throw new EasyCouponError($lineItem->getId(), 'Incomplete line item');
        }

        $discount = new DiscountLineItem(
            $lineItem->getLabel() ?? '',
            $lineItem->getPriceDefinition(),
            $lineItem->getPayload(),
            $lineItem->getReferencedId()
        );

        switch ($discount->getType()) {
            case EasyCouponEntity::VALUE_TYPE_ABSOLUTE:
                $calculator = new DiscountAbsoluteCalculator($this->absolutePriceCalculator);
                break;

            case EasyCouponEntity::VALUE_TYPE_PERCENTAL:
                $calculator = new DiscountPercentageCalculator(
                    $this->percentagePriceCalculator,
                    $this->absolutePriceCalculator
                );
                break;

            default:
                // @TODO Check if the cast is okay.
                throw new DiscountCalculatorNotFoundException((string) $discount->getType());
        }

        // @TODO Exclude non relevant line items from calculation
        $priceCollection = new PriceCollection();

        if (
            EasyCouponEntity::VALUE_TYPE_ABSOLUTE === $discount->getType()
            && \is_string($discount->getPayload()['productId'])
        ) {
            $value = (float) $discount->getPayload()['value'];
            $priceCollection->add(
                new CalculatedPrice(
                    $value,
                    $value,
                    new CalculatedTaxCollection([new CalculatedTax(0, 0, $value)]),
                    new TaxRuleCollection([new TaxRule(0)])
                )
            );
        } else {
            foreach ($calculatedCart->getLineItems() as $item) {
                $priceCollection->add($item->getPrice());
            }
        }

        $result           = $calculator->calculate($discount, $priceCollection, $salesChannelContext);
        $maxDiscountValue = $this->getMaxDiscountValue($calculatedCart, $salesChannelContext);

        if (abs($result->getPrice()->getTotalPrice()) > abs($maxDiscountValue)) {
            $result = $this->limitDiscountResult($maxDiscountValue, $priceCollection, $result, $salesChannelContext);
        }

        return $result;
    }

    private function calculateCart(Cart $cart, SalesChannelContext $context): void
    {
        $amount = $this->amountCalculator->calculate(
            $cart->getLineItems()->getPrices(),
            $cart->getDeliveries()->getShippingCosts(),
            $context
        );

        $cart->setPrice($amount);
    }

    private function getMaxDiscountValue(Cart $cart, SalesChannelContext $context): float
    {
        if (CartPrice::TAX_STATE_NET === $context->getTaxState()) {
            return $cart->getPrice()->getNetPrice();
        }

        return $cart->getPrice()->getTotalPrice();
    }

    private function limitDiscountResult(
        float $maxDiscountValue,
        PriceCollection $priceCollection,
        DiscountCalculatorResult $originalResult,
        SalesChannelContext $context
    ): DiscountCalculatorResult {
        $price = $this->absolutePriceCalculator->calculate(
            -abs($maxDiscountValue),
            $priceCollection,
            $context
        );

        return new DiscountCalculatorResult($price);
    }
}
