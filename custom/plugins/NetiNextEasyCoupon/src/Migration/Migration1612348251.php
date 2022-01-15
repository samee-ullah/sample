<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Rule\DateRangeRule;

class Migration1612348251 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612348251;
    }

    public function update(Connection $connection): void
    {
        $sql = '
            UPDATE neti_easy_coupon_condition
            SET type = ?
            WHERE type = ?
        ';

        $connection->executeStatement(
            $sql,
            [
                (new \NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule\DateRangeRule())->getName(),
                (new DateRangeRule())->getName()
            ]
        );

        $sql = '
            UPDATE neti_easy_coupon_product_condition
            SET type = ?
            WHERE type = ?
        ';

        $connection->executeStatement(
            $sql,
            [
                (new \NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule\DateRangeRule())->getName(),
                (new DateRangeRule())->getName()
            ]
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
