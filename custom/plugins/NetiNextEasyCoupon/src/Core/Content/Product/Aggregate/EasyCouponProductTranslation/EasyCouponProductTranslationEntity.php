<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class EasyCouponProductTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $netiEasyCouponProductId;

    /**
     * @var EasyCouponProductEntity
     */
    protected $netiEasyCouponProduct;

    /**
     * @var string
     */
    protected $title;

    public function setNetiEasyCouponProductId(string $netiEasyCouponProductId): self
    {
        $this->netiEasyCouponProductId = $netiEasyCouponProductId;

        return $this;
    }

    public function getNetiEasyCouponProductId(): string
    {
        return $this->netiEasyCouponProductId;
    }

    public function setNetiEasyCouponProduct(EasyCouponProductEntity $netiEasyCouponProduct
    ): self {
        $this->netiEasyCouponProduct = $netiEasyCouponProduct;

        return $this;
    }

    public function getNetiEasyCouponProduct(): EasyCouponProductEntity
    {
        return $this->netiEasyCouponProduct;
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
