<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;

class PurchaseVoucherWithoutValueError extends Error
{
    protected const KEY = 'easy-coupon-purchase-voucher-without-value';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $lineItemId;

    public function __construct(LineItem $lineItem)
    {
        $this->lineItemId = $lineItem->getId();
        $this->name       = $lineItem->getLabel() ?? '';
        $this->message    = \sprintf(
            'Purchase voucher %s has added to basket via detail page only',
            $this->name
        );

        parent::__construct($this->name);
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->lineItemId);
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
            'name' => $this->name,
        ];
    }
}
