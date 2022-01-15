<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class NotCombinableError extends Error
{
    private const KEY = 'easy-coupon-not-combine-error';

    public function __construct(string $code)
    {
        $this->code    = $code;
        $this->message = \sprintf(
            'The voucher %s is not allowed to combine with another vouchers',
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
        return true;
    }

    public function getParameters(): array
    {
        return [
            'id' => $this->code,
        ];
    }
}
