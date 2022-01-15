<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Aggregate\EasyCouponTranslation;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class EasyCouponTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $netiEasyCouponId;

    /**
     * @var EasyCouponEntity
     */
    protected $netiEasyCoupon;

    /**
     * @var string
     */
    protected $title;

    public function setNetiEasyCouponId(string $netiEasyCouponId): self
    {
        $this->netiEasyCouponId = $netiEasyCouponId;

        return $this;
    }

    public function getNetiEasyCouponId(): string
    {
        return $this->netiEasyCouponId;
    }

    public function setNetiEasyCoupon(EasyCouponEntity $netiEasyCoupon): self
    {
        $this->netiEasyCoupon = $netiEasyCoupon;

        return $this;
    }

    public function getNetiEasyCoupon(): EasyCouponEntity
    {
        return $this->netiEasyCoupon;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
