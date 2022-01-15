<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1605561698 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1605561698;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        if (false === $this->hasFreeTaxRate($connection)) {
            $taxId = Uuid::randomBytes();
            $sql   = '
                INSERT INTO tax (id, tax_rate, name, custom_fields, created_at, updated_at)
                VALUES (:id, :value, :name, NULL, NOW(), NULL);
            ';

            $connection->executeStatement(
                $sql,
                [
                    'id'    => $taxId,
                    'value' => 0,
                    'name'  => 'Steuerfrei / Tax free',
                ]
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param Connection $connection
     *
     * @return bool
     * @throws DBALException
     */
    protected function hasFreeTaxRate(Connection $connection): bool
    {
        $sql = '
            SELECT id
            FROM tax
            WHERE tax_rate = 0
        ';

        $id = (string) $connection->fetchColumn($sql);

        return '' !== $id;
    }
}
