<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\LegacySetting;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class LegacySettingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon_legacy_setting';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return LegacySettingCollection::class;
    }

    public function getEntityClass(): string
    {
        return LegacySettingEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

                (new FkField('easy_coupon_id', 'easyCouponId', EasyCouponDefinition::class))->addFlags(new Required()),
                (new OneToOneAssociationField(
                    'easyCoupon', 'easy_coupon_id', 'id', EasyCouponDefinition::class
                )),

                (new JsonField('legacy_setting', 'legacySetting'))->addFlags(new Required()),

                new UpdatedAtField(),
                new CreatedAtField(),

            ]
        );
    }
}
