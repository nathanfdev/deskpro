<?php

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;

class DbalConnectionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $id => $serviceId) {
            $def     = $container->getDefinition($serviceId);
            $args    = $def->getArguments();
            $args[0] = new Expression("service('deskpro.db_config_reader').getParams('$id')");
            $def->setArguments($args);

            $cnfDef = $container->getDefinition(sprintf('doctrine.dbal.%s_connection.configuration', $id));
            $cnfDef->addMethodCall('setSQLLogger', [new Expression("service('deskpro.db_config_reader').getSqlLogger('$id')")]);
        }
    }
}
