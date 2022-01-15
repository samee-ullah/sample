<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Constants;

use NetInventors\NetiNextEasyCoupon\Events\BusinessEvent\CouponActivationEvent;

abstract class BusinessEventsConstants
{
    public const EVENTS = [
        CouponActivationEvent::NAME  => [
            MailConstants::MAIL_VOUCHER_ACTIVATION_TECHNICAL_NAME,
        ]
    ];

    public const EVENT_CLASSES = [
        CouponActivationEvent::class,
    ];
}
