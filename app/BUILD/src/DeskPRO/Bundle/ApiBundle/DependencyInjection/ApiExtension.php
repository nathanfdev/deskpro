<?php

namespace DeskPRO\Bundle\ApiBundle\DependencyInjection;

use DeskPRO\Bundle\ApiBundle\Request\ApiVersionInfo;
use DeskPRO\Bundle\AppBundle\DependencyInjection\YamlDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Class ApiExtension.
 */
class ApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config/services');

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        // register version info
        $definition = new Definition(ApiVersionInfo::class, [
            'default_version' => $config['default_version'],
            'versions'        => $config['versions'],
        ]);

        $container->setDefinition('api.version_info', $definition);
    }
}
