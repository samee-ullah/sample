<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\LegacySetting;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class LegacySettingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LegacySettingEntity::class;
    }
}
