<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    malte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Condition;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponDefinition;
use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ConditionDefinition extends RuleConditionDefinition
{
    public const ENTITY_NAME = 'neti_easy_coupon_condition';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ConditionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ConditionCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return null;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('type', 'type'),
            (new FkField('coupon_id', 'couponId', EasyCouponDefinition::class))->addFlags(new Required()),
            new ParentFkField(self::class),
            new JsonField('value', 'value'),
            new IntField('position', 'position'),
            new ParentAssociationField(self::class, 'id'),
            new ChildrenAssociationField(self::class),

            new UpdatedAtField(),
            new CreatedAtField(),
        ]);
    }
}
