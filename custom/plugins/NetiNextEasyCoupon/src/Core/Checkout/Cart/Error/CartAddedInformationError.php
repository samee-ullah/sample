<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;

class CartAddedInformationError extends Error
{
    private const KEY = 'easy-coupon-discount-added';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $discountLineItemId;

    public function __construct(LineItem $discountLineItem)
    {
        $this->name               = $discountLineItem->getLabel() ?? '';
        $this->discountLineItemId = $discountLineItem->getId();
        $this->message            = \sprintf(
            'Discount %s has been added',
            $this->name
        );

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getParameters(): array
    {
        return [
            'name'               => $this->name,
            'discountLineItemId' => $this->discountLineItemId,
        ];
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->discountLineItemId);
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function blockOrder(): bool
    {
        return false;
    }
}
