<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtensionDefinition extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                'netiEasyCouponProduct',
                'id',
                'product_id',
                EasyCouponProductDefinition::class
            ))->addFlags(new CascadeDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
