<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Webhooks\DependencyInjection;

use DeskPRO\Bundle\AppBundle\Webhooks\PayloadConvertersRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(PayloadConvertersRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(PayloadConvertersRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('webhooks.payload_converter');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addPayloadConverter', [new Reference($id)]);
        }
    }
}
