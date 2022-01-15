<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Aggregate\EasyCouponTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EasyCouponTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EasyCouponTranslationEntity::class;
    }
}
