<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EasyCouponRuleScope extends CartRuleScope
{
    /**
     * @var EasyCouponEntity
     */
    private $easyCoupon;

    public function __construct(EasyCouponEntity $easyCoupon, Cart $cart, SalesChannelContext $context)
    {
        parent::__construct($cart, $context);

        $this->easyCoupon = $easyCoupon;
    }

    public function getEasyCoupon(): EasyCouponEntity
    {
        return $this->easyCoupon;
    }
}
