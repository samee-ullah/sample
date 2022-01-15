<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Framework\Struct\Struct;

class CartDataDefinition extends Struct
{
    /**
     * @var EasyCouponEntity[]
     */
    protected $easyCoupons = [];

    public function addEasyCoupon(string $code, EasyCouponEntity $easyCoupon): void
    {
        if ('' === $code) {
            return;
        }

        $this->easyCoupons[$code] = $easyCoupon;
    }

    public function hasEasyCoupon(string $code): bool
    {
        return \array_key_exists($code, $this->easyCoupons);
    }

    public function removeEasyCoupon(string $code): void
    {
        if ('' === $code) {
            return;
        }

        if (!$this->hasEasyCoupon($code)) {
            return;
        }

        unset($this->easyCoupons[$code]);
    }

    /**
     * @return array<EasyCouponEntity>
     */
    public function getEasyCoupons(): array
    {
        return $this->easyCoupons;
    }
}
