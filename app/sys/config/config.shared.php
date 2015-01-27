<?php if (!defined('DP_ROOT')) exit('No access');
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%../doctrine-proxies');
$container->setParameter('doctrine.orm.entity_manager.class', 'Application\\DeskPRO\\ORM\\EntityManager');
$container->setParameter('secret', "irrelevant - compiler pass will override this");
$container->setParameter('locale', 'en');

####################################################################
# This config is shared between kernels (DpKernel and PortalKernel)
####################################################################

// app secret
$definition = new Definition();
$definition->setClass('Application\AppBundle\AppSecret\AppSecret');
$container->setDefinition('app_secret', $definition);

// settings
$definition = new Definition();
$definition->setClass('Application\DeskPRO\NewSettings\SettingsResolver');
$definition->setFactoryClass('Application\DeskPRO\DependencyInjection\SystemServices\SettingsResolverService');
$definition->setFactoryMethod('create');
$definition->setArguments(array(
        new Reference('service_container')
    )
);
$container->setDefinition('settings_resolver', $definition);

// swiftmailer.mailer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Mailer');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailerFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(
    array(
        new Reference('service_container')
    )
);
$container->setDefinition('swiftmailer.mailer', $definition);

// a service to set the correct slug on a content object
$definition = new Definition();
$definition->setClass('Application\AppBundle\Service\ContentSlugManager');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('content_slug_manager', $definition);

############################################################################
# Listeners
############################################################################

$definition = new Definition();
$definition->setClass('Application\AppBundle\EventListener\SecurityHeadersResponseListener');
$definition->addTag('kernel.event_subscriber');
$container->setDefinition('listener.security_headers', $definition);


############################################################################
# Form Type
############################################################################

$definition = new Definition();
$definition->setClass('Application\DeskPRO\Form\Type\CleanerExtension');
$definition->setArguments(array(new Reference('deskpro.core.input_cleaner')));
$definition->addTag('form.type_extension', array('alias' => 'form'));
$container->setDefinition('form.cleaner_extension', $definition);

############################################################################
# Input
############################################################################

// Init readers
$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_REQUEST', array('accept_json_post' => true)));
$container->setDefinition('deskpro.core.input_reader_req', $definition);

$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_POST', array('accept_json_post' => true)));
$container->setDefinition('deskpro.core.input_reader_post', $definition);

$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_GET'));
$container->setDefinition('deskpro.core.input_reader_get', $definition);

$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_COOKIE'));
$container->setDefinition('deskpro.core.input_reader_cookie', $definition);

// Cleaner plugin: XssCleaner
$definition = new Definition('Orb\Input\Cleaner\CleanerPlugin\BasicXss');
$container->setDefinition('deskpro.core.input_cleaner_plugin_xss', $definition);

// Cleaner plugin: HTML Purifier
$definition = new Definition('Orb\Input\Cleaner\CleanerPlugin\HtmlPurifier');
$container->setDefinition('deskpro.core.input_cleaner_plugin_html_purifier', $definition);

// Init cleaner
$definition = new Definition('Orb\Input\Cleaner\Cleaner');
$definition->addMethodCall('addCleaner', array(new Reference('deskpro.core.input_cleaner_plugin_xss')));
$definition->addMethodCall('addCleaner', array(new Reference('deskpro.core.input_cleaner_plugin_html_purifier')));
$container->setDefinition('deskpro.core.input_cleaner', $definition);

// Init reader
$definition = new Definition('Application\DeskPRO\Input\Reader', array(new Reference('deskpro.core.input_cleaner')));
$definition->addMethodCall('addSource', array('req', new Reference('deskpro.core.input_reader_req')));
$definition->addMethodCall('addSource', array('post', new Reference('deskpro.core.input_reader_post')));
$definition->addMethodCall('addSource', array('get', new Reference('deskpro.core.input_reader_get')));
$definition->addMethodCall('addSource', array('cookie', new Reference('deskpro.core.input_reader_cookie')));
$definition->addMethodCall('setArrayStringSeparator', array('.'));
$container->setDefinition('deskpro.core.input_reader', $definition);



############################################################################
# Doctrine services
############################################################################

// doctrine.dbal.connection_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\DBAL\\ConnectionFactory');
$definition->setArguments(
    array(
        '%doctrine.dbal.connection_factory.types%'
    )
);
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$container->setDefinition('doctrine.dbal.connection_factory', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments(
    array(
        new Reference('service_container')
    )
);
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// slug listener (sets slugs on content)
$definition = new Definition();
$definition->setClass('Application\AppBundle\EventListener\DoctrineContentSlugListener');
$definition->setArguments(array(new Reference('content_slug_manager')));
$definition->addTag('doctrine.event_subscriber');
$container->setDefinition('doctrine_listener.content_slug', $definition);

// doctrine.orm.default_query_cache
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\ArrayFileCache');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\ArrayFileCacheFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(array('dql'));
$definition->addMethodCall('registerShutdownCommit');
$container->setDefinition('doctrine.orm.default_query_cache', $definition);

// entity listeners
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonChangeLogListener');
$definition->setArguments(array(new Reference('service_container')));
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_changelog', $definition);
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonContactDataChangeLogListener');
$definition->setArguments(array(new Reference('service_container')));
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_contact_data_changelog', $definition);
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonCustomDataChangeLogListener');
$definition->setArguments(array(new Reference('service_container')));
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_custo_data_changelog', $definition);

############################################################################
# Doctrine Configuration
############################################################################

$container->loadFromExtension(
    'doctrine', array(
        'orm'  => array(
            'auto_generate_proxy_classes' => false,
            'default_entity_manager'      => 'default',
            'entity_managers'             => array(
                'default' => array(
                    'mappings'                    => array('DeskPRO' => array('type' => 'staticphp')),
                    'class_metadata_factory_name' => 'Orb\\Doctrine\\ORM\\Mapping\\StaticClassMetadataFactory'
                )
            )
        ),
        'dbal' => array(
            'default_connection' => 'default',
            'connections'        => array(
                'default' => array('host' => 'from_user_config.db', 'logging' => true),
                'read'    => array('host' => 'from_user_config.db_read', 'logging' => true)
            )
        )
    )
);

############################################################################
# Cache services
############################################################################

## NOTE: duplicated in install bundle's DI
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\SimpleArrayCache');
$definition->setArguments(array());
$container->setDefinition('cache.simple_array', $definition);


############################################################################
# Swiftmailer Configuration
############################################################################

// swiftmailer.transport.dp_delegating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Transport\\DelegatingTransport');
$definition->setArguments(
    array(
        new Reference('swiftmailer.mailer.default.transport.eventdispatcher')
    )
);
$container->setDefinition('swiftmailer.mailer.transport.dp_delegating', $definition);

$container->loadFromExtension(
    'swiftmailer', array(
        'transport' => 'dp_delegating'
    )
);


// deskpro.mail_logger
$definition = new Definition();
$definition->setClass('Orb\\Log\\Logger');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailLoggerService');
$definition->setFactoryMethod('create');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('deskpro.mail_logger', $definition);


$definition = new Definition('Application\\DeskPRO\\People\\ActivityLogger\\ActivityLogger', array(
    new Reference('doctrine.orm.entity_manager')
));
$container->setDefinition('deskpro.person_activity_logger', $definition);

$definition = new Definition('Application\DeskPRO\Log\Handler\LogEventHandler', array(new Reference('doctrine.orm.entity_manager')));
$container->setDefinition('deskpro.log_handler.log_event', $definition);

$definition = new Definition('Application\DeskPRO\Monolog\Logger', array('changelog'));
$definition->addMethodCall('pushHandler', array(new Reference('deskpro.log_handler.log_event')));
$container->setDefinition('deskpro.logger.changelog', $definition);


$definition = new Definition('Application\\DeskPRO\\Settings\\Settings', array(
    DP_ROOT.'/sys/config/settings.php',
    new Reference('database_connection')
));
$container->setDefinition('deskpro.core.settings', $definition);


$definition = new Definition('Application\\DeskPRO\\Groups\\GroupsReposFactory', array(new Reference('doctrine.orm.entity_manager')));
$definition->setFactoryClass('Application\\DeskPRO\\Groups\\GroupsReposFactory');
$definition->setFactoryMethod('createFromEntityManager');
$container->setDefinition('deskpro.people.groups_repos_factory', $definition);

$definition = new Definition('Application\\DeskPRO\\People\\AgentGroups');
$definition->setFactoryService('deskpro.people.groups_repos_factory');
$definition->setFactoryMethod('createAgentGroups');
$container->setDefinition('deskpro.people.agent_groups', $definition);

$definition = new Definition('Application\\DeskPRO\\People\\UserGroups');
$definition->setFactoryService('deskpro.people.groups_repos_factory');
$definition->setFactoryMethod('createUserGroups');
$container->setDefinition('deskpro.people.user_groups', $definition);


$container
    ->register('dp.custom_fields.manager', 'Application\DeskPRO\Service\CustomFieldManager')
    ->addArgument(new Reference('doctrine.orm.entity_manager'))
    ->addArgument(new Reference('form.factory'));
