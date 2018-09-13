<?php

namespace DeskPRO\Bundle\VoiceBundle\DependencyInjection;

use DeskPRO\Bundle\AppBundle\DependencyInjection\YamlDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class VoiceExtension.
 */
class VoiceExtension extends Extension
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
