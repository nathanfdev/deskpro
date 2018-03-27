<?php

namespace DeskPRO\Bundle\ReportBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class RendererCompilerPass.
 */
class RendererCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('reports.renderer_registry')) {
            foreach ($container->findTaggedServiceIds('reports.renderer') as $id => $attributes) {
                $class      = $container->getDefinition($id)->getClass();
                $reflection = new \ReflectionClass($class);
                if (!$reflection->implementsInterface(ReportsRendererInterface::class)) {
                    throw new \Exception('Reports renderer should implement ReportsRendererInterface');
                }

                $contentType  = call_user_func([$class, 'getOutputFormat']);
                $outputFormat = call_user_func([$class, 'getExtension']);

                $registry = $container->getDefinition('reports.renderer_registry');
                $registry->addMethodCall('addRenderer', [$contentType, $outputFormat, $id]);
            }
        }
    }
}
