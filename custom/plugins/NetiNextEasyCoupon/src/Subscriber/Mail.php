<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Service\OrderVoucherService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Mail implements EventSubscriberInterface
{
    /**
     * @var PluginConfig
     */
    private $config;

    /**
     * @var OrderVoucherService
     */
    private $orderVoucherService;

    public function __construct(PluginConfig $config, OrderVoucherService $orderVoucherService)
    {
        $this->config              = $config;
        $this->orderVoucherService = $orderVoucherService;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MailBeforeValidateEvent::class => 'beforeMailValidate',
        ];
    }

    public function beforeMailValidate(MailBeforeValidateEvent $event): void
    {
        if (!(
            $this->config->isActive()
            && isset($event->getTemplateData()['order'])
            && $event->getTemplateData()['order'] instanceof OrderEntity
        )) {
            return;
        }

        $this->orderVoucherService->addPurchaseVouchersToOrder($event->getTemplateData()['order'], $event->getContext());
    }
}
