<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation\EasyCouponProductTranslationCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\TypeContainingEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePriceCollection;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Traits\ValueTypeTrait;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Tax\TaxEntity;

class EasyCouponProductEntity extends TypeContainingEntity
{
    use EntityIdTrait;
    use ValueTypeTrait;

    public const PREFIX_VALUE_TYPE    = 'VALUE_TYPE_';

    public const VALUE_TYPE_FIXED     = 20010;

    public const VALUE_TYPE_RANGE     = 20020;

    public const VALUE_TYPE_SELECTION = 20030;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var string|null
     */
    protected $productId;

    /**
     * @var bool
     */
    protected $postal;

    /**
     * @var ProductValuePriceCollection|null
     */
    protected $value;

    /**
     * @var bool
     */
    protected $shippingCharge;

    /**
     * @var bool
     */
    protected $excludeFromShippingCosts;

    /**
     * @var bool
     */
    protected $noDeliveryCharge;

    /**
     * @var bool
     */
    protected $customerGroupCharge;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var string
     */
    protected $orderPositionNumber;

    /**
     * @var TaxEntity|null
     */
    protected $tax;

    /**
     * @var string|null
     */
    protected $taxId;

    /**
     * @var ?EasyCouponProductTranslationCollection
     */
    protected $translations;

    /**
     * @var bool
     */
    protected $combineVouchers;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var ConditionCollection|null
     */
    protected $conditions;

    /**
     * @var int|null
     */
    protected $validityTime;

    /**
     * @var bool|null
     */
    protected $validityByEndOfYear;

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    public function isPostal(): bool
    {
        return $this->postal;
    }

    public function setPostal(bool $postal): void
    {
        $this->postal = $postal;
    }

    public function getValue(): ?ProductValuePriceCollection
    {
        return $this->value;
    }

    public function setValue(?ProductValuePriceCollection $value): void
    {
        $this->value = $value;
    }

    public function isShippingCharge(): bool
    {
        return $this->shippingCharge;
    }

    public function setShippingCharge(bool $shippingCharge): void
    {
        $this->shippingCharge = $shippingCharge;
    }

    public function isExcludeFromShippingCosts(): bool
    {
        return $this->excludeFromShippingCosts;
    }

    public function setExcludeFromShippingCosts(bool $excludeFromShippingCosts): void
    {
        $this->excludeFromShippingCosts = $excludeFromShippingCosts;
    }

    public function isNoDeliveryCharge(): bool
    {
        return $this->noDeliveryCharge;
    }

    public function setNoDeliveryCharge(bool $noDeliveryCharge): void
    {
        $this->noDeliveryCharge = $noDeliveryCharge;
    }

    public function isCustomerGroupCharge(): bool
    {
        return $this->customerGroupCharge;
    }

    public function setCustomerGroupCharge(bool $customerGroupCharge): void
    {
        $this->customerGroupCharge = $customerGroupCharge;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getOrderPositionNumber(): string
    {
        return $this->orderPositionNumber;
    }

    public function setOrderPositionNumber(string $orderPositionNumber): void
    {
        $this->orderPositionNumber = $orderPositionNumber;
    }

    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    public function setTax(?TaxEntity $tax): void
    {
        $this->tax = $tax;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function setTranslations(EasyCouponProductTranslationCollection $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function getTranslations(): ?EasyCouponProductTranslationCollection
    {
        return $this->translations;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return ConditionCollection|null
     */
    public function getConditions(): ?ConditionCollection
    {
        return $this->conditions;
    }

    /**
     * @param ConditionCollection|null $conditions
     */
    public function setConditions(?ConditionCollection $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @param bool $combineVouchers
     *
     * @return EasyCouponProductEntity
     */
    public function setCombineVouchers(bool $combineVouchers): EasyCouponProductEntity
    {
        $this->combineVouchers = $combineVouchers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCombineVouchers(): bool
    {
        return $this->combineVouchers;
    }

    /**
     * @return int|null
     */
    public function getValidityTime(): ?int
    {
        return $this->validityTime;
    }

    /**
     * @param int|null $validityTime
     */
    public function setValidityTime(?int $validityTime): void
    {
        $this->validityTime = $validityTime;
    }

    /**
     * @return bool
     */
    public function isValidityByEndOfYear(): bool
    {
        return $this->validityByEndOfYear ?? false;
    }

    /**
     * @param bool $validityByEndOfYear
     */
    public function setValidityByEndOfYear(bool $validityByEndOfYear): void
    {
        $this->validityByEndOfYear = $validityByEndOfYear;
    }
}
