<?php

namespace DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EntityHandlerRegistryCompilerPass.
 */
class EntityHandlerRegistryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.importer.writer.entity_handler_registry')) {
            return;
        }

        $handlers = [];
        foreach ($container->findTaggedServiceIds('dp.importer.writer.entity_handler') as $id => $attributes) {
            $class = $container->getDefinition($id)->getClass();
            $ref   = new \ReflectionClass($class);

            if (array_key_exists('priority', $attributes[0])) {
                $priority = (int) $attributes[0]['priority'];
            } else {
                $priority = 0;
            }

            if (!$ref->implementsInterface(EntityHandlerInterface::class)) {
                throw new \RuntimeException('Expected instance of '.EntityHandlerInterface::class);
            }

            $handlers[$priority][call_user_func([$class, 'getModelClass'])] = $id;
        }

        krsort($handlers);
        $flattenHandlers = [];
        foreach ($handlers as $priorityHandlers) {
            foreach ($priorityHandlers as $modelClass => $handler) {
                $flattenHandlers[$modelClass] = $handler;
            }
        }

        $container->getDefinition('dp.importer.writer.entity_handler_registry')->addMethodCall('setHandlers', [$flattenHandlers]);
    }
}
