<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\Calculator;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountCalculatorInterface;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountCalculatorResult;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountLineItem;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\InvalidPriceDefinitionException;
use Shopware\Core\Checkout\Cart\Exception\PayloadKeyNotFoundException;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Defaults;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountAbsoluteCalculator implements DiscountCalculatorInterface
{
    /**
     * @var AbsolutePriceCalculator
     */
    private $absolutePriceCalculator;

    public function __construct(AbsolutePriceCalculator $absolutePriceCalculator)
    {
        $this->absolutePriceCalculator = $absolutePriceCalculator;
    }

    public function calculate(
        DiscountLineItem $discount,
        PriceCollection $priceCollection,
        SalesChannelContext $context
    ): DiscountCalculatorResult {
        $definition = $discount->getPriceDefinition();

        if (!$definition instanceof AbsolutePriceDefinition) {
            throw new InvalidPriceDefinitionException($discount->getLabel(), $discount->getCode());
        }

        $discountValue      = $definition->getPrice();
        $priceCollectionSum = $priceCollection->sum()->getTotalPrice();

        if (abs($discountValue) > abs($priceCollectionSum)) {
            $discountValue = $priceCollectionSum;
        }

        $discountValue = -abs($discountValue);

        $price = $this->absolutePriceCalculator->calculate(
            $discountValue,
            $priceCollection,
            $context
        );

        // Workaround due to a Shopware bug not respecting currency factor
        if (
            $price->getUnitPrice() === $discountValue
            && Defaults::CURRENCY !== $context->getCurrency()->getId()
        ) {
            $discountValue = $definition->getPrice() * $context->getCurrency()->getFactor();

            if (abs($discountValue) > $priceCollectionSum) {
                $discountValue = $priceCollectionSum;
            }

            $price = $this->absolutePriceCalculator->calculate(
                -abs($discountValue),
                $priceCollection,
                $context
            );
        }

        if ($this->hasMaxValue($discount)) {
            $maxValue = $discount->getPayloadValue('maxValue');
            $maxValue = \is_float($maxValue) ? $maxValue : false;

            if (false !== $maxValue && abs($price->getTotalPrice()) > abs($maxValue)) {
                $price = $this->absolutePriceCalculator->calculate(
                    -abs($maxValue),
                    $priceCollection,
                    $context
                );
            }
        }

        return new DiscountCalculatorResult($price);
    }

    private function hasMaxValue(DiscountLineItem $discount): bool
    {
        try {
            $maxValue = $discount->getPayloadValue('maxValue');
        } catch (PayloadKeyNotFoundException $e) {
            return false;
        }

        if (!\is_float($maxValue)) {
            return false;
        }

        return $maxValue > 0.0;
    }
}
