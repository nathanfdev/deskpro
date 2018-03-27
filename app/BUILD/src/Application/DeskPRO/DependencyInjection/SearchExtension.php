<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SearchExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $definition = new Definition('Application\\DeskPRO\\Search\\Adapter\\AbstractAdapter');
        $definition->setFactory('Application\\DeskPRO\\StaticLoader\\SearchAdapter::getSearchAdapter');
        $container->setDefinition('deskpro.search_adapter', $definition);

        // Doctrine listener to support search engine
        $definition = new Definition('Application\\DeskPRO\\Search\\EntityWatcher\\EntityWatcher', [new Reference('service_container')]);
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('deskpro.search.entity_listener', $definition);

        $definition = new Definition('Application\\DeskPRO\\Elastica\\ClientFactory', [new Reference('deskpro.core.settings')]);
        $container->setDefinition('deskpro.elastica.client_factory', $definition);

        $definition = new Definition('Application\\DeskPRO\\ApacheTika\\ClientManager', [new Reference('deskpro.core.settings')]);
        $container->setDefinition('deskpro.apache_tika.client_manager', $definition);
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
        return 'deskpro_search';
    }
}
