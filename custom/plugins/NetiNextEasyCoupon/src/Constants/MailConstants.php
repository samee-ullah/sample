<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Constants;

abstract class MailConstants
{
    public const MAIL_VOUCHER_ACTIVATION_NAME           = 'NetiEasyCoupon_ActivateCoupon';

    public const MAIL_VOUCHER_ACTIVATION_TECHNICAL_NAME = 'neti_easy_coupon_activate_coupon';

    /**
     * @return array<string>
     */
    public static function getMailTechnicalNames(): array
    {
        $reflection     = new \ReflectionClass(self::class);
        $constants      = $reflection->getConstants() ?? [];
        $technicalNames = [];

        foreach ($constants as $constantName => $constantValue) {
            if (\is_int(\mb_strpos($constantName, 'TECHNICAL_NAME'))) {
                $technicalNames[] = $constantValue;
            }
        }

        return $technicalNames;
    }
}
