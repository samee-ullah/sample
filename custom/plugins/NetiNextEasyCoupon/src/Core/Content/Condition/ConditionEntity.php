<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    malte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Condition;

use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionEntity;

class ConditionEntity extends RuleConditionEntity
{
    /**
     * @var string
     */
    protected $couponId;

    public function getCouponId(): string
    {
        return $this->couponId;
    }

    public function setCouponId(string $couponId): void
    {
        $this->couponId = $couponId;
    }
}
