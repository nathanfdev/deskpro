<?php

/**
 * https://gist.github.com/vadim2404/9538227.
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEntityListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('dp.doctrine.entity_listener_resolver')) {
            return;
        }

        $ems = $container->getParameter('doctrine.entity_managers');
        foreach ($ems as $name => $em) {
            $container->getDefinition(sprintf('doctrine.orm.%s_configuration', $name))
                ->addMethodCall('setEntityListenerResolver', [new Reference('dp.doctrine.entity_listener_resolver')])
            ;
        }

        $definition = $container->getDefinition('dp.doctrine.entity_listener_resolver');
        $services   = $container->findTaggedServiceIds('doctrine.entity_listener');

        foreach ($services as $service => $attributes) {
            $definition->addMethodCall(
                'addMapping',
                [$container->getDefinition($service)->getClass(), $service]
            );
        }
    }
}
