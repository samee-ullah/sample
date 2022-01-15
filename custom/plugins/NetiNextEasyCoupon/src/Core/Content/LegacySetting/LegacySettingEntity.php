<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\LegacySetting;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LegacySettingEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var EasyCouponEntity
     */
    protected $easyCoupon;

    /**
     * @var string
     */
    protected $easyCouponId;

    /**
     * @var mixed
     */
    protected $legacySetting;

    public function getEasyCoupon(): EasyCouponEntity
    {
        return $this->easyCoupon;
    }

    public function setEasyCoupon(EasyCouponEntity $easyCoupon): void
    {
        $this->easyCoupon = $easyCoupon;
    }

    public function getEasyCouponId(): string
    {
        return $this->easyCouponId;
    }

    public function setEasyCouponId(string $easyCouponId): void
    {
        $this->easyCouponId = $easyCouponId;
    }

    /**
     * @return mixed
     */
    public function getLegacySetting()
    {
        return $this->legacySetting;
    }

    /**
     * @param mixed $legacySetting
     */
    public function setLegacySetting($legacySetting): void
    {
        $this->legacySetting = $legacySetting;
    }
}
