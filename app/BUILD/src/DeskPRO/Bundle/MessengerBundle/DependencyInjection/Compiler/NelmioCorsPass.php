<?php

namespace DeskPRO\Bundle\MessengerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class NelmioCorsPass.
 */
class NelmioCorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // in most cases definition `nelmio_cors.cors_listener` exists
        // but because of kernel spaghetti for some console commands we have to skip this process
        if (!$container->hasDefinition('nelmio_cors.cors_listener')) {
            return;
        }
        $def = $container->getDefinition('nelmio_cors.cors_listener');
        $def->addMethodCall('setIgnoreAuth', [true]);
    }
}
