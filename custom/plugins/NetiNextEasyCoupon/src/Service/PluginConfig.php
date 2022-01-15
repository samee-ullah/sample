<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PluginConfig
{
    public const CONFIG_DOMAIN        = 'NetiNextEasyCoupon.config';

    public const CART_PRIORITY_BEFORE = 'beforeShopwarePromotions';

    public const CART_PRIORITY_AFTER  = 'afterShopwarePromotions';

    /**
     * @var array
     */
    private $config;

    public function __construct(
        SystemConfigService $systemConfig,
        RequestStack $requestStack
    ) {
        $this->loadConfig($systemConfig, $requestStack);
    }

    public function isActive(): bool
    {
        if (!isset($this->config['active'])) {
            return true;
        }

        return !(false === $this->config['active']);
    }

    public function isDisplayInAccount(): bool
    {
        if (!isset($this->config['displayInAccount'])) {
            return true;
        }

        return !(false === $this->config['displayInAccount']);
    }

    public function isShowCodeAfterPayment(): bool
    {
        if (!isset($this->config['showCodeAfterPayment'])) {
            return true;
        }

        return !(false === $this->config['showCodeAfterPayment']);
    }

    /**
     * @return string[]
     */
    public function getVoucherActivatePaymentStatus(): array
    {
        return $this->config['voucherActivatePaymentStatus'] ?? [];
    }

    public function getDefaultCodePattern(): string
    {
        return $this->config['defaultCodePattern'] ?? VoucherCodeGeneratorConfig::DEFAULT_PATTERN;
    }

    public function isAllowCombineVouchers(): bool
    {
        return isset($this->config['allowCombineVouchers'])
            && (bool) $this->config['allowCombineVouchers'];
    }

    public function getCartProcessorPriority(): string
    {
        return $this->config['cartProcessorPriority'] ?? self::CART_PRIORITY_BEFORE;
    }

    private function loadConfig(SystemConfigService $systemConfig, RequestStack $requestStack): void
    {
        $request        = $requestStack->getCurrentRequest();
        $salesChannelId = null;

        if ($request instanceof Request) {
            $salesChannelId = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        }

        $this->config = (array) $systemConfig->get(self::CONFIG_DOMAIN, $salesChannelId);
    }
}
