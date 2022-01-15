<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RuleBasedContainerFileLoader
{
    /**
     * @var DelegatingLoader
     */
    private $loader;

    public function __construct($container, $path)
    {
        $fileLocator    = new FileLocator($path);
        $loaderResolver = new LoaderResolver([
            new XmlFileLoader($container, $fileLocator),
        ]);

        $this->loader = new DelegatingLoader($loaderResolver);
    }

    public function load(RuleBasedContainerFile $ruleBasedContainerFile): void
    {
        if ($ruleBasedContainerFile->match()) {
            $this->loader->load($ruleBasedContainerFile->getFile());
        }
    }
}
