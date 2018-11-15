<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Application\DeskPRO\Service\JIRA;
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
        $definition = new Definition('Application\\DeskPRO\\StaticLoader\\SystemEvents');
        $definition->addArgument(new Reference('event_dispatcher'));
        $container->setDefinition('deskpro.sys_events_loader', $definition);

        $definition = new Definition('Symfony\\Component\\HttpFoundation\\Response');
        $container->setDefinition('response', $definition);

        $definition = new Definition('Application\\DeskPRO\\CacheInvalidator\\QueryListener');
        $container->setDefinition('deskpro.cache.query_listener', $definition);

        $definition = new Definition('Application\\DeskPRO\\People\\ActivityLogger\\ActivityLogger', [
            new Reference('doctrine.orm.entity_manager'),
        ]);
        $container->setDefinition('deskpro.person_activity_logger', $definition);

        $definition = new Definition('Application\DeskPRO\Log\Handler\LogEventHandler', [new Reference('doctrine.orm.entity_manager')]);
        $container->setDefinition('deskpro.log_handler.log_event', $definition);

        $definition = new Definition('Application\DeskPRO\Monolog\Logger', ['changelog']);
        $definition->addMethodCall('pushHandler', [new Reference('deskpro.log_handler.log_event')]);
        $container->setDefinition('deskpro.logger.changelog', $definition);

        $container
            ->register('dp.custom_fields.manager', 'Application\DeskPRO\Service\CustomFieldManager')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('form.factory'));

        $container->register(JIRA::NAME, 'Application\DeskPRO\Service\JIRA')->addArgument(new Reference('service_container'));

        $definition = new Definition();
        $definition->setClass('Application\DeskPRO\Form\Type\CleanerExtension');
        $definition->setArguments([new Reference('deskpro.core.input_cleaner')]);
        $definition->addTag('form.type_extension', ['alias' => 'form']);
        $container->setDefinition('form.cleaner_extension', $definition);

        $this->loadPeople($container);
        $this->loadTranslation($container);
        $this->loadSettings($container);
        $this->loadEntityListeners($container);
    }

    protected function loadPeople(ContainerBuilder $container)
    {
        $definition = new Definition('Application\\DeskPRO\\Groups\\GroupsReposFactory', [new Reference('doctrine.orm.entity_manager')]);
        $definition->setFactory('Application\\DeskPRO\\Groups\\GroupsReposFactory::createFromEntityManager');
        $container->setDefinition('deskpro.people.groups_repos_factory', $definition);

        $definition = new Definition('Application\\DeskPRO\\People\\AgentGroups');
        $definition->setFactory([new Reference('deskpro.people.groups_repos_factory'), 'createAgentGroups']);
        $container->setDefinition('deskpro.people.agent_groups', $definition);

        $definition = new Definition('Application\\DeskPRO\\People\\UserGroups');
        $definition->setFactory([new Reference('deskpro.people.groups_repos_factory'), 'createUserGroups']);
        $container->setDefinition('deskpro.people.user_groups', $definition);
    }

    /**
     * Sets up the translater.
     */
    protected function loadTranslation(ContainerBuilder $container)
    {
        $definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\SystemLoader', [[
            DP_ROOT.'/locales',
        ]]);
        $container->setDefinition('deskpro.core.translate_loader_system', $definition);

        $definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\DbLoader', [
            new Reference('database_connection'),
        ]);
        $container->setDefinition('deskpro.core.translate_loader_db', $definition);

        $definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\DeskproLoader');
        $definition->addMethodCall('setSystemLoader', [new Reference('deskpro.core.translate_loader_system')]);
        $definition->addMethodCall('setDbLoader', [new Reference('deskpro.core.translate_loader_db')]);
        $container->setDefinition('deskpro.core.translate_loader', $definition);

        // Now create the translate object
        $definition = new Definition('Application\\DeskPRO\\Translate\\Translate', [
            new Reference('deskpro.core.translate_loader'),
            new Reference('event_dispatcher'),
        ]);
        $definition->addMethodCall('setSession', [new Reference('session')]);
        $container->setDefinition('deskpro.core.translate', $definition);

        // Attach listener for no phrase
        $definition = $container->getDefinition('deskpro.sys_events_loader');
        $definition->addMethodCall('addNoPhraseEventListener');

        $definition = new Definition('Application\\DeskPRO\\People\\ActivityLogger\\EventListener', [new Reference('service_container')]);
        $definition->addTag('doctrine.event_subscriber');
        $container->setDefinition('deskpro.orm.event_listener.activity_stream', $definition);
    }

    /**
     * Sets up the input reader.
     */
    protected function loadInputReader(ContainerBuilder $container)
    {
        // this is now done in config.shared.php
    }

    /**
     * Sets up entity listeners.
     */
    protected function loadEntityListeners(ContainerBuilder $container)
    {
        $container
            ->register(
                'dp.entity_listener.person_contact_data_changelog',
                'Application\DeskPRO\Entity\EventListener\PersonContactDataChangeLogListener')
            ->addArgument(new Reference('service_container'))
            ->addTag('doctrine.entity_listener');

        $container
            ->register(
                'dp.entity_listener.person_custom_data_changelog',
                'Application\DeskPRO\Entity\EventListener\PersonCustomDataChangeLogListener')
            ->addArgument(new Reference('service_container'))
            ->addTag('doctrine.entity_listener');
    }

    /**
     * Sets up the settings loader.
     */
    protected function loadSettings(ContainerBuilder $container)
    {
        $definition = new Definition('Application\\DeskPRO\\Settings\\Settings', [
            DP_ROOT.'/sys/config/settings.php',
            new Reference('database_connection'),
        ]);
        $container->setDefinition('deskpro.core.settings', $definition);
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
        return 'deskpro_core';
    }
}
