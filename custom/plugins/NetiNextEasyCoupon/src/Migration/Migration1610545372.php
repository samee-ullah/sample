<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1610545372 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1610545372;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `neti_easy_coupon_product_condition` (
                `id` BINARY(16) NOT NULL,
                `type` VARCHAR(255) NULL,
                `coupon_id` BINARY(16) NOT NULL,
                `parent_id` BINARY(16) NULL,
                `value` JSON NULL,
                `position` INT(11) NULL,
                `updated_at` DATETIME(3) NULL,
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `json.neti_easy_coupon_product_condition.value` CHECK (JSON_VALID(`value`)),
                KEY `fk.neti_easy_coupon_product_condition.parent_id` (`parent_id`),
                CONSTRAINT `fk.neti_easy_coupon_product_condition.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `neti_easy_coupon_product_condition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
