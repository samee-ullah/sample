<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\RestApi\V1\Api;

use PHPUnit\Framework\TestCase;
use Swag\PayPal\RestApi\V1\Api\Payment;

class PaymentTest extends TestCase
{
    public function testJsonSerializable(): void
    {
        $payment = new Payment();
        $payment->assign([
            'redirect_urls' => ['return_url' => 'return'],
        ]);

        static::assertSame(
            '{"id":null,"intent":"sale","state":null,"cart":null,"payer":null,"transactions":null,"create_time":null,"update_time":null,"links":null,"redirect_urls":{"return_url":"return","cancel_url":null},"application_context":null}',
            \json_encode($payment)
        );
    }
}
