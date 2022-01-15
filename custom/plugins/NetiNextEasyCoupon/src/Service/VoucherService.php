<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCollection;

class VoucherService
{
    /**
     * @var VoucherCodeGenerator
     */
    private $voucherCodeGenerator;

    public function __construct(VoucherCodeGenerator $voucherCodeGenerator)
    {
        $this->voucherCodeGenerator = $voucherCodeGenerator;
    }

    /**
     * @param VoucherCodeGeneratorConfig $config
     *
     * @return VoucherCollection
     * @throws \Exception
     */
    public function generateVoucherCodes(VoucherCodeGeneratorConfig $config): VoucherCollection
    {
        return $this->createGenerator($config)->generateVoucherCodes();
    }

    public function createGenerator(VoucherCodeGeneratorConfig $config): VoucherCodeGenerator
    {
        return $this->voucherCodeGenerator->withConfig($config);
    }
}
