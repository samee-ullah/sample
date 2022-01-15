<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Transaction;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\User\UserDefinition;

class TransactionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon_transaction';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TransactionCollection::class;
    }

    public function getEntityClass(): string
    {
        return TransactionEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                (new FloatField('value', 'value'))->addFlags(new Required()),
                (new StringField('intern_comment', 'internComment')),
                (new FloatField('currency_factor', 'currencyFactor'))->addFlags(new Required()),
                (new IntField('transaction_type', 'transactionType'))->addFlags(new Required()),

                (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->addFlags(new Required()),
                (new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class)),

                (new FkField('easy_coupon_id', 'easyCouponId', EasyCouponDefinition::class))->addFlags(new Required()),
                (new ManyToOneAssociationField(
                    'easyCoupon', 'easy_coupon_id', EasyCouponDefinition::class
                )),

                new FkField('customer_id', 'customerId', CustomerDefinition::class),
                (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id'))
                    ->addFlags(new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),

                new FkField('order_id', 'orderId', OrderDefinition::class),
                (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id'))
                    ->addFlags(new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
                new ReferenceVersionField(OrderDefinition::class, 'order_version_id'),

                new FkField('user_id', 'userId', UserDefinition::class),
                (new ManyToOneAssociationField('user', 'user_id', UserDefinition::class, 'id')),

                new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class),
                (new ManyToOneAssociationField(
                    'salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id'
                )),

                (new FkField('order_line_item_id', 'orderLineItemId', OrderLineItemDefinition::class)),
                (new OneToOneAssociationField(
                    'orderLineItem',
                    'order_line_item_id',
                    'id',
                    OrderLineItemDefinition::class
                )),
            ]
        );
    }
}
