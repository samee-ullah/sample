<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EasyCouponCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EasyCouponEntity::class;
    }
}
