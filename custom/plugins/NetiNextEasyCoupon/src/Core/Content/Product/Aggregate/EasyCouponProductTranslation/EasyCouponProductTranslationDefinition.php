<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EasyCouponProductTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = EasyCouponProductDefinition::ENTITY_NAME . '_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EasyCouponProductTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return EasyCouponProductTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return EasyCouponProductDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('title', 'title'))->addFlags(new Required()),

            new UpdatedAtField(),
            new CreatedAtField(),
        ]);
    }
}
