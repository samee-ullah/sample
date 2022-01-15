<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;

class DiscountCalculatorResult
{
    /**
     * @var CalculatedPrice
     */
    private $price;

    public function __construct(CalculatedPrice $price)
    {
        $this->price = $price;
    }

    public function getPrice(): CalculatedPrice
    {
        return $this->price;
    }
}
