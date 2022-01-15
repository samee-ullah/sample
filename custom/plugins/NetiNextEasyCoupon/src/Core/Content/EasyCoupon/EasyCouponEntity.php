<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon;

use NetInventors\NetiNextEasyCoupon\Core\Content\Condition\ConditionCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Aggregate\EasyCouponTranslation\EasyCouponTranslationCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\LegacySetting\LegacySettingEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\ProductForVoucher\ProductForVoucherCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\TypeContainingEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Traits\ValueTypeTrait;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Traits\VoucherTypeTrait;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Version\VersionEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\Tag\TagEntity;
use Shopware\Core\System\Tax\TaxEntity;

class EasyCouponEntity extends TypeContainingEntity
{
    use EntityIdTrait;
    use ValueTypeTrait;
    use VoucherTypeTrait {
        VoucherTypeTrait::validateType insteadof ValueTypeTrait;
    }

    public const PREFIX_VALUE_TYPE        = 'VALUE_TYPE_';

    public const PREFIX_VOUCHER_TYPE      = 'VOUCHER_TYPE_';

    public const VALUE_TYPE_ABSOLUTE      = 10010;

    public const VALUE_TYPE_PERCENTAL     = 10020;

    public const VOUCHER_TYPE_GENERAL     = 40010;

    public const VOUCHER_TYPE_INDIVIDUAL  = 40020;

    public const REDEMPTION_ORDER_INHERIT = 0;

    public const REDEMPTION_ORDER_BEFORE  = 1;

    public const REDEMPTION_ORDER_AFTER   = 2;

    /**
     * @var bool
     */
    protected $deleted;

    /**
     * @var \DateTimeInterface|null
     */
    protected $deletedDate;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var bool
     */
    protected $discardRemaining;

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
     * @var bool
     */
    protected $mailSent;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var string|null
     */
    protected $productId;

    /**
     * @var VersionEntity|null
     */
    protected $productVersion;

    /**
     * @var string|null
     */
    protected $productVersionId;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var CurrencyEntity
     */
    protected $currency;

    /**
     * @var string
     */
    protected $currencyId;

    /**
     * @var float
     */
    protected $currencyFactor;

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
     * @var TagEntity|null
     */
    protected $tag;

    /**
     * @var string|null
     */
    protected $tagId;

    /**
     * @var TransactionCollection
     */
    protected $transactions;

    /**
     * @var PriceCollection|null
     */
    protected $maxRedemptionValue;

    /**
     * @var bool
     */
    protected $combineVouchers;

    /**
     * @var ?ConditionCollection
     */
    protected $conditions;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var EasyCouponTranslationCollection
     */
    protected $translations;

    /**
     * @var ProductForVoucherCollection|null
     */
    protected $productForVoucher;

    /**
     * @var LegacySettingEntity|null
     */
    protected $legacySetting;

    /**
     * @var string|null
     */
    protected $virtualImport;

    /**
     * @var \DateTimeInterface|null
     */
    protected $validUntil;

    protected int $redemptionOrder;

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getDeletedDate(): ?\DateTimeInterface
    {
        return $this->deletedDate;
    }

    public function setDeletedDate(?\DateTimeInterface $deletedDate): void
    {
        $this->deletedDate = $deletedDate;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    public function isDiscardRemaining(): bool
    {
        return $this->discardRemaining;
    }

    public function setDiscardRemaining(bool $discardRemaining): void
    {
        $this->discardRemaining = $discardRemaining;
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

    public function isMailSent(): bool
    {
        return $this->mailSent;
    }

    public function setMailSent(bool $mailSent): void
    {
        $this->mailSent = $mailSent;
    }

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

    public function getProductVersion(): ?VersionEntity
    {
        return $this->productVersion;
    }

    public function setProductVersion(?VersionEntity $productVersion): void
    {
        $this->productVersion = $productVersion;
    }

    public function getProductVersionId(): ?string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(?string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCurrency(): CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    public function setCurrencyFactor(float $currencyFactor): void
    {
        $this->currencyFactor = $currencyFactor;
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

    public function getTag(): ?TagEntity
    {
        return $this->tag;
    }

    public function setTag(?TagEntity $tag): void
    {
        $this->tag = $tag;
    }

    public function getTagId(): ?string
    {
        return $this->tagId;
    }

    public function setTagId(?string $tagId): void
    {
        $this->tagId = $tagId;
    }

    public function setTransactions(TransactionCollection $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }

    public function getTransactions(): TransactionCollection
    {
        return $this->transactions;
    }

    public function getMaxRedemptionValue(): ?PriceCollection
    {
        return $this->maxRedemptionValue;
    }

    public function setMaxRedemptionValue(?PriceCollection $maxRedemptionValue): self
    {
        $this->maxRedemptionValue = $maxRedemptionValue;

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
     * @param bool $combineVouchers
     *
     * @return EasyCouponEntity
     */
    public function setCombineVouchers(bool $combineVouchers): EasyCouponEntity
    {
        $this->combineVouchers = $combineVouchers;

        return $this;
    }

    /**
     * @return ?ConditionCollection
     */
    public function getConditions(): ?ConditionCollection
    {
        return $this->conditions;
    }

    /**
     * @param ?ConditionCollection $conditions
     *
     * @return EasyCouponEntity
     */
    public function setConditions(?ConditionCollection $conditions): self
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTranslations(): EasyCouponTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(EasyCouponTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProductForVoucher(): ?ProductForVoucherCollection
    {
        return $this->productForVoucher;
    }

    public function setProductForVoucher(?ProductForVoucherCollection $productForVoucher): void
    {
        $this->productForVoucher = $productForVoucher;
    }

    public function getLegacySetting(): ?LegacySettingEntity
    {
        return $this->legacySetting;
    }

    public function setLegacySetting(?LegacySettingEntity $legacySetting): void
    {
        $this->legacySetting = $legacySetting;
    }

    /**
     * @return string|null
     */
    public function getVirtualImport(): ?string
    {
        return $this->virtualImport;
    }

    /**
     * @param string|null $virtualImport
     */
    public function setVirtualImport(?string $virtualImport): void
    {
        $this->virtualImport = $virtualImport;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    /**
     * @param \DateTimeInterface|null $validUntil
     */
    public function setValidUntil(?\DateTimeInterface $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    public function setRedemptionOrder(int $redemptionOrder): EasyCouponEntity
    {
        $this->redemptionOrder = $redemptionOrder;

        return $this;
    }

    public function getRedemptionOrder(): int
    {
        return $this->redemptionOrder;
    }
}
