<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components\Setup;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportExportProfile
{
    public const IMPORT_EXPORT_PROFILE_NAME = 'EasyCoupon';

    /**
     * @var EntityRepositoryInterface
     */
    private $importExportProfileRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $importExportProfileTranslationRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->importExportProfileRepository            = $container->get('import_export_profile.repository');
        $this->importExportProfileTranslationRepository =
            $container->get('import_export_profile_translation.repository');
        $this->languageRepository                       = $container->get('language.repository');
    }

    public function installImportExportProfile(Context $context): void
    {
        if ($this->isProfileInstalled($context, self::IMPORT_EXPORT_PROFILE_NAME)) {
            return;
        }

        $importExportProfile            = [];
        $importExportProfileTranslation = [];

        $languageIds = $this->getLanguageIds($context);

        $profileId             = Uuid::randomHex();
        $importExportProfile[] = $this->createProfileMapping($profileId, self::IMPORT_EXPORT_PROFILE_NAME);

        foreach ($languageIds as $languageId) {
            $importExportProfileTranslation[] =
                $this->createProfileTranslation($languageId, $profileId, self::IMPORT_EXPORT_PROFILE_NAME);
        }

        if (!empty($importExportProfile)) {
            $this->importExportProfileRepository->create($importExportProfile, $context);
            $this->importExportProfileTranslationRepository->upsert($importExportProfileTranslation, $context);
        }
    }

    /**
     * @param Context $context
     *
     * @return array
     */
    private function getLanguageIds(Context $context): array
    {
        $languageCriteria = new Criteria();

        return $this->languageRepository->searchIds($languageCriteria, $context)->getIds();
    }

    /**
     * @param Context $context
     * @param string  $name
     *
     * @return bool
     */
    private function isProfileInstalled(
        Context $context,
        string $name
    ): bool {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->addFilter(new EqualsFilter('sourceEntity', 'neti_easy_coupon'));

        $result = $this->importExportProfileRepository->searchIds($criteria, $context);

        return !(0 === $result->getTotal());
    }

    /**
     * @param string $id
     * @param string $name
     *
     * @return array|array[]
     */
    private function createProfileMapping(string $id, string $name): array
    {
        return [
            'id'            => $id,
            'name'          => $name,
            'label'         => $name,
            'systemDefault' => false,
            'sourceEntity'  => 'neti_easy_coupon',
            'fileType'      => 'text/csv',
            'delimiter'     => ';',
            'enclosure'     => '"',
            'mapping'       => [
                [
                    'key'       => 'id',
                    'mappedKey' => 'id',
                ],
                [
                    'key'       => 'translations.DEFAULT.title',
                    'mappedKey' => 'title',
                ],
                [
                    'key'       => 'deleted',
                    'mappedKey' => 'deleted',
                ],
                [
                    'key'       => 'deletedDate',
                    'mappedKey' => 'deleted_date',
                ],
                [
                    'key'       => 'active',
                    'mappedKey' => 'active',
                ],
                [
                    'key'       => 'voucherType',
                    'mappedKey' => 'voucher_type',
                ],
                [
                    'key'       => 'code',
                    'mappedKey' => 'code',
                ],
                [
                    'key'       => 'value',
                    'mappedKey' => 'value',
                ],
                [
                    'key'       => 'valueType',
                    'mappedKey' => 'value_type',
                ],
                [
                    'key'       => 'comment',
                    'mappedKey' => 'comment',
                ],
                [
                    'key'       => 'discardRemaining',
                    'mappedKey' => 'discard_remaining',
                ],
                [
                    'key'       => 'shippingCharge',
                    'mappedKey' => 'shipping_charge',
                ],
                [
                    'key'       => 'excludeFromShippingCosts',
                    'mappedKey' => 'exclude_from_shipping_costs',
                ],
                [
                    'key'       => 'noDeliveryCharge',
                    'mappedKey' => 'no_delivery_charge',
                ],
                [
                    'key'       => 'customerGroupCharge',
                    'mappedKey' => 'customer_group_charge',
                ],
                [
                    'key'       => 'mailSent',
                    'mappedKey' => 'mail_sent',
                ],
                [
                    'key'       => 'combineVouchers',
                    'mappedKey' => 'combine_vouchers',
                ],
                [
                    'key'       => 'currencyFactor',
                    'mappedKey' => 'currency_factor',
                ],
                [
                    'key'       => 'orderPositionNumber',
                    'mappedKey' => 'order_position_number',
                ],
                [
                    'key'       => 'maxRedemptionValue',
                    'mappedKey' => 'max_redemption_value',
                ],
                [
                    'key'       => 'currency.isoCode',
                    'mappedKey' => 'currency_iso_code',
                ],
                [
                    'key'       => 'virtualImport',
                    'mappedKey' => 'virtual_import',
                ],
            ],
        ];
    }

    /**
     * @param string $languageId
     * @param string $profileId
     * @param string $profileName
     *
     * @return array
     */
    private function createProfileTranslation(string $languageId, string $profileId, string $profileName): array
    {
        return [
            'importExportProfileId' => $profileId,
            'languageId'            => $languageId,
            'label'                 => $profileName,
        ];
    }
}
