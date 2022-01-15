<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use NetInventors\NetiNextEasyCoupon\Components\Setup\ImportExportProfile;
use NetInventors\NetiNextEasyCoupon\Constants\BusinessEventsConstants;
use Shopware\Core\Content\MailTemplate\MailTemplateActions;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Event\EventAction\EventActionEntity;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use NetInventors\NetiNextEasyCoupon\Components\Setup\MailTemplate;

class Setup
{
    /**
     * @var EntityRepository
     */
    private $productRepository;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var MailTemplate
     */
    private $mailTemplateSetup;

    /**
     * @var ImportExportProfile
     */
    private                           $importProfileSetup;

    private EntityRepositoryInterface $eventRepository;

    private EntityRepositoryInterface $mailTemplateRepository;

    private InstallContext            $context;

    public function __construct(
        ContainerInterface $container,
        InstallContext $context
    ) {
        $this->context                = $context;
        $this->mailTemplateSetup      = new MailTemplate($container);
        $this->productRepository      = $container->get('product.repository');
        $this->db                     = $container->get(Connection::class);
        $this->importProfileSetup     = new ImportExportProfile($container);
        $this->eventRepository        = $container->get('event_action.repository');
        $this->mailTemplateRepository = $container->get('mail_template.repository');
    }

    public function install(): void
    {
        $this->mailTemplateSetup->create($this->context->getContext());
        $this->createEventActions();
    }

    public function update(): void
    {
        $this->createEventActions();
    }

    protected function createEventActions(): void
    {
        $events = $this->getFilteredEvents();
        if ([] === $events) {
            return;
        }

        $mailTemplates = [];
        foreach ($events as $event) {
            $mailTemplates = \array_merge($mailTemplates, $event);
        }

        $data                  = [];
        $mailTemplatesEntities = $this->getMailTemplates($mailTemplates);
        foreach ($events as $eventName => $mailTechnicalNames) {
            foreach ($mailTechnicalNames as $technicalName) {
                $mailTemplateEntity = $mailTemplatesEntities[$technicalName];

                $data[]             = [
                    'id'         => Uuid::randomHex(),
                    'eventName'  => $eventName,
                    'actionName' => MailTemplateActions::MAIL_TEMPLATE_MAIL_SEND_ACTION,
                    'config'     => [
                        'mail_template_id'      => $mailTemplateEntity->getId(),
                        'mail_template_type_id' => $mailTemplateEntity->getMailTemplateTypeId(),
                    ],
                ];
            }
        }

        $this->eventRepository->create($data, $this->context->getContext());
    }

    protected function getMailTemplates(array $technicalNames): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('mailTemplateType.technicalName', $technicalNames)
        )
            ->addAssociation('mailTemplateType');

        $mailTemplates = $this->mailTemplateRepository->search($criteria, $this->context->getContext());
        if ($mailTemplates->count() < \count($technicalNames)) {
            throw new \RuntimeException(
                \sprintf(
                    'One of required mail templates (%s) not found.',
                    \implode(', ', $technicalNames)
                )
            );
        }

        $result = [];

        /** @var MailTemplateEntity $mailTemplate */
        foreach ($mailTemplates->getElements() as $mailTemplate) {
            $result[$mailTemplate->getMailTemplateType()->getTechnicalName()] = $mailTemplate;
        }

        return $result;
    }

    protected function getFilteredEvents(): array
    {
        $events = BusinessEventsConstants::EVENTS;

        /** @var EventActionEntity[] $eventEntities */
        $eventEntities = $this->eventRepository->search(
            (new Criteria())->addFilter(
                new EqualsAnyFilter('eventName', array_keys($events))
            ),
            $this->context->getContext()
        )->getElements();

        foreach ($eventEntities as $entity) {
            unset($events[$entity->getEventName()]);
        }

        return $events;
    }

    public function installImportExportProfile($context): void
    {
        $this->importProfileSetup->installImportExportProfile($context);
    }

    /**
     * @throws DBALException
     */
    public function uninstall(): void
    {
        $this->deleteBusinessEvents();
        $this->mailTemplateSetup->remove($this->context->getContext());
        $this->deletePurchaseVoucherProducts();
    }

    protected function deleteBusinessEvents(): void
    {
        $criteria        = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('eventName', \array_keys(BusinessEventsConstants::EVENTS)));

        $eventIds = $this->eventRepository->searchIds($criteria, $this->context->getContext())->getIds();

        $data = \array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $eventIds
        );

        if ([] === $data) {
            return;
        }

        $this->eventRepository->delete($data, $this->context->getContext());
    }

    /**
     * Delete products of purchase vouchers.
     *
     * @throws DBALException
     */
    protected function deletePurchaseVoucherProducts(): void
    {
        $sql = '
            SELECT product_id
            FROM neti_easy_coupon_product
            WHERE product_id IS NOT NULL
        ';

        $productIds = $this->db->executeQuery($sql)->fetchAll(FetchMode::COLUMN);

        if ([] === $productIds) {
            return;
        }

        $productIds = array_map(
            static function ($item) {
                return [
                    'id' => Uuid::fromBytesToHex($item),
                ];
            },
            $productIds
        );

        $this->productRepository->delete($productIds, $this->context->getContext());
    }
}
