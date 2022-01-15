<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    malte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Condition;

use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;

class ConditionCollection extends RuleConditionCollection
{
    protected function getExpectedClass(): string
    {
        return ConditionEntity::class;
    }
}
