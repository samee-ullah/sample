<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\ProductForVoucher;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductForVoucherCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductForVoucherEntity::class;
    }
}
