<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\FieldSerializer;

use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePrice;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\PriceFieldSerializer;

class ProductValuePriceFieldSerializer extends PriceFieldSerializer
{
    /**
     * @param Field $field
     * @param mixed $value
     *
     * @return ProductValuePriceCollection|null
     */
    public function decode(Field $field, $value): ?ProductValuePriceCollection
    {
        if (null === $value) {
            return null;
        }
        $value = json_decode($value, true);

        $prices = [];
        foreach ($value as $row) {
            $price = new ProductValuePrice(
                $row['currencyId'],
                (float) $row['net'],
                (float) $row['gross'],
                (bool) $row['linked'],
                null,
                (float) $row['from'],
                (float) $row['to'],
                $row['selectableValues']
            );

            if (isset($row['listPrice']['gross'])) {
                $listPrice = $row['listPrice'];

                $price->setListPrice(
                    new ProductValuePrice(
                        $row['currencyId'],
                        (float) $listPrice['net'],
                        (float) $listPrice['gross'],
                        (bool) $listPrice['linked'],
                        null,
                        (float) $row['from'],
                        (float) $row['to'],
                        $row['selectableValues']
                    )
                );
            }

            $prices[] = $price;
        }

        return new ProductValuePriceCollection($prices);
    }
}
