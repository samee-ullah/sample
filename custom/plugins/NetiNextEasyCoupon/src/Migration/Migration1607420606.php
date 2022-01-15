<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1607420606 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1607420606;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        $sql = '
            ALTER TABLE `neti_easy_coupon`         CHANGE `tax_id` `tax_id` binary(16) NULL AFTER `product_version_id`;
            ALTER TABLE `neti_easy_coupon_product` CHANGE `tax_id` `tax_id` binary(16) NULL AFTER `product_version_id`;
        ';

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
