<?php

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FeaturesCompilerPass.
 */
class FeaturesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('deskpro.features_collection')) {
            return;
        }

        $collection = $container->getDefinition('deskpro.features_collection');
        foreach ($container->findTaggedServiceIds('deskpro.feature') as $id => $attributes) {
            $class = $container->getDefinition($id)->getClass();
            $ref   = new \ReflectionClass($class);

            if (!$ref->implementsInterface(BetaFeatureInterface::class)) {
                throw new \RuntimeException('Expected instance of '.BetaFeatureInterface::class);
            }

            $collection->addMethodCall('addFeature', [new Reference($id)]);
        }
    }
}
