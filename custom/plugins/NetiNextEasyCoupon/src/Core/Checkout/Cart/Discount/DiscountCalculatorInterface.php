<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount;

use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface DiscountCalculatorInterface
{
    public function calculate(
        DiscountLineItem $discount,
        PriceCollection $priceCollection,
        SalesChannelContext $context
    ): DiscountCalculatorResult;
}
