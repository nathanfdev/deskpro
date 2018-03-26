<?php

namespace DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class HelperRegistryCompilerPass.
 */
class HelperRegistryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.importer.writer.helper_registry')) {
            return;
        }

        $helpers = [];
        foreach ($container->findTaggedServiceIds('dp.importer.writer.helper') as $id => $attributes) {
            $class           = $container->getDefinition($id)->getClass();
            $helpers[$class] = $id;
        }

        $container->getDefinition('dp.importer.writer.helper_registry')->addMethodCall('setHelpers', [$helpers]);
    }
}
