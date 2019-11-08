<?php

namespace DeskPRO\Bundle\MessengerBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\MessengerBundle\Security\EventListener\CorsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        // this should be done for messenger kernel only
        if (!$container->hasDefinition('nelmio_cors.cors_listener')
            || $container->getParameter('kernel.name') !== 'Messenger') {
            return;
        }
        $def = $container->getDefinition('nelmio_cors.cors_listener');
        $def->setClass(CorsListener::class);
        $def->addMethodCall('setBrandSettingsResolver', [new Reference('brand_aware_settings_resolver')]);
    }
}
