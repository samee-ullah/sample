<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1622727012 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1622727012;
    }

    /**
     * @param Connection $connection
     *
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement('
ALTER TABLE `neti_easy_coupon`
    ADD COLUMN `redemption_order` INT(11) NOT NULL DEFAULT \'0\'
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
