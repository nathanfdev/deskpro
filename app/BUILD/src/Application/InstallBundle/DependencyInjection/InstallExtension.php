<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class InstallExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        //# NOTE: duplicated in config.php
        $definition = new Definition();
        $definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\SimpleArrayCache');
        $definition->setArguments([]);
        $container->setDefinition('cache.simple_array', $definition);

        $definition = new Definition('Application\\DeskPRO\\Settings\\Settings', [
            DP_ROOT.'/sys/config/settings.php',
            new Reference('database_connection'),
        ]);
        $container->setDefinition('deskpro.core.settings', $definition);

        $definition = new Definition('Application\\DeskPRO\\Search\\Adapter\\AbstractAdapter');
        $definition->setFactory('Application\\DeskPRO\\StaticLoader\\SearchAdapter::getSearchAdapter');
        $container->setDefinition('deskpro.search_adapter', $definition);

        // slug listener (sets slugs on content)
        // note this is a duplicate for install (canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineContentSlugListener');
        $definition->setArguments([new Reference('content_slug_manager')]);
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('doctrine_listener.content_slug', $definition);
        // slug manager (note duplicate: canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\Content\ContentSlugManager');
        $definition->setArguments([new Reference('service_container')]);
        $container->setDefinition('content_slug_manager', $definition);

        // slug listener (sets slugs on categories)
        // note this is a duplicate for install (canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineCategorySlugListener');
        $definition->setArguments([new Reference('category_slug_manager')]);
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('doctrine_listener.category_slug', $definition);
        // slug manager (note duplicate: canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\Content\CategorySlugManager');
        $definition->setArguments([new Reference('service_container')]);
        $container->setDefinition('category_slug_manager', $definition);

        $this->loadInputReader($container);
    }

    /**
     * Sets up the input reader.
     */
    protected function loadInputReader(ContainerBuilder $container)
    {
        $request_stack_reference = new Reference('request_stack');

        $definition = new Definition(
            'Orb\Input\Reader\Source\Superglobal',
            ['_REQUEST', ['accept_json_post' => true], $request_stack_reference]
        );
        $container->setDefinition('deskpro.core.input_reader_req', $definition);

        $definition = new Definition(
            'Orb\Input\Reader\Source\Superglobal',
            ['_POST', ['accept_json_post' => true], $request_stack_reference]
        );
        $container->setDefinition('deskpro.core.input_reader_post', $definition);

        $definition = new Definition('Orb\Input\Reader\Source\Superglobal', ['_GET']);
        $container->setDefinition('deskpro.core.input_reader_get', $definition);

        $definition = new Definition('Orb\Input\Reader\Source\Superglobal', ['_COOKIE']);
        $container->setDefinition('deskpro.core.input_reader_cookie', $definition);

        // Init cleaner
        $definition = new Definition('Orb\Input\Cleaner\Cleaner');
        $container->setDefinition('deskpro.core.input_cleaner', $definition);

        // Init reader
        $definition = new Definition('Application\DeskPRO\Input\Reader', [new Reference('deskpro.core.input_cleaner')]);
        $definition->addMethodCall('addSource', ['req', new Reference('deskpro.core.input_reader_req')]);
        $definition->addMethodCall('addSource', ['post', new Reference('deskpro.core.input_reader_post')]);
        $definition->addMethodCall('addSource', ['get', new Reference('deskpro.core.input_reader_get')]);
        $definition->addMethodCall('addSource', ['cookie', new Reference('deskpro.core.input_reader_cookie')]);
        $definition->addMethodCall('setArrayStringSeparator', ['.']);
        $container->setDefinition('deskpro.core.input_reader', $definition);
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
        return 'install';
    }
}
