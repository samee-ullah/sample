<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EasyCouponProductTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EasyCouponProductTranslationEntity::class;
    }
}
