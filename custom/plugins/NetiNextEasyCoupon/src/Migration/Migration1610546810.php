<?php declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1610546810 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1610546810;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon`
                ADD COLUMN `combine_vouchers` tinyint(1) NOT NULL DEFAULT 1 AFTER `max_redemption_value`;
        ');

        $connection->executeStatement('
            ALTER TABLE `neti_easy_coupon_product`
                ADD COLUMN `combine_vouchers` tinyint(1) NOT NULL DEFAULT 1 AFTER `order_position_number`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
