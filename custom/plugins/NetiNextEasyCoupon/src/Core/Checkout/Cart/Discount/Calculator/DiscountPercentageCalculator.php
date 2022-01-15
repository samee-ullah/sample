<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\Calculator;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountCalculatorInterface;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountCalculatorResult;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount\DiscountLineItem;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\InvalidPriceDefinitionException;
use Shopware\Core\Checkout\Cart\Exception\PayloadKeyNotFoundException;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountPercentageCalculator implements DiscountCalculatorInterface
{
    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    /**
     * @var AbsolutePriceCalculator
     */
    private $absolutePriceCalculator;

    public function __construct(
        PercentagePriceCalculator $percentagePriceCalculator,
        AbsolutePriceCalculator $absolutePriceCalculator
    ) {
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->absolutePriceCalculator   = $absolutePriceCalculator;
    }

    public function calculate(
        DiscountLineItem $discount,
        PriceCollection $priceCollection,
        SalesChannelContext $context
    ): DiscountCalculatorResult {
        $definition = $discount->getPriceDefinition();

        if (!$definition instanceof PercentagePriceDefinition) {
            throw new InvalidPriceDefinitionException($discount->getLabel(), $discount->getCode());
        }

        $definedPercentage = -abs($definition->getPercentage());

        $calculatedPrice = $this->percentagePriceCalculator->calculate(
            $definedPercentage,
            $priceCollection,
            $context
        );

        if ($this->hasMaxValue($discount)) {
            $maxValue            = $discount->getPayloadValue('maxValue');
            $maxValue            = \is_float($maxValue) ? $maxValue : false;
            $actualDiscountPrice = $calculatedPrice->getTotalPrice();

            if (false !== $maxValue && abs($actualDiscountPrice) > abs($maxValue)) {
                $calculatedPrice = $this->absolutePriceCalculator->calculate(
                    -abs($maxValue),
                    $priceCollection,
                    $context
                );
            }
        }

        return new DiscountCalculatorResult($calculatedPrice);
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
