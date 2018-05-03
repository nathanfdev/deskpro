<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SendmailExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config/services');
    }
}
