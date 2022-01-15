<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EasyCouponProductCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EasyCouponProductEntity::class;
    }
}
