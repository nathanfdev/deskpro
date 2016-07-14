<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        ## NOTE: duplicated in config.php
        $definition = new Definition();
        $definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\SimpleArrayCache');
        $definition->setArguments(array());
        $container->setDefinition('cache.simple_array', $definition);

        $definition = new Definition('Application\\DeskPRO\\Settings\\Settings', array(
            DP_ROOT.'/sys/config/settings.php',
            new Reference('database_connection'),
        ));
        $container->setDefinition('deskpro.core.settings', $definition);

        $definition = new Definition('Application\\DeskPRO\\Search\\Adapter\\AbstractAdapter');
        $definition->setFactory('Application\\DeskPRO\\StaticLoader\\SearchAdapter::getSearchAdapter');
        $container->setDefinition('deskpro.search_adapter', $definition);

        // slug listener (sets slugs on content)
        // note this is a duplicate for install (canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineContentSlugListener');
        $definition->setArguments(array(new Reference('content_slug_manager')));
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('doctrine_listener.content_slug', $definition);
        // slug manager (note duplicate: canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\Content\ContentSlugManager');
        $definition->setArguments(array(new Reference('service_container')));
        $container->setDefinition('content_slug_manager', $definition);

        // slug listener (sets slugs on categories)
        // note this is a duplicate for install (canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineCategorySlugListener');
        $definition->setArguments(array(new Reference('category_slug_manager')));
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('doctrine_listener.category_slug', $definition);
        // slug manager (note duplicate: canonical definition is in config.shared.php)
        $definition = new Definition();
        $definition->setClass('DeskPRO\Bundle\AppBundle\Content\CategorySlugManager');
        $definition->setArguments(array(new Reference('service_container')));
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
            array('_REQUEST', array('accept_json_post' => true), $request_stack_reference)
        );
        $container->setDefinition('deskpro.core.input_reader_req', $definition);

        $definition = new Definition(
            'Orb\Input\Reader\Source\Superglobal',
            array('_POST', array('accept_json_post' => true), $request_stack_reference)
        );
        $container->setDefinition('deskpro.core.input_reader_post', $definition);

        $definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_GET'));
        $container->setDefinition('deskpro.core.input_reader_get', $definition);

        $definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_COOKIE'));
        $container->setDefinition('deskpro.core.input_reader_cookie', $definition);

        // Init cleaner
        $definition = new Definition('Orb\Input\Cleaner\Cleaner');
        $container->setDefinition('deskpro.core.input_cleaner', $definition);

        // Init reader
        $definition = new Definition('Application\DeskPRO\Input\Reader', array(new Reference('deskpro.core.input_cleaner')));
        $definition->addMethodCall('addSource', array('req', new Reference('deskpro.core.input_reader_req')));
        $definition->addMethodCall('addSource', array('post', new Reference('deskpro.core.input_reader_post')));
        $definition->addMethodCall('addSource', array('get', new Reference('deskpro.core.input_reader_get')));
        $definition->addMethodCall('addSource', array('cookie', new Reference('deskpro.core.input_reader_cookie')));
        $definition->addMethodCall('setArrayStringSeparator', array('.'));
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
