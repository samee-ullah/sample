<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\ProductForVoucher;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductForVoucherDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon_product_for_voucher';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductForVoucherCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductForVoucherEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

                (new FkField('easy_coupon_id', 'easyCouponId', EasyCouponDefinition::class))->addFlags(new Required()),
                (new OneToOneAssociationField(
                    'easyCoupon', 'easy_coupon_id', 'id', EasyCouponDefinition::class, false
                )),

                (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
                (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id')),
                (new ReferenceVersionField(ProductDefinition::class, 'product_version_id'))->addFlags(new Required()),

                (new PriceField('additional_payment', 'additionalPayment'))->addFlags(new ApiAware(SalesChannelApiSource::class)),

                new UpdatedAtField(),
                new CreatedAtField(),
            ]
        );
    }
}
