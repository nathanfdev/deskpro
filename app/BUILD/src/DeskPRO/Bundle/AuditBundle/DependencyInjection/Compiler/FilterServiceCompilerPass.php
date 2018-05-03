<?php

namespace DeskPRO\Bundle\AuditBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilterServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('audit_log.field_filter_service')) {
            $definition = $container->getDefinition('audit_log.field_filter_service');
            $services   = $container->findTaggedServiceIds('audit.field_filter');
            foreach ($services as $id => $service) {
                $tags = $container->findDefinition($id)->getTag('audit.field_filter');
                foreach ($tags as $tag) {
                    $definition->addMethodCall('registerFilter', [new Reference($id), $tag['alias']]);
                }
            }
        }
    }
}
