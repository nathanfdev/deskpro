<?php

namespace DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SourceScriptResolverCompilerPass.
 */
class SourceScriptResolverCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.importer.source_script_resolver')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('dp.importer.source.helper') as $id => $attributes) {
            $container->getDefinition('dp.importer.source_script_resolver')->addMethodCall('addHelper', [$id]);
        }
    }
}
