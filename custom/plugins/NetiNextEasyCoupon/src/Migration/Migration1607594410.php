<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1607594410 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1607594410;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon_product_for_voucher`
                ADD COLUMN `product_version_id` BINARY NOT NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
