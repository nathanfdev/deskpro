<?php

namespace DeskPRO\Bundle\VoiceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EventListenerCompilerPass.
 */
class EventListenerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.voice.event_dispatcher')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('dp.voice.event_subscriber') as $id => $tags) {
            $container->getDefinition('dp.voice.event_dispatcher')->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}
