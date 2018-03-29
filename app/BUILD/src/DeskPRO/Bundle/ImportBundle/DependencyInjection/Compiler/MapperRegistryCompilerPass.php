<?php

namespace DeskPRO\Bundle\ImportBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ContainerMapperInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MapperRegistryCompilerPass.
 */
class MapperRegistryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dp.importer.writer.mapper_registry')) {
            return;
        }

        $mappers = [];
        foreach ($container->findTaggedServiceIds('dp.importer.writer.mapper') as $id => $attributes) {
            $class = $container->getDefinition($id)->getClass();
            $ref   = new \ReflectionClass($class);

            if (!$ref->implementsInterface(ContainerMapperInterface::class)) {
                throw new \RuntimeException('Expected instance of '.ContainerMapperInterface::class);
            }

            $mappers[call_user_func([$class, 'getMapperEntityClass'])] = $id;
        }

        $container->getDefinition('dp.importer.writer.mapper_registry')->addMethodCall('setContainerMappers', [$mappers]);
    }
}
