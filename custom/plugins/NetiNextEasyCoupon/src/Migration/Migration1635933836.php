<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1635933836 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1635933836;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
SELECT `mapping` FROM `import_export_profile` WHERE `name` = "EasyCoupon" AND `source_entity` = "neti_easy_coupon"
SQL;

        $result = $connection->executeQuery($sql)->fetchOne();
        $result = json_decode($result, true);

        if (null === $result) {
            throw new \Exception('Could not update the Import/Export profile for EasyCoupon');
        }

        foreach ($result as &$item) {
            if ($item['key'] === 'virtualImport') {
                $item['mappedKey'] = 'virtual_import';
            }
        }

        $result = json_encode($result);

        $sql    = <<<SQL
UPDATE `import_export_profile` SET `mapping` = ? WHERE `name` = "EasyCoupon"
SQL;

        $connection->executeStatement($sql, [ $result ]);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
