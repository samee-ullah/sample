<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Events\BusinessEvent;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\ArrayType;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\MailActionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CouponActivationEvent extends Event implements MailActionInterface
{
    public const NAME = 'neti_easy_coupon.coupon.activate';

    protected Context     $context;

    protected OrderEntity $order;

    /**
     * @var EasyCouponEntity[]
     */
    protected array             $vouchers;

    protected VoucherCollection $voucherCodes;

    public function __construct(
        Context           $context,
        OrderEntity       $order,
        array             $vouchers,
        VoucherCollection $voucherCodes
    ) {
        $this->context      = $context;
        $this->order        = $order;
        $this->vouchers     = $vouchers;
        $this->voucherCodes = $voucherCodes;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())->add(
            'order',
            new EntityType(OrderEntity::class)
        )->add(
            'vouchers',
            new ArrayType(new EntityType(EasyCouponEntity::class))
        )->add(
            'voucherCodes',
            new ArrayType(new ScalarValueType(ScalarValueType::TYPE_STRING))
        )->add(
            'customer',
            new EntityType(OrderCustomerEntity::class)
        );
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        return new MailRecipientStruct([
            $this->order->getOrderCustomer()->getEmail() =>
                $this->order->getOrderCustomer()->getFirstName()
                . ' '
                . $this->order->getOrderCustomer()->getLastName(),
        ]);
    }

    public function getSalesChannelId(): ?string
    {
        return $this->order->getSalesChannelId();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getVouchers(): array
    {
        return $this->vouchers;
    }

    public function getVoucherCodes(): VoucherCollection
    {
        return $this->voucherCodes;
    }

    public function getCustomer(): OrderCustomerEntity
    {
        return $this->order->getOrderCustomer();
    }
}
