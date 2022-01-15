<?php

declare(strict_types=1);

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     sbrueggenolte
 */

namespace NetInventors\NetiNextEasyCoupon\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

abstract class TypeContainingEntity extends Entity
{
    public static function getValidTypes(string $constantPrefix): array
    {
        $validTypes = [];
        $reflection = new \ReflectionClass(static::class);
        $constants  = $reflection->getConstants() ?? [];

        foreach ($constants as $name => $value) {
            if (0 !== \strpos($name, $constantPrefix)) {
                continue;
            }

            $validTypes[] = $value;
        }

        return $validTypes;
    }
}
