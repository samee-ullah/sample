<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ValidatorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const SERVICE_TAG = 'neti_easy_coupon.voucher_generator.validator';

    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(ValidationProcessor::class)) {
            return;
        }

        /** @phpstan-ignore-next-line */
        $definition     = $container->findDefinition(ValidationProcessor::class);
        $taggedServices = $this->findAndSortTaggedServices(self::SERVICE_TAG, $container);

        foreach ($taggedServices as $reference) {
            $definition->addMethodCall('addValidator', [ $reference ]);
        }
    }
}
