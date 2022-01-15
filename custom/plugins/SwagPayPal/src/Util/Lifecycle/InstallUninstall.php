<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Psr\Log\NullLogger;
use Shopware\Core\Checkout\Cart\Rule\CartAmountRule;
use Shopware\Core\Checkout\Customer\Rule\BillingCountryRule;
use Shopware\Core\Checkout\Customer\Rule\IsCompanyRule;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Shopware\Core\Framework\Rule\Container\AndRule;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Country\Exception\CountryNotFoundException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\PayPal\Checkout\Payment\PayPalPaymentHandler;
use Swag\PayPal\Checkout\Payment\PayPalPuiPaymentHandler;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelDefinition;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelInventoryDefinition;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelMediaDefinition;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelProductDefinition;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelRunDefinition;
use Swag\PayPal\Pos\DataAbstractionLayer\Entity\PosSalesChannelRunLogDefinition;
use Swag\PayPal\Setting\Exception\PayPalSettingsInvalidException;
use Swag\PayPal\Setting\Service\SettingsValidationService;
use Swag\PayPal\Setting\Settings;
use Swag\PayPal\Util\PaymentMethodUtil;

class InstallUninstall
{
    public const PAYPAL_PUI_AVAILABILITY_RULE_NAME = 'PayPalPuiAvailabilityRule';

    private EntityRepositoryInterface $systemConfigRepository;

    private EntityRepositoryInterface $paymentRepository;

    private EntityRepositoryInterface $salesChannelRepository;

    private EntityRepositoryInterface $ruleRepository;

    private EntityRepositoryInterface $countryRepository;

    private PluginIdProvider $pluginIdProvider;

    private string $className;

    private SystemConfigService $systemConfig;

    private Connection $connection;

    public function __construct(
        EntityRepositoryInterface $systemConfigRepository,
        EntityRepositoryInterface $paymentRepository,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $ruleRepository,
        EntityRepositoryInterface $countryRepository,
        PluginIdProvider $pluginIdProvider,
        SystemConfigService $systemConfig,
        Connection $connection,
        string $className
    ) {
        $this->systemConfigRepository = $systemConfigRepository;
        $this->paymentRepository = $paymentRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->ruleRepository = $ruleRepository;
        $this->countryRepository = $countryRepository;
        $this->pluginIdProvider = $pluginIdProvider;
        $this->className = $className;
        $this->systemConfig = $systemConfig;
        $this->connection = $connection;
    }

    public function install(Context $context): void
    {
        $this->addDefaultConfiguration();
        $this->addPaymentMethods($context);
    }

    public function uninstall(Context $context): void
    {
        $this->removeConfiguration($context);
        $this->removePuiAvailabilityRule($context);
        $this->removePosTables();
    }

    private function addDefaultConfiguration(): void
    {
        if ($this->validSettingsExists()) {
            return;
        }

        foreach (Settings::DEFAULT_VALUES as $key => $value) {
            $this->systemConfig->set($key, $value);
        }
    }

    private function removeConfiguration(Context $context): void
    {
        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('configurationKey', Settings::SYSTEM_CONFIG_DOMAIN));
        $idSearchResult = $this->systemConfigRepository->searchIds($criteria, $context);

        $ids = \array_map(static function ($id) {
            return ['id' => $id];
        }, $idSearchResult->getIds());

        if ($ids === []) {
            return;
        }

        $this->systemConfigRepository->delete($ids, $context);
    }

    private function addPaymentMethods(Context $context): void
    {
        $puiAvailabilityRuleId = $this->getPuiAvailabilityRuleId($context);
        $pluginId = $this->pluginIdProvider->getPluginIdByBaseClass($this->className, $context);
        $paymentMethodUtil = new PaymentMethodUtil($this->paymentRepository, $this->salesChannelRepository);

        $data = [];

        $paypalData = [
            'handlerIdentifier' => PayPalPaymentHandler::class,
            'name' => 'PayPal',
            'position' => -100,
            'afterOrderEnabled' => true,
            'pluginId' => $pluginId,
            'description' => 'Payment via PayPal - easy, fast and secure.',
            'translations' => [
                'de-DE' => [
                    'description' => 'Bezahlung per PayPal - einfach, schnell und sicher.',
                    'name' => 'PayPal',
                ],
                'en-GB' => [
                    'description' => 'Payment via PayPal - easy, fast and secure.',
                    'name' => 'PayPal',
                ],
            ],
        ];

        $payPalPaymentMethodId = $paymentMethodUtil->getPayPalPaymentMethodId($context);
        if ($payPalPaymentMethodId === null) {
            $data[] = $paypalData;
        } else {
            $paypalData['id'] = $payPalPaymentMethodId;
            $data[] = $paypalData;
        }

        $puiData = [
            'handlerIdentifier' => PayPalPuiPaymentHandler::class,
            'position' => -99,
            'active' => false,
            'pluginId' => $pluginId,
            'availabilityRuleId' => $puiAvailabilityRuleId,
            'name' => 'Pay upon invoice',
            'description' => 'Buy comfortably on invoice and pay later.',
            'translations' => [
                'de-DE' => [
                    'name' => 'Rechnungskauf',
                    'description' => 'Kaufen Sie ganz bequem auf Rechnung und bezahlen Sie später.',
                ],
                'en-GB' => [
                    'name' => 'Pay upon invoice',
                    'description' => 'Buy comfortably on invoice and pay later.',
                ],
            ],
        ];

        $payPalPuiPaymentMethodId = $paymentMethodUtil->getPayPalPuiPaymentMethodId($context);
        if ($payPalPuiPaymentMethodId === null) {
            $data[] = $puiData;
        } else {
            $puiData['id'] = $payPalPuiPaymentMethodId;
            $data[] = $puiData;
        }

        $this->paymentRepository->upsert($data, $context);
    }

    private function getPuiAvailabilityRuleId(Context $context): string
    {
        if ($paypalPuiAvailabilityRuleId = $this->getPayPalPuiAvailabilityRuleId($context)) {
            return $paypalPuiAvailabilityRuleId;
        }

        $germanCountryId = $this->getGermanCountryId($context);
        $ruleId = Uuid::randomHex();
        $data = [
            'id' => $ruleId,
            'name' => self::PAYPAL_PUI_AVAILABILITY_RULE_NAME,
            'priority' => 1,
            'description' => 'Determines whether or not the PayPal - Pay upon invoice payment method is available for the given rule context.',
            'conditions' => [
                [
                    'type' => (new AndRule())->getName(),
                    'children' => [
                        [
                            'type' => (new BillingCountryRule())->getName(),
                            'value' => [
                                'operator' => BillingCountryRule::OPERATOR_EQ,
                                'countryIds' => [
                                    $germanCountryId,
                                ],
                            ],
                        ],
                        [
                            'type' => (new IsCompanyRule())->getName(),
                            'value' => [
                                'isCompany' => false,
                            ],
                        ],
                        [
                            'type' => (new CartAmountRule())->getName(),
                            'value' => [
                                'operator' => CartAmountRule::OPERATOR_GTE,
                                'amount' => 2.0,
                            ],
                        ],
                        [
                            'type' => (new CartAmountRule())->getName(),
                            'value' => [
                                'operator' => CartAmountRule::OPERATOR_LTE,
                                'amount' => 1470.0,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->ruleRepository->create([$data], $context);

        return $ruleId;
    }

    /**
     * @throws CountryNotFoundException
     */
    private function getGermanCountryId(Context $context): string
    {
        $germanIso3 = 'DEU';
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('iso3', $germanIso3)
        );

        /** @var CountryEntity|null $germanCountry */
        $germanCountry = $this->countryRepository->search($criteria, $context)->first();

        if ($germanCountry === null) {
            throw new CountryNotFoundException($germanIso3);
        }

        return $germanCountry->getId();
    }

    private function removePuiAvailabilityRule(Context $context): void
    {
        $paymentMethodUtil = new PaymentMethodUtil($this->paymentRepository, $this->salesChannelRepository);
        $payPalPuiPaymentMethodId = $paymentMethodUtil->getPayPalPuiPaymentMethodId($context);
        if ($payPalPuiPaymentMethodId === null) {
            return;
        }

        $criteria = new Criteria([$payPalPuiPaymentMethodId]);

        /** @var PaymentMethodEntity $payPalPuiPaymentMethod */
        $payPalPuiPaymentMethod = $this->paymentRepository->search($criteria, $context)->get($payPalPuiPaymentMethodId);

        $payPalPuiPaymentMethodAvailabilityRuleId = $payPalPuiPaymentMethod->getAvailabilityRuleId();
        if ($payPalPuiPaymentMethodAvailabilityRuleId === null) {
            return;
        }

        $this->paymentRepository->update([[
            'id' => $payPalPuiPaymentMethodId,
            'availabilityRuleId' => null,
        ]], $context);

        $this->ruleRepository->delete([[
            'id' => $payPalPuiPaymentMethodAvailabilityRuleId,
        ]], $context);
    }

    private function getPayPalPuiAvailabilityRuleId(Context $context): ?string
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', self::PAYPAL_PUI_AVAILABILITY_RULE_NAME));

        /** @var RuleEntity|null $paypalPuiRule */
        $paypalPuiRule = $this->ruleRepository->search($criteria, $context)->first();
        if ($paypalPuiRule === null) {
            return null;
        }

        return $paypalPuiRule->getId();
    }

    private function validSettingsExists(): bool
    {
        // since we don't have access to the regular service, we create it
        $validation = new SettingsValidationService($this->systemConfig, new NullLogger());

        try {
            $validation->validate();
        } catch (PayPalSettingsInvalidException $e) {
            return false;
        }

        return true;
    }

    private function removePosTables(): void
    {
        $classNames = [
            PosSalesChannelInventoryDefinition::ENTITY_NAME,
            PosSalesChannelMediaDefinition::ENTITY_NAME,
            PosSalesChannelProductDefinition::ENTITY_NAME,
            PosSalesChannelRunLogDefinition::ENTITY_NAME,
            PosSalesChannelRunDefinition::ENTITY_NAME,
            PosSalesChannelDefinition::ENTITY_NAME,
        ];

        foreach ($classNames as $className) {
            $this->connection->executeUpdate(\sprintf('DROP TABLE IF EXISTS `%s`', $className));
        }
    }
}
