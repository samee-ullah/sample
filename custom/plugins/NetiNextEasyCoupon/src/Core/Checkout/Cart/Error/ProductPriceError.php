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

class ProductPriceError extends Error
{
    protected const KEY = 'easy-coupon-product-price-out-of-range';

    /**
     * @var string
     */
    protected $lineItemId;

    /**
     * @var string
     */
    protected $name;

    public function __construct(LineItem $lineItem)
    {
        $this->lineItemId = $lineItem->getId();
        $this->name       = $lineItem->getLabel() ?? '';
        $this->message    = \sprintf('The product price of %s is out of range', $this->name);

        parent::__construct($this->message);
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
        return self::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    public function getParameters(): array
    {
        return [ 'name' => $this->name ];
    }
}
