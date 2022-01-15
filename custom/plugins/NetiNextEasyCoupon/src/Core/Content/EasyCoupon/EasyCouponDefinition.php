<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon;

use NetInventors\NetiNextEasyCoupon\Core\Content\Condition\ConditionDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Aggregate\EasyCouponTranslation\EasyCouponTranslationDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\LegacySetting\LegacySettingDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\ProductForVoucher\ProductForVoucherDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\Tag\TagDefinition;
use Shopware\Core\System\Tax\TaxDefinition;

class EasyCouponDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EasyCouponCollection::class;
    }

    public function getEntityClass(): string
    {
        return EasyCouponEntity::class;
    }

    public function getDefaults(): array
    {
        $defaults = parent::getDefaults();

        $defaults['redemptionOrder'] = EasyCouponEntity::REDEMPTION_ORDER_INHERIT;

        return $defaults;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

                new BoolField('deleted', 'deleted'),
                new DateTimeField('deleted_date', 'deletedDate'),

                (new BoolField('active', 'active'))->addFlags(new Required()),
                (new IntField('voucher_type', 'voucherType'))->addFlags(new Required()),
                (new StringField('code', 'code'))->addFlags(
                    new Required(),
                    new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)
                ),
                (new FloatField('value', 'value'))->addFlags(new Required()),
                (new BoolField('discard_remaining', 'discardRemaining'))->addFlags(new Required()),
                (new BoolField('shipping_charge', 'shippingCharge'))->addFlags(new Required()),
                (new BoolField('exclude_from_shipping_costs', 'excludeFromShippingCosts'))->addFlags(new Required()),
                (new BoolField('no_delivery_charge', 'noDeliveryCharge'))->addFlags(new Required()),
                (new BoolField('customer_group_charge', 'customerGroupCharge'))->addFlags(new Required()),
                (new BoolField('mail_sent', 'mailSent'))->addFlags(new Required()),
                new StringField('comment', 'comment'),
                (new FloatField('currency_factor', 'currencyFactor'))->addFlags(new Required()),
                (new StringField('order_position_number', 'orderPositionNumber'))->addFlags(new Required()),
                (new IntField('value_type', 'valueType'))->addFlags(new Required()),
                (new PriceField('max_redemption_value', 'maxRedemptionValue'))
                    ->addFlags(new ApiAware(SalesChannelApiSource::class, AdminApiSource::class)),
                (new BoolField('combine_vouchers', 'combineVouchers'))->addFlags(new Required()),

                new FkField('tag_id', 'tagId', TagDefinition::class),
                (new ManyToOneAssociationField('tag', 'tag_id', TagDefinition::class, 'id')),

                (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->addFlags(new Required()),
                (new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class)),

                (new FkField('product_id', 'productId', ProductDefinition::class)),
                (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id')),
                new ReferenceVersionField(ProductDefinition::class, 'product_version_id'),

                (new FkField('tax_id', 'taxId', TaxDefinition::class)),
                (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class, 'id')),

                (new OneToManyAssociationField('transactions', TransactionDefinition::class, 'easy_coupon_id'))
                    ->addFlags(new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),

                (new OneToManyAssociationField('conditions', ConditionDefinition::class, 'coupon_id', 'id'))
                    ->addFlags(new CascadeDelete()),

                (new OneToOneAssociationField('productForVoucher', 'id', 'easy_coupon_id', ProductForVoucherDefinition::class, false)),

                (new OneToOneAssociationField('legacySetting', 'id', 'easy_coupon_id', LegacySettingDefinition::class, false)),

                (new TranslatedField('title'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
                (new TranslationsAssociationField(EasyCouponTranslationDefinition::class, 'neti_easy_coupon_id'))
                    ->addFlags(new Required()),

                new StringField('virtual_import', 'virtualImport'),
                new DateTimeField('valid_until', 'validUntil'),
                (new IntField('redemption_order', 'redemptionOrder'))->addFlags(new Required()),

                new UpdatedAtField(),
                new CreatedAtField(),
            ]
        );
    }
}
