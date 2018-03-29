<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class YamlDirectoryLoader
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Loads all .yml files into the container from this directory.
     *
     * @param $servicesDir
     */
    public function loadDir($servicesDir)
    {
        $loader = new YamlFileLoader($this->container, new FileLocator($servicesDir));

        $serviceFiles = new Finder();
        $serviceFiles->files()->in($servicesDir)->name('*.yml');

        /** @var \SplFileInfo $file */
        foreach ($serviceFiles as $file) {
            $loader->load($file->getFilename());
        }
    }
}
