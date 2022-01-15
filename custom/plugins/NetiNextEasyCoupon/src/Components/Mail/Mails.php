<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components\Mail;

use NetInventors\NetiNextEasyCoupon\Constants\MailConstants;

class Mails
{
    public const VOUCHER_ACTIVATION = [
        'name'              => MailConstants::MAIL_VOUCHER_ACTIVATION_NAME,
        'technicalName'     => MailConstants::MAIL_VOUCHER_ACTIVATION_TECHNICAL_NAME,
        'description'       => [
            'de-DE' => 'NetiEasyCoupon: Gutschein(e) wurde(n) aktiviert',
            'en-GB' => 'NetiEasyCoupon: Voucher(s) was/were activate',
        ],
        'subject'           => [
            'de-DE' => 'Ihr(e) Gutschein(e) wurde(n) aktiviert',
            'en-GB' => 'Your voucher(s) has/have been activated',
        ],
        'availableEntities' => [
            'customer'     => 'order_customer',
            'salesChannel' => 'sales_channel',
        ],
        'senderName'        => '{{ salesChannel.name }}',
    ];
}
