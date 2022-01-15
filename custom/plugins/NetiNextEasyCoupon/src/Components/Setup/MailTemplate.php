<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components\Setup;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use NetInventors\NetiNextEasyCoupon\Components\Mail\Mails;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailTemplate
{
    /**
     * @var EntityRepository
     */
    private $mailTemplateTypeRepository;

    /**
     * @var EntityRepository
     */
    private $mailTemplateRepository;

    /**
     * @var EntityRepository
     */
    private $languageRepository;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->mailTemplateTypeRepository = $container->get('mail_template_type.repository');
        $this->mailTemplateRepository     = $container->get('mail_template.repository');
        $this->languageRepository         = $container->get('language.repository');
    }

    public function create(Context $context): void
    {
        $mailTemplateTypes = [];
        $mailTemplates     = [];
        $languages         = $this->getLanguages($context);

        /** @var LanguageEntity $defaultLanguage */
        $defaultLanguage = $languages->get(Defaults::LANGUAGE_SYSTEM);
        $defaultIsoCode  = $defaultLanguage->getLocale()->getCode();

        /**
         * This must be extended when we support more than 2 languages.
         *
         * The $languageCode is only used to read the mailConfig.subject and mailConfig.description correctly.
         */
        $languageCode = $defaultIsoCode === 'de-DE' ? $defaultIsoCode : 'en-GB';

        foreach ($this->getMails() as $name => $mailConfig) {
            $templatePath = __DIR__ . '/../Mail/' . $mailConfig['technicalName'] . '/';

            $mailTemplateType = [
                'id'                => Uuid::randomHex(),
                'name'              => $mailConfig['name'],
                'technicalName'     => $mailConfig['technicalName'],
                'availableEntities' => $mailConfig['availableEntities'] ?? [],
            ];

            $mailTemplateTypes[] = $mailTemplateType;

            $mailTemplate = [
                'id'                 => Uuid::randomHex(),
                'mailTemplateTypeId' => $mailTemplateType['id'],
                'subject'            => $mailConfig['subject'][$languageCode],
                'contentPlain'       => $this->loadContent($templatePath, $defaultIsoCode, 'plain.html.twig'),
                'contentHtml'        => $this->loadContent($templatePath, $defaultIsoCode, 'html.html.twig'),
                'senderName'         => $mailConfig['senderName'] ?? '{{ salesChannel.name }}',
                'description'        => $mailConfig['description'][$languageCode],
                'translations'       => [],
            ];

            /** @var LanguageEntity $language */
            foreach ($languages as $language) {
                $locale = $language->getLocale();

                if (
                    !$locale instanceof LocaleEntity
                    || $defaultIsoCode === $locale->getCode()
                ) {
                    continue;
                }

                $isoCode = $locale->getCode();

                $mailTemplate['translations'][] = [
                    'id'                 => Uuid::randomHex(),
                    'mailTemplateTypeId' => $mailTemplateType['id'],
                    'subject'            => $mailConfig['subject'][$isoCode],
                    'contentPlain'       => $this->loadContent($templatePath, $isoCode, 'plain.html.twig'),
                    'contentHtml'        => $this->loadContent($templatePath, $isoCode, 'html.html.twig'),
                    'senderName'         => $mailConfig['senderName'] ?? '{{ salesChannel.name }}',
                    'description'        => $mailConfig['description'][$isoCode],
                    'languageId'         => $language->getId(),
                ];
            }

            $mailTemplates[] = $mailTemplate;
        }

        try {
            $this->mailTemplateTypeRepository->create($mailTemplateTypes, $context);
            $this->mailTemplateRepository->create($mailTemplates, $context);
        } catch (UniqueConstraintViolationException $exception) {
            // Noop, we've already installed the fields, it's fine.
        }
    }

    public function remove(Context $context): void
    {
        $technicalMailNames = [];

        foreach ($this->getMails() as $name => $mailConfig) {
            $technicalMailNames[] = $mailConfig['technicalName'];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('technicalName', $technicalMailNames));

        $mailTemplateTypeIds = $this->mailTemplateTypeRepository->search($criteria, $context)->getIds();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('mailTemplateTypeId', $mailTemplateTypeIds));

        $mailTemplateIds = $this->mailTemplateRepository->searchIds($criteria, $context)->getIds();

        $this->removeIds($this->mailTemplateRepository, $mailTemplateIds, $context);
        $this->removeIds($this->mailTemplateTypeRepository, $mailTemplateTypeIds, $context);
    }

    /**
     * A helper method to delete a bunch of ids of the given repository.
     *
     * @param EntityRepositoryInterface $repository
     * @param array                     $ids
     * @param Context                   $context
     */
    private function removeIds(EntityRepositoryInterface $repository, array $ids, Context $context): void
    {
        $ids = \array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $ids
        );

        if (0 < \count($ids)) {
            $repository->delete(\array_values($ids), $context);
        }
    }

    /**
     * Extracts the mail template definitions from the \NetInventors\NetiNextEasyCoupon\Components\Mail\Mails class
     *
     * @return array
     */
    private function getMails(): array
    {
        $reflection = new \ReflectionClass(Mails::class);

        return $reflection->getConstants();
    }

    /**
     * Loads the content for the given locale and type. If the given locale does not exist, we fallback to
     * $fallbackIsoCode
     *
     * @param string $path
     * @param string $isoCode
     * @param string $type
     * @param string $fallbackIsoCode
     *
     * @return string
     */
    private function loadContent(string $path, string $isoCode, string $type, string $fallbackIsoCode = 'en-GB'): string
    {
        $filename = $path . $isoCode . '/' . $type;

        if (!is_file($filename)) {
            $filename = $path . $fallbackIsoCode . '/' . $type;
        }

        return file_get_contents($filename);
    }

    /**
     * Load languages.
     *
     * @param Context $context
     *
     * @return EntityCollection
     */
    private function getLanguages(Context $context): EntityCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');

        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('id', Defaults::LANGUAGE_SYSTEM),
                    new EqualsAnyFilter('locale.code', [ 'en-GB', 'de-DE' ]),
                ]
            )
        );

        return $this->languageRepository->search($criteria, $context);
    }
}