<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart;

use Shopware\Core\Checkout\Cart\Error\Error;

class EasyCouponError extends Error
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id, string $message)
    {
        $this->id = $id;

        parent::__construct($message);
    }

    public function getParameters(): array
    {
        return [ 'id' => $this->id ];
    }

    public function getId(): string
    {
        return $this->getMessageKey() . $this->id;
    }

    public function getMessageKey(): string
    {
        return 'easy-coupon-error';
    }

    public function getLevel(): int
    {
        return self::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return true;
    }
}
