<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1618477128 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1618477128;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon_product`
                ADD `validity_time` int(11) NULL DEFAULT 0,
                ADD `validity_by_end_of_year` tinyint(1) NULL DEFAULT 0 AFTER `validity_time`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
