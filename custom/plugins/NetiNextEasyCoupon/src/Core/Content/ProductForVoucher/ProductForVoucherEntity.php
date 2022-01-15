<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\ProductForVoucher;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;

class ProductForVoucherEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var ProductEntity
     */
    protected $product;

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var EasyCouponEntity
     */
    protected $easyCoupon;

    /**
     * @var string
     */
    protected $easyCouponId;

    /**
     * @var PriceCollection|null
     */
    protected $additionalPayment;

    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

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

    public function getAdditionalPayment(): ?PriceCollection
    {
        return $this->additionalPayment;
    }

    public function setAdditionalPayment(?PriceCollection $additionalPayment): void
    {
        $this->additionalPayment = $additionalPayment;
    }
}
