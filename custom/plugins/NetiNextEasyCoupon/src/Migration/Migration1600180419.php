<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1600180419 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1600180419;
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        $this->createEasyCouponTable($connection);
        $this->createEasyCouponTransactionTable($connection);
        $this->createLegacySettingTable($connection);
        $this->createProductForVoucherTable($connection);
        $this->createEasyCouponProductTable($connection);
        $this->createEasyCouponTranslationTable($connection);
        $this->createEasyCouponProductTranslationTable($connection);
        $this->createConditionTable($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createEasyCouponTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon` (
    `id` BINARY(16) NOT NULL,
    `deleted` TINYINT(1) NOT NULL DEFAULT '0',
    `deleted_date` DATETIME(3) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT '0',
    `voucher_type` INT(11) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `value` DOUBLE NOT NULL,
    `value_type` INT(11) NOT NULL,
    `discard_remaining` TINYINT(1) NOT NULL DEFAULT '0',
    `shipping_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `exclude_from_shipping_costs` TINYINT(1) NOT NULL DEFAULT '0',
    `no_delivery_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `customer_group_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `mail_sent` TINYINT(1) NOT NULL DEFAULT '0',
    `comment` VARCHAR(255) NULL,
    `currency_factor` DOUBLE NOT NULL,
    `order_position_number` VARCHAR(255) NOT NULL,
    `max_redemption_value` JSON NULL,
    `tag_id` BINARY(16) NULL,
    `currency_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NULL,
    `product_version_id` BINARY(16) NULL,
    `tax_id` BINARY(16) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `json.neti_easy_coupon.max_redemption_value` CHECK (JSON_VALID(`max_redemption_value`)),
    KEY `fk.neti_easy_coupon.tag_id` (`tag_id`),
    KEY `fk.neti_easy_coupon.currency_id` (`currency_id`),
    KEY `fk.neti_easy_coupon.product_id` (`product_id`,`product_version_id`),
    KEY `fk.neti_easy_coupon.tax_id` (`tax_id`),
    CONSTRAINT `fk.neti_easy_coupon.tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon.tax_id` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `value_type` (`value_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;
        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createEasyCouponTransactionTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_transaction` (
    `id` BINARY(16) NOT NULL,
    `transaction_type` INT(11) NOT NULL,
    `value` DOUBLE NOT NULL,
    `intern_comment` VARCHAR(255) NULL,
    `currency_factor` DOUBLE NOT NULL,
    `currency_id` BINARY(16) NOT NULL,
    `easy_coupon_id` BINARY(16) NOT NULL,
    `customer_id` BINARY(16) NULL,
    `order_id` BINARY(16) NULL,
    `order_version_id` BINARY(16) NULL,
    `user_id` BINARY(16) NULL,
    `sales_channel_id` BINARY(16) NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk.neti_easy_coupon_transaction.currency_id` (`currency_id`),
    KEY `fk.neti_easy_coupon_transaction.customer_id` (`customer_id`),
    KEY `fk.neti_easy_coupon_transaction.order_id` (`order_id`,`order_version_id`),
    KEY `fk.neti_easy_coupon_transaction.user_id` (`user_id`),
    KEY `fk.neti_easy_coupon_transaction.sales_channel_id` (`sales_channel_id`),
    CONSTRAINT `fk.neti_easy_coupon_transaction.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_transaction.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_transaction.order_id` FOREIGN KEY (`order_id`,`order_version_id`) REFERENCES `order` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_transaction.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_transaction.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `transaction_type` (`transaction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;
        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createLegacySettingTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_legacy_setting` (
    `id` BINARY(16) NOT NULL,
    `easy_coupon_id` BINARY(16) NOT NULL,
    `legacy_setting` JSON NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `json.neti_easy_coupon_legacy_setting.legacy_setting` CHECK (JSON_VALID(`legacy_setting`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;
        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createProductForVoucherTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_product_for_voucher` (
    `id` BINARY(16) NOT NULL,
    `easy_coupon_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product` BINARY(16) NULL,
    `additional_payment` JSON NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `json.neti_easy_coupon_product_for_voucher.additional_payment` CHECK (JSON_VALID(`additional_payment`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;
        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createEasyCouponProductTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_product` (
    `id` BINARY(16) NOT NULL,
    `value` JSON NOT NULL,
    `value_type` INT(11) NOT NULL,
    `postal` TINYINT(1) NOT NULL DEFAULT '0',
    `shipping_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `exclude_from_shipping_costs` TINYINT(1) NOT NULL DEFAULT '0',
    `no_delivery_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `customer_group_charge` TINYINT(1) NOT NULL DEFAULT '0',
    `comment` VARCHAR(255) NULL,
    `order_position_number` VARCHAR(255) NOT NULL,
    `product_id` BINARY(16) NULL,
    `product_version_id` BINARY(16) NULL,
    `tax_id` BINARY(16) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `json.neti_easy_coupon_product.value` CHECK (JSON_VALID(`value`)),
    KEY `fk.neti_easy_coupon_product.product_id` (`product_id`,`product_version_id`),
    KEY `fk.neti_easy_coupon_product.tax_id` (`tax_id`),
    CONSTRAINT `fk.neti_easy_coupon_product.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_product.tax_id` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `value_type` (`value_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;
        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createEasyCouponTranslationTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_translation` (
    `title` VARCHAR(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `neti_easy_coupon_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    PRIMARY KEY (`neti_easy_coupon_id`,`language_id`),
    KEY `fk.neti_easy_coupon_translation.neti_easy_coupon_id` (`neti_easy_coupon_id`),
    KEY `fk.neti_easy_coupon_translation.language_id` (`language_id`),
    CONSTRAINT `fk.neti_easy_coupon_translation.neti_easy_coupon_id` FOREIGN KEY (`neti_easy_coupon_id`) REFERENCES `neti_easy_coupon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;

        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function createEasyCouponProductTranslationTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_product_translation` (
    `title` VARCHAR(255) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `neti_easy_coupon_product_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    PRIMARY KEY (`neti_easy_coupon_product_id`,`language_id`),
    KEY `fk.neti_easy_coupon_product_translation.neti_product_id` (`neti_easy_coupon_product_id`),
    KEY `fk.neti_easy_coupon_product_translation.language_id` (`language_id`),
    CONSTRAINT `fk.neti_easy_coupon_product_translation.neti_product_id` FOREIGN KEY (`neti_easy_coupon_product_id`) REFERENCES `neti_easy_coupon_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.neti_easy_coupon_product_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;

        $connection->executeStatement($sql);
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    protected function createConditionTable(Connection $connection): void
    {
        $sql = <<<EOL
CREATE TABLE IF NOT EXISTS `neti_easy_coupon_condition` (
    `id` BINARY(16) NOT NULL,
    `type` VARCHAR(255) NULL,
    `coupon_id` BINARY(16) NOT NULL,
    `parent_id` BINARY(16) NULL,
    `value` JSON NULL,
    `position` INT(11) NULL,
    `updated_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `json.neti_easy_coupon_condition.value` CHECK (JSON_VALID(`value`)),
    KEY `fk.neti_easy_coupon_condition.parent_id` (`parent_id`),
    CONSTRAINT `fk.neti_easy_coupon_condition.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `neti_easy_coupon_condition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL;

        $connection->executeStatement($sql);
    }
}
