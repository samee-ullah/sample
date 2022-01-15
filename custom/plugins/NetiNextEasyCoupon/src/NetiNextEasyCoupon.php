<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use NetInventors\NetiNextEasyCoupon\Components\RuleBasedContainerFile;
use NetInventors\NetiNextEasyCoupon\Components\RuleBasedContainerFileLoader;
use NetInventors\NetiNextEasyCoupon\Components\Setup;
use NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator\ValidatorPass as VoucherCodeGeneratorValidatorPass;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidatorPass as VoucherRedemptionValidatorPass;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NetiNextEasyCoupon extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new VoucherCodeGeneratorValidatorPass());
        $container->addCompilerPass(new VoucherRedemptionValidatorPass());

        $ruleBasedContainerFileLoader = new RuleBasedContainerFileLoader(
            $container,
            $this->getPath()
        );

        $version = $container->getParameter('kernel.shopware_version');

        $ruleBasedContainerFileLoader->load(new RuleBasedContainerFile(
            $this->getPath() . '/Resources/config/migrations/6.3.5.0/NEXT-12478-after.xml',
            function () use ($version) {
                return \version_compare($version, '6.3.5.0', '>=')
                    && \version_compare($version, '6.4.3.0', '<');
            }
        ));

        $ruleBasedContainerFileLoader->load(new RuleBasedContainerFile(
            $this->getPath() . '/Resources/config/migrations/6.4.3.0/NEXT-15687.xml',
            function () use ($version) {
                return \version_compare($version, '6.4.3.0', '>=');
            }
        ));
    }

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $setup = new Setup($this->container, $installContext);
        $setup->install();
        $setup->installImportExportProfile($installContext->getContext());
    }

    /**
     * @param UninstallContext $uninstallContext
     *
     * @throws DBALException
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if (false === $uninstallContext->keepUserData()) {
            $connection = $this->container->get(Connection::class);

            if (!$connection instanceof Connection) {
                return;
            }

            $setup = new Setup($this->container, $uninstallContext);
            $setup->uninstall();

            $query = <<<SQL
SET foreign_key_checks=0;
DROP TABLE IF EXISTS `neti_easy_coupon_translation`,
`neti_easy_coupon_product_translation`,
`neti_easy_coupon_product`,
`neti_easy_coupon_transaction`,
`neti_easy_coupon_legacy_setting`,
`neti_easy_coupon_product_for_voucher`,
`neti_easy_coupon`,
`neti_easy_coupon_condition`,
`neti_easy_coupon_product_condition`;
SET foreign_key_checks=1;
SQL;

            $connection->executeQuery($query);
        }
    }

    public function update(UpdateContext $updateContext): void
    {
        $setup = new Setup($this->container, $updateContext);
        $setup->installImportExportProfile($updateContext->getContext());
        $setup->update();
    }
}
