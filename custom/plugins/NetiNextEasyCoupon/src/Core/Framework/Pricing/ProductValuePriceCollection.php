<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\Currency\CurrencyEntity;

class ProductValuePriceCollection extends PriceCollection
{
    public function getApiAlias(): string
    {
        return 'product_value_price_collection';
    }

    public function getCurrencyPrice(string $currencyId, bool $fallback = true, ?Context $context = null): ?Price
    {
        $price = parent::getCurrencyPrice($currencyId, $fallback);

        if (
            $price instanceof ProductValuePrice
            && $context instanceof Context
            && Defaults::CURRENCY !== $currencyId
            && Defaults::CURRENCY === $price->getCurrencyId()
        ) {
            $currency = new CurrencyEntity();
            $currency->setId($context->getCurrencyId());
            $currency->setFactor($context->getCurrencyFactor());
            $currency->setItemRounding($context->getRounding());

            $price = $price->transformToFactor($currency);
        }

        return $price;
    }

    protected function getExpectedClass(): ?string
    {
        return ProductValuePrice::class;
    }
}
