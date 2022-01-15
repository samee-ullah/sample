<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation\EasyCouponProductTranslationDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Field\ProductValuePriceField;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tax\TaxDefinition;

class EasyCouponProductDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EasyCouponProductCollection::class;
    }

    public function getEntityClass(): string
    {
        return EasyCouponProductEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                (new BoolField('postal', 'postal'))->addFlags(new Required()),
                (new ProductValuePriceField('value', 'value'))->addFlags(new Required(), new ApiAware(AdminApiSource::class, SalesChannelApiSource::class)),
                (new BoolField('shipping_charge', 'shippingCharge'))->addFlags(new Required()),
                (new BoolField('exclude_from_shipping_costs', 'excludeFromShippingCosts'))->addFlags(new Required()),
                (new BoolField('no_delivery_charge', 'noDeliveryCharge'))->addFlags(new Required()),
                (new BoolField('customer_group_charge', 'customerGroupCharge'))->addFlags(new Required()),
                new StringField('comment', 'comment'),
                (new StringField('order_position_number', 'orderPositionNumber'))->addFlags(new Required()),
                (new IntField('value_type', 'valueType'))->addFlags(new Required()),
                (new BoolField('combine_vouchers', 'combineVouchers'))->addFlags(new Required()),

                (new IntField('validity_time', 'validityTime')),
                (new BoolField('validity_by_end_of_year', 'validityByEndOfYear')),

                new FkField('product_id', 'productId', ProductDefinition::class),
                new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id'),
                new ReferenceVersionField(ProductDefinition::class, 'product_version_id'),

                (new FkField('tax_id', 'taxId', TaxDefinition::class)),
                (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class, 'id')),

                (new OneToManyAssociationField('conditions', ConditionDefinition::class, 'coupon_id', 'id'))
                    ->addFlags(new CascadeDelete()),

                new TranslatedField('title'),
                (new TranslationsAssociationField(EasyCouponProductTranslationDefinition::class, 'neti_easy_coupon_product_id'))->addFlags(
                    new Required()
                ),

                new UpdatedAtField(),
                new CreatedAtField(),
            ]
        );
    }
}
