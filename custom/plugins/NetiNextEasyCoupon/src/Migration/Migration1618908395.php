<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1618908395 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1618908395;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon`
                ADD COLUMN `valid_until` DATETIME(3) NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
