<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TriggersCollectorCompilerPass.
 *
 * Add trigger services to the TriggeringProcess service
 */
class TriggersCollectorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('dp_sys.alerts.triggering_process')) {
            return;
        }

        $definition = $container->findDefinition('dp_sys.alerts.triggering_process');
        $triggers   = $container->findTaggedServiceIds('dp_sys.alerts.trigger');
        foreach ($triggers as $id => $tags) {
            $definition->addMethodCall('addTrigger', [new Reference($id)]);
        }
    }
}
