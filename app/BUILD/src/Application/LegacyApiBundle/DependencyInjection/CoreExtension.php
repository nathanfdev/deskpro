<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\LegacyApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Registers basic core stuff.
 */
class CoreExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $definition = new Definition('Application\\LegacyApiBundle\\Request\\RequestAuth');
        $definition->setScope('request');
        $definition->setArguments([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('request'),
            new Reference('settings_resolver'),
        ]);
        $container->setDefinition('deskpro.api.request_auth', $definition);

        $container
            ->register('apiv1.endpoint_listener', 'Application\\LegacyApiBundle\\Event\\Apiv1EndpointListener')
            ->addArgument(new Reference('security.authorization_checker'))
            ->addTag('kernel.event_subscriber');
    }

    public function getXsdValidationBasePath()
    {
        return;
    }

    public function getNamespace()
    {
        return;
    }

    public function getAlias()
    {
        return 'deskpro_api_core';
    }
}
