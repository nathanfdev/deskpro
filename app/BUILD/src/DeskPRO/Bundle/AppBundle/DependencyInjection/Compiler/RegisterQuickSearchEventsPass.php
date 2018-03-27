<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RegisterQuickSearchEventsPass.
 */
class RegisterQuickSearchEventsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $dispatcher_definition = $container->getDefinition('quick_search.event_dispatcher');

        foreach ($container->findTaggedServiceIds('quick_search.event_subscriber') as $id => $tags) {
            $dispatcher_definition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}
