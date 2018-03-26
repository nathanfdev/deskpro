<?php

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;

class MongoConnectionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $connections = $container->getParameter('doctrine_mongodb.odm.connections');

        foreach ($connections as $id => $serviceId) {
            $def     = $container->getDefinition($serviceId);
            $args    = $def->getArguments();
            $args[0] = new Expression("service('deskpro.mongo_config_reader').getParams('$id')");
            $args[1] = new Expression("service('deskpro.mongo_config_reader').getParams('$id', 'options')");

            $def->setArguments($args);
        }
    }
}
