<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1627480654 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1627480654;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon_transaction`
                ADD COLUMN `order_line_item_id` BINARY(16) NULL AFTER `sales_channel_id`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
