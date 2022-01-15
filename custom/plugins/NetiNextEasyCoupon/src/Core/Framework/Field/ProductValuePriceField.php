<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Field;

use NetInventors\NetiNextEasyCoupon\Core\Framework\FieldSerializer\ProductValuePriceFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;

class ProductValuePriceField extends PriceField
{
    public function getSerializerClass(): string
    {
        return ProductValuePriceFieldSerializer::class;
    }
}
