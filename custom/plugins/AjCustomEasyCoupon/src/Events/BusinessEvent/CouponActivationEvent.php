<?php

declare(strict_types=1);

namespace AjCustomEasyCoupon\Events\BusinessEvent;

use NetInventors\NetiNextEasyCoupon\Events\BusinessEvent\CouponActivationEvent as CouponActivationEventParent;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;

class CouponActivationEvent extends CouponActivationEventParent
{
    public function getMailStruct(): MailRecipientStruct
    {
        $emailRecipients = $this->getEmailRecipients();
        $recipients = [
            $this->order->getOrderCustomer()->getEmail() =>
                $this->order->getOrderCustomer()->getFirstName()
                . ' '
                . $this->order->getOrderCustomer()->getLastName()
        ];
        foreach ($emailRecipients as $emailRecipient) {
            if ($emailRecipient) {
                $recipients[$emailRecipient] = 'Dear Friend';
            }
        }
        return new MailRecipientStruct($recipients);
    }

    private function getEmailRecipients(): array
    {
        $data = [];
        $vouchers = $this->getVouchers();

        foreach ($vouchers as $voucher) {
            $transactions = $voucher->getTransactions();
            foreach ($transactions as $transaction) {
                $data[] = $transaction->getOrderLineItem()->getPayload()['recipientEmail'] ?? false;
            }
        }
        return $data;
    }
}
