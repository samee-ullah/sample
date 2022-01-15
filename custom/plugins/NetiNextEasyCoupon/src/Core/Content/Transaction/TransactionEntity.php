<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Transaction;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\TypeContainingEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Traits\TransactionTypeTrait;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\User\UserEntity;

class TransactionEntity extends TypeContainingEntity
{
    use EntityIdTrait;
    use TransactionTypeTrait;

    public const PREFIX_TRANSACTION_TYPE                 = 'TYPE_';

    public const TYPE_CREATED_BY_PURCHASE                = 30010;

    public const TYPE_CREATED_BY_ADMIN                   = 30020;

    public const TYPE_CREATED_BY_IMPORT                  = 30030;

    public const TYPE_CREATED_BY_API                     = 30040;

    public const TYPE_REDEMPTION_IN_STOREFRONT           = 30110;

    public const TYPE_REDEMPTION_BY_ADMIN                = 30120;

    public const TYPE_REDEMPTION_BY_API                  = 30130;

    public const TYPE_DEBIT_BY_ADMIN                     = 30210;

    public const TYPE_CREDIT_BY_ADMIN                    = 30310;

    /**
     * @var EasyCouponEntity
     */
    protected $easyCoupon;

    /**
     * @var string
     */
    protected $easyCouponId;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var string|null
     */
    protected $customerId;

    /**
     * @var OrderEntity|null
     */
    protected $order;

    /**
     * @var string|null
     */
    protected $orderId;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $internComment;

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
     * @var UserEntity|null
     */
    protected $user;

    /**
     * @var string|null
     */
    protected $userId;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var string|null
     */
    protected $orderLineItemId;

    /**
     * @var OrderLineItemEntity|null
     */
    protected $orderLineItem;

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

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    public function getInternComment(): ?string
    {
        return $this->internComment;
    }

    public function setInternComment(?string $internComment): void
    {
        $this->internComment = $internComment;
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

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(?UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getOrderLineItemId(): ?string
    {
        return $this->orderLineItemId;
    }

    public function setOrderLineItemId(?string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    public function getOrderLineItem(): ?OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    public function setOrderLineItem(?OrderLineItemEntity $orderLineItem): void
    {
        $this->orderLineItem = $orderLineItem;
    }
}
