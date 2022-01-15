<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Service\OrderVoucherService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    /**
     * @var OrderVoucherService
     */
    protected $orderVoucherService;

    public function __construct(
        PluginConfig $pluginConfig,
        OrderVoucherService $orderVoucherService
    ) {
        $this->pluginConfig        = $pluginConfig;
        $this->orderVoucherService = $orderVoucherService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class                 => [
                [ 'createVoucherByLineItems', 0 ],
                [ 'createTransactions', 5000 ],
            ],

            'state_machine.order_transaction.state_changed' => [
                'sendVoucherActivateMailByOrderTransaction',
            ],

            // @TODO Also remove redemption voucher on context switch
            SalesChannelContextSwitchEvent::class           => [
                'removePurchaseVoucherFromCart',
            ],

            CheckoutFinishPageLoadedEvent::class            => [
                ['displayPurchaseVouchers', 0 ],
                ['displayVoucherRestValue', 5000 ],
                ['removeVoucherCodesFromSession', 9000 ],
            ],

            'order_line_item.written' => 'onOrderLineItemWritten'
        ];
    }

    public function onOrderLineItemWritten(EntityWrittenEvent $event): void
    {
        // if the live version of the order_line_item is updated, the associated transaction should be updated, too.
        foreach ($event->getWriteResults() as $writeResult) {
            $existence = $writeResult->getExistence();

            if (null === $existence || !$existence->exists()) {
                continue; // we don't care about deleted items here
            }

            $payload = $writeResult->getPayload();

            if ($payload['versionId'] !== Defaults::LIVE_VERSION) {
                continue; // we only update the transaction if the live version order_line_item is updated
            }

            $this->orderVoucherService->updateTransactionValueFromOrderLineItem(
                $writeResult->getPrimaryKey(),
                $event->getContext()
            );
        }
    }

    public function createVoucherByLineItems(CheckoutOrderPlacedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        if (!$event->getOrder()->getLineItems() instanceof OrderLineItemCollection) {
            return;
        }

        $this->orderVoucherService->createVoucherByLineItems($event->getOrder()->getLineItems(), $event->getContext());
    }

    public function displayPurchaseVouchers(CheckoutFinishPageLoadedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->orderVoucherService->addPurchaseVouchersToCheckoutConfirmPage($event->getPage(), $event->getContext());
    }

    public function displayVoucherRestValue(CheckoutFinishPageLoadedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->orderVoucherService->displayVoucherRestValueAtCheckoutConfirmPage($event->getPage(), $event->getContext());
    }

    public function createTransactions(CheckoutOrderPlacedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->orderVoucherService->createTransactionsForRedeemedVouchers($event->getOrder(), $event->getContext());
    }

    public function sendVoucherActivateMailByOrderTransaction(StateMachineStateChangeEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->orderVoucherService->sendVoucherActivateMailByOrderTransaction(
            $event->getTransition()->getEntityId(),
            $event->getNextState()->getId(),
            $event->getContext()
        );
    }

    public function removePurchaseVoucherFromCart(SalesChannelContextSwitchEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $currencyId = $event->getRequestDataBag()->get('currencyId');

        if (!(\is_string($currencyId) && '' !== $currencyId)) {
            return;
        }

        $this->orderVoucherService->removePurchaseVoucherFromCart($event->getSalesChannelContext());
    }

    public function removeVoucherCodesFromSession(CheckoutFinishPageLoadedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $event->getRequest()->getSession()->remove('EasyCouponVoucherCodes');
    }
}
