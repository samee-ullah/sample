<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;

class CartProcessorAfterPromotions extends AbstractCartProcessor
{
    protected int $redemptionOrder = EasyCouponEntity::REDEMPTION_ORDER_AFTER;
}
