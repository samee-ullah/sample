<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class AlreadyRedeemedError extends Error
{
    private const KEY = 'easy-coupon-already-redeemed-error';

    /**
     * @var string
     */
    protected $code;

    public function __construct(string $code)
    {
        $this->code    = $code;
        $this->message = \sprintf(
            'The voucher with the code "%s" was already redeemed',
            $this->code
        );

        parent::__construct($this->message);
    }

    public function getId(): string
    {
        return $this->code;
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
        return [
            'id' => $this->code,
        ];
    }
}
