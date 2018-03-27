<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection;

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
     * @param $services_dir
     */
    public function loadDir($services_dir)
    {
        $loader = new YamlFileLoader($this->container, new FileLocator($services_dir));

        $service_files = new Finder();
        $service_files->files()->in($services_dir)->name('*.yml');

        /** @var \SplFileInfo $file */
        foreach ($service_files as $file) {
            $loader->load($file->getFilename());
        }
    }
}
