<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Commands;

use NetInventors\NetiNextEasyCoupon\Service\FakeDataService;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FakeVoucherProductData extends BaseFaker
{
    /**
     * @var string
     */
    protected static $defaultName = 'neti:easy_coupon:product_fake';

    public function __construct(
        FakeDataService $fakeDataService,
        ?string $name = null
    ) {
        parent::__construct($fakeDataService, $name);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('number', null, InputOption::VALUE_OPTIONAL, 'Number of products to generate');
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Wipe previous product entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            parent::execute($input, $output);

            $number = $input->getOption('number');

            if (!\is_numeric($number)) {
                throw new InvalidArgumentException('The argument "number" has to be numeric');
            }

            $this->fakeDataService->fakeVoucherProductData((int) $number, (bool) $input->getOption('reset'));
        } catch (\Exception $exception) {
            $output->write($exception->getMessage(), true);

            $code = $exception->getCode();

            return \is_int($code) ? $code : 1;
        }

        return 0;
    }
}
