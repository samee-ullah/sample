<?php
/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

namespace NetInventors\NetiNextEasyCoupon\Commands;

use NetInventors\NetiNextEasyCoupon\Service\FakeDataService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseFaker extends Command
{
    /**
     * @var FakeDataService
     */
    protected $fakeDataService;

    public function __construct(FakeDataService $fakeDataService, string $name = null)
    {
        parent::__construct($name);

        $this->fakeDataService = $fakeDataService;
    }

    protected function configure(): void
    {
        $this->addOption('iknowwhatiamdoing', null, InputOption::VALUE_NONE,'Argument has to be set to execute this command');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('iknowwhatiamdoing') === false) {
            throw new InvalidArgumentException('Option "iknowwhatiamdoing" is missing. Please set it.' );
        }

        if ('dev' !== getenv('APP_ENV')) {
            throw new \Exception('Command is allowed in dev environment only.');
        }

        return 1;
    }
}
