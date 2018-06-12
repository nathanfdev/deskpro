<?php

namespace DeskPRO\Bundle\ApiBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ApiBundle\OAuth\OAuth2;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FOSOAuthServerPass.
 */
class FOSOAuthServerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('fos_oauth_server.server');
        $def->setClass(OAuth2::class);
        $def->addMethodCall('setEntityManager', [new Reference('doctrine.orm.default_entity_manager')]);
    }
}
