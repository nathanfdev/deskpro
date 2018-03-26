<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $config);

        $notification_config = [];
        if (array_key_exists('notification', $config)) {
            if (array_key_exists('strategies', $config['notification'])) {
                $notification_config = $config['notification']['strategies'];
            }
        }
        $container->setParameter('notification.settings', $notification_config);

        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config/services');

        // use our translator
        $container->setAlias('translator', 'deskpro.core.translate');

        $this->applyBackwardsCompatibilityRequirements($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function applyBackwardsCompatibilityRequirements(ContainerBuilder $container)
    {
        $container->setAlias('deskpro.blob_storage', 'blob.storage');
    }
}
