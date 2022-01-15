<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ValidationContext
{
    /**
     * @var EasyCouponEntity
     */
    private $easyCoupon;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    public function __construct(
        EasyCouponEntity $easyCoupon,
        Cart $cart,
        SalesChannelContext $salesChannelContext
    ) {
        $this->easyCoupon          = $easyCoupon;
        $this->cart                = $cart;
        $this->salesChannelContext = $salesChannelContext;
    }

    public function getEasyCoupon(): EasyCouponEntity
    {
        return $this->easyCoupon;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
