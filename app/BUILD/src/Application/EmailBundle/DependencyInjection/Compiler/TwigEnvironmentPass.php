<?php

namespace Application\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged twig.extension services to twig service.
 */
class TwigEnvironmentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('templating.email.twig')) {
            return;
        }

        $definition = $container->getDefinition('templating.email.twig');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls([]);
        foreach ($container->findTaggedServiceIds('email.templating.twig.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', [new Reference($id)]);
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
