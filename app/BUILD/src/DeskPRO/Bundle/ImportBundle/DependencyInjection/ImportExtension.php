<?php

namespace DeskPRO\Bundle\ImportBundle\DependencyInjection;

use DeskPRO\Bundle\AppBundle\DependencyInjection\YamlDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ImportExtension.
 */
class ImportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config');
    }
}
