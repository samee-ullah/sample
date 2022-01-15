<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Struct;

use Shopware\Core\Framework\Struct\Struct;

class LineItemStruct extends Struct
{
    public const NAME = 'neti-next-easy-coupon';

    public const PAYLOAD_NAME = 'netiNextEasyCoupon';

    /**
     * @var float
     */
    protected $voucherValue;

    public function getVoucherValue(): float
    {
        return $this->voucherValue;
    }
}
