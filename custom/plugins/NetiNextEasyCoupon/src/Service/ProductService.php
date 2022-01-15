<?php
/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePrice;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class ProductService
{
    /**
     * @param SalesChannelProductEntity[] $salesChannelProducts
     * @param string $currencyId
     */
    public function setPriceRanges(array $salesChannelProducts, string $currencyId): void
    {
        foreach ($salesChannelProducts as $product) {
            $attribute = $product->getExtension('netiEasyCouponProduct');
            if (!$attribute instanceof EasyCouponProductEntity) {
                continue;
            }

            $valueType       = $attribute->getValueType();
            $isRangeType     = EasyCouponProductEntity::VALUE_TYPE_RANGE === $valueType;
            $isSelectionType = EasyCouponProductEntity::VALUE_TYPE_SELECTION === $valueType;
            if (!($isSelectionType || $isRangeType)) {
                continue;
            }

            $calculatedPrice = $product->getCalculatedPrice();

            if (!$attribute->getValue() instanceof ProductValuePriceCollection) {
                return;
            }

            /** @var ProductValuePrice $price */
            $price               = $attribute->getValue()->getCurrencyPrice($currencyId);
            $fromPrice           = $isRangeType ? (float) $price->getFrom() : $price->getMinSelectableValue();
            $toPrice             = $isRangeType ? (float) $price->getTo() : $price->getMaxSelectableValue();
            $fromCalculatedPrice = new CalculatedCheapestPrice(
                $fromPrice,
                $fromPrice,
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules()
            );
            $toCalculatedPrice   = new CalculatedPrice(
                $toPrice,
                $toPrice,
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules()
            );

            $product->getCalculatedPrices()->clear();
            $product->setCalculatedPrices(new PriceCollection([ $fromCalculatedPrice, $fromCalculatedPrice ]));
            $product->setCalculatedCheapestPrice($fromCalculatedPrice);
        }
    }
}
