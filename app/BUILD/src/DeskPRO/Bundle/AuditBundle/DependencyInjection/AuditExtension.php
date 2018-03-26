<?php

namespace DeskPRO\Bundle\AuditBundle\DependencyInjection;

use DeskPRO\Bundle\AppBundle\DependencyInjection\YamlDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AuditExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config/services');
    }
}
