<?php

namespace DeskPRO\Bundle\ReportBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ReportBundle\Dpql2\Func\DpqlFunctionInterface;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\DpqlPlaceholderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DpqlCompilerPass.
 */
class DpqlCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('dpql.func_registry')) {
            foreach ($container->findTaggedServiceIds('dpql.func') as $id => $attributes) {
                $class      = $container->getDefinition($id)->getClass();
                $reflection = new \ReflectionClass($class);
                if (!$reflection->implementsInterface(DpqlFunctionInterface::class)) {
                    throw new \Exception('DPQL function should implement DpqlFunctionInterface');
                }

                $func     = call_user_func([$class, 'getName']);
                $registry = $container->getDefinition('dpql.func_registry');

                $registry->addMethodCall('addFunction', [$func, $id]);
                $registry->addMethodCall('addFunction', [str_replace('_', '', $func), $id]);
                $registry->addMethodCall('addFunction', [preg_replace('/(?<!dpql)_/i', '', $func), $id]);
            }
        }

        if ($container->hasDefinition('dpql.placeholder_registry')) {
            foreach ($container->findTaggedServiceIds('dpql.placeholder') as $id => $attributes) {
                $class      = $container->getDefinition($id)->getClass();
                $reflection = new \ReflectionClass($class);
                if (!$reflection->implementsInterface(DpqlPlaceholderInterface::class)) {
                    throw new \Exception('DPQL function should implement DpqlPlaceholderInterface');
                }

                $func     = call_user_func([$class, 'getName']);
                $registry = $container->getDefinition('dpql.placeholder_registry');

                $registry->addMethodCall('addPlaceholder', [$func, $id]);
            }
        }
    }
}
