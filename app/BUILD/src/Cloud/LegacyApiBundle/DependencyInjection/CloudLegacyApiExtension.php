<?php

namespace Cloud\LegacyApiBundle\DependencyInjection;

use DeskPRO\Bundle\AppBundle\DependencyInjection\YamlDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExt;

/**
 * Class BrandExtension.
 */
class CloudLegacyApiExtension extends BaseExt
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
