<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class EmptyCartError extends Error
{
    public const KEY = 'easy-coupon-empty-cart-error';

    public function __construct()
    {
        $this->message = 'The cart is empty';

        parent::__construct($this->message);
    }

    public function getId(): string
    {
        return 'unknown';
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_ERROR;
    }

    public function blockOrder(): bool
    {
        return false;
    }

    public function getParameters(): array
    {
        return [];
    }
}
