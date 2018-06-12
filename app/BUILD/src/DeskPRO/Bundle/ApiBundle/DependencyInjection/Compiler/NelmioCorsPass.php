<?php

namespace DeskPRO\Bundle\ApiBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ApiBundle\EventListener\CorsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class NelmioCorsPass.
 */
class NelmioCorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('nelmio_cors.cors_listener');
        $def->setClass(CorsListener::class);
        $def->addMethodCall('setApiAuthenticator', [new Reference('api_authenticator')]);
        $def->addMethodCall('setTokenStorage', [new Reference('security.token_storage')]);
        $def->addMethodCall('setEntityManager', [new Reference('doctrine.orm.default_entity_manager')]);
    }
}
