<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Extension;

use Shopware\Core\Framework\Struct\Struct;

class CartExtension extends Struct
{
    public const KEY = 'cart-easy-coupons';

    /**
     * @var string[]
     */
    protected $voucherCodes = [];

    public function addVoucherCode(string $code): void
    {
        if ('' === $code) {
            return;
        }

        if (!$this->hasVoucherCode($code)) {
            $this->voucherCodes[] = $code;
        }
    }

    public function hasVoucherCode(string $code): bool
    {
        return \in_array($code, $this->voucherCodes, true);
    }

    public function removeVoucherCode(string $code): void
    {
        if ('' === $code) {
            return;
        }

        if (!$this->hasVoucherCode($code)) {
            return;
        }

        foreach ($this->voucherCodes as $index => $existingCode) {
            if ($existingCode !== $code) {
                continue;
            }

            \array_splice($this->voucherCodes, $index, 1);
        }
    }

    /**
     * @return string[]
     */
    public function getVoucherCodes(): array
    {
        return $this->voucherCodes;
    }

    public function removeCode(string $code): void
    {
        if ('' === $code) {
            return;
        }

        if (!$this->hasVoucherCode($code)) {
            return;
        }

        $newList = [];

        foreach ($this->voucherCodes as $existingCode) {
            if ($existingCode !== $code) {
                $newList[] = $existingCode;
            }
        }

        $this->voucherCodes = $newList;
    }
}
