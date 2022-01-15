<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1612349225AddImportColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612349225;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon`
                ADD COLUMN `virtual_import` VARCHAR(255) NULL AFTER `tax_id`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
