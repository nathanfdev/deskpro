<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

if (!defined('DP_ROOT')) {
    exit('No access');
}
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

$loader->import(__DIR__.'/config.shared.yml');
$loader->import(__DIR__.'/services.yml');
$loader->import(__DIR__.'/event_listeners.yml');

/* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/doctrine-proxies');
$container->setParameter('secret', 'irrelevant - compiler pass will override this');
$container->setParameter('locale', 'en');

####################################################################
# This config is shared between kernels (DpKernel, PortalKernel and ApiKernel)
####################################################################

// app secret (NOTE; see intall/config.php, as this is copy/pasted to that file)
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\AppSecret\AppSecret');
$container->setDefinition('app_secret', $definition);

// settings
$definition = new Definition();
$definition->setClass('Application\DeskPRO\NewSettings\SettingsResolver');
$definition->setFactoryClass('Application\DeskPRO\DependencyInjection\SystemServices\SettingsResolverService');
$definition->setFactoryMethod('create');
$definition->setArguments(
    array(
        new Reference('service_container'),
    )
);
$container->setDefinition('settings_resolver', $definition);

############################################################################
# Listeners
############################################################################

$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\SecurityHeadersResponseListener');
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
# Object Router
############################################################################

$loader->import(__DIR__.'/../../src/DeskPRO/Bundle/AppBundle/Resources/config/services/object_router.yml');

############################################################################
# Ticket Public ID Resolver
############################################################################

$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver');
$definition->setArguments(array(new Reference('settings_resolver')));
$container->setDefinition('ticket.public_id_resolver', $definition);

############################################################################
# Input
############################################################################

// Init readers
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

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments(
    array(
        new Reference('service_container'),
    )
);
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// slug listener (sets slugs on content)
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineContentSlugListener');
$definition->setArguments(array(new Reference('content_slug_manager')));
$definition->addTag('doctrine.event_subscriber');
$container->setDefinition('doctrine_listener.content_slug', $definition);
// a service to set the correct slug on a content object
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\Content\ContentSlugManager');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('content_slug_manager', $definition);

$definition = new Definition();
$definition->setClass('DeskPRO\\Bundle\\AppBundle\\Assets\\PackagesFactory');
$definition->setArguments([
    new Reference('settings_resolver'),
    new Reference('request_stack'),
    new Expression("service('deskpro.app_env').getConfig('paths.asset_paths')"),
    new Expression("{DP_ACTIVE_BUILD: service('deskpro.app_env').getAppName(), DP_ENV_ID: service('deskpro.app_env').getEnvId()}"),
]);
$container->setDefinition('assets.packages.factory', $definition);

$definition = new Definition();
$definition->setClass('Symfony\\Component\\Asset\\Packages');
$definition->setFactory([new Reference('assets.packages.factory'), 'createPackages']);
$container->setDefinition('assets.packages', $definition);

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
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\ProblemListener');
$definition->setArguments(array(new Reference('service_container')));
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.problem', $definition);

############################################################################
# Doctrine Configuration
############################################################################

$container->loadFromExtension(
    'doctrine',
    array(
        'orm' => array(
            'auto_generate_proxy_classes' => 'FILE_NOT_EXISTS',
            'default_entity_manager'      => 'default',
            'entity_managers'             => array(
                'default' => array(
                    'mappings' => array(
                        'DeskPRO' => array(
                            'type' => 'staticphp',
                        ),
                        'EmailBundle' => array(
                            'type' => 'staticphp',
                        ),
                        'AppBundle' => array(
                            'type'      => 'annotation',
                            'alias'     => 'App',
                            'is_bundle' => false,
                            'dir'       => '%kernel.root_dir%/../src/DeskPRO/Bundle/AppBundle/Entity',
                            'prefix'    => 'DeskPRO\Bundle\AppBundle\Entity',
                        ),
                    ),
                ),
            ),
        ),
        'dbal' => array(
            'default_connection' => 'default',
            'connections'        => array(
                'default'      => ['host' => 'see DbalConnectionPass'],
                'read'         => ['host' => 'see DbalConnectionPass'],
                'read_reports' => ['host' => 'see DbalConnectionPass'],
                'read_search'  => ['host' => 'see DbalConnectionPass'],
            ),
            'types' => array(
                'term_engine_term' => 'DeskPRO\Bundle\AppBundle\Doctrine\Type\TermEngineTermType',
                'dpblob'           => 'Application\\DeskPRO\\DBAL\\Types\\DpBlobType',
                'dpblob_file'      => 'Application\\DeskPRO\\DBAL\\Types\\DpBlobFileType',
                'dp_json_obj'      => 'Application\\DeskPRO\\DBAL\\Types\\DpJsonObject',
                'array'            => 'Application\\DeskPRO\\DBAL\\Types\\DpArrayType',
                'object'           => 'Application\\DeskPRO\\DBAL\\Types\\DpObjectType',
            ),
        ),
    )
);

$container->register(
    'dp.entity_listener.person_changelog',
    'Application\DeskPRO\Entity\EventListener\PersonChangeLogListener'
)->addArgument(new Reference('service_container'))->addTag('doctrine.entity_listener');

############################################################################
# Cache services
############################################################################

## NOTE: duplicated in install bundle's DI
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\SimpleArrayCache');
$definition->setArguments(array());
$container->setDefinition('cache.simple_array', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\ExpiringDoctrineCache');
$seconds_in_one_day = 86400;
$definition->setArguments(array(new Reference('doctrine.orm.default_entity_manager'), $seconds_in_one_day));
$container->setDefinition('cache.one_day_doctrine', $definition);

// make an alias so we can easily swap out the underlying adapter for a diff implementation of the same concept
$container->setAlias('cache.one_day', 'cache.one_day_doctrine');

############################################################################
# Swiftmailer Configuration
############################################################################

// swiftmailer.transport.dp_delegating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Transport\\DelegatingTransport');
$definition->setArguments(
    array(
        new Reference('swiftmailer.mailer.default.transport.eventdispatcher'),
    )
);
$container->setDefinition('swiftmailer.mailer.transport.dp_delegating', $definition);

$container->loadFromExtension(
    'swiftmailer',
    array(
        'transport' => 'dp_delegating',
    )
);

// deskpro.mail_logger
$definition = new Definition();
$definition->setClass('Orb\\Log\\Logger');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailLoggerService');
$definition->setFactoryMethod('create');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('deskpro.mail_logger', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\People\\ActivityLogger\\ActivityLogger', array(
        new Reference('doctrine.orm.entity_manager'),
    )
);
$container->setDefinition('deskpro.person_activity_logger', $definition);

$definition = new Definition(
    'Application\DeskPRO\Log\Handler\LogEventHandler',
    array(new Reference('doctrine.orm.entity_manager'))
);
$container->setDefinition('deskpro.log_handler.log_event', $definition);

$definition = new Definition('Application\DeskPRO\Monolog\Logger', array('changelog'));
$definition->addMethodCall('pushHandler', array(new Reference('deskpro.log_handler.log_event')));
$container->setDefinition('deskpro.logger.changelog', $definition);

############################################################################
# Global config and Monolog handler
############################################################################

$definition = new Definition('DeskPRO\Bundle\AppBundle\Logging\DeskproFilesystemHandler');
$definition->addArgument(new Expression("service('deskpro.app_env').getUserLogsDir()"));
$definition->addArgument(new Expression("service('deskpro.app_env').getConfig('logs.general_log_level')"));
$definition->addArgument('%kernel.name%');
$definition->addArgument('%kernel.environment%');
$container->setDefinition('monolog.handler.deskpro_filesystem', $definition);

$definition = new Definition('Monolog\Handler\FingersCrossedHandler');
$definition->addArgument(new Reference('monolog.handler.deskpro_filesystem'));
$definition->addArgument(new Expression("service('deskpro.app_env').getConfig('logs.general_log_level_threshold')"));
$container->setDefinition('monolog.handler.deskpro_fingers_crossed', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\Settings\\Settings', array(
        DP_ROOT.'/sys/config/settings.php',
        new Reference('database_connection'),
    )
);
$container->setDefinition('deskpro.core.settings', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\Groups\\GroupsReposFactory',
    array(new Reference('doctrine.orm.entity_manager'))
);
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

############################################################################
# Search
############################################################################

$definition = new Definition();
$definition->setClass('Application\DeskPRO\NewSearch\SearchEngine\SearchEngine');
$definition->setFactoryClass('Application\DeskPRO\DependencyInjection\SystemServices\SearchEngineService');
$definition->setFactoryMethod('create');
$definition->setArguments(
    array(
        new Reference('service_container'),
    )
);
$container->setDefinition('search_engine', $definition);

// deskpro.search_manager.elasticsearch
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Manager\\Elasticsearch');
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$container->setDefinition('deskpro.search_manager.elasticsearch', $definition);

// deskpro.search_manager.doctrine
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Manager\\Doctrine');
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$definition->addMethodCall('setEntityManager', array(new Reference('doctrine.orm.entity_manager')));
$definition->addMethodCall('setSettings', array(new Reference('deskpro.core.settings')));
$container->setDefinition('deskpro.search_manager.doctrine', $definition);

// deskpro.search.ticket_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\TicketToElasticaTransformer');
$container->setDefinition('deskpro.search.ticket_to_elastica_transformer', $definition);

// deskpro.search.person_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\PersonToElasticaTransformer');
$container->setDefinition('deskpro.search.person_to_elastica_transformer', $definition);

// deskpro.search.org_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\OrgToElasticaTransformer');
$container->setDefinition('deskpro.search.org_to_elastica_transformer', $definition);

// deskpro.search.chat_conversation_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\ChatToElasticaTransformer');
$container->setDefinition('deskpro.search.chat_conversation_to_elastica_transformer', $definition);

// deskpro.search.article_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\ArticleToElasticaTransformer');
$container->setDefinition('deskpro.search.article_to_elastica_transformer', $definition);

// deskpro.search.news_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\NewsToElasticaTransformer');
$container->setDefinition('deskpro.search.news_to_elastica_transformer', $definition);

// deskpro.search.download_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\DownloadToElasticaTransformer');
$container->setDefinition('deskpro.search.download_to_elastica_transformer', $definition);

// deskpro.search.feedback_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\FeedbackToElasticaTransformer');
$container->setDefinition('deskpro.search.feedback_to_elastica_transformer', $definition);

// fos_elastica.provider.prototype.orm
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Provider\\Doctrine');
$definition->setArguments(
    array(
        '',
        new Reference('fos_elastica.indexable'),
        '',
        array(),
        new Reference('doctrine'),
    )
);
$definition->setAbstract(true);
$container->setDefinition('fos_elastica.provider.prototype.orm', $definition);

############################################################################
# FOS Elastica Configuration
############################################################################

$container->loadFromExtension(
    'fos_elastica',
    array(
        'clients' => array(
            'default' => array('host' => 'DEFAULT', 'port' => 9200),
        ),
        'indexes' => array(
            'deskpro' => array(
                'settings' => array(
                    'analysis' => array(
                        'filter' => array(
                            'ngram_filter_3' => array(
                                'type'        => 'nGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol'),
                            ),
                            'edge_ngram_filter_3' => array(
                                'type'        => 'edgeNGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol'),
                            ),
                            'edge_ngram_filter_4' => array(
                                'type'        => 'edgeNGram',
                                'min_gram'    => 4,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol'),
                            ),
                            'ngram_filter_5' => array(
                                'type'        => 'nGram',
                                'min_gram'    => 5,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol'),
                            ),
                            'email_filter' => array(
                                'type'              => 'pattern_capture',
                                'preserve_original' => 1,
                                'patterns'          => array(
                                    '(\\w+)',
                                    '(\\p{L}+)',
                                    '(\\d+)',
                                    '@(.+)',
                                ),
                            ),
                            'phone_filter_leading_zero' => array(
                                'type'              => 'pattern_replace',
                                'preserve_original' => 1,
                                'pattern'           => '^(\\+\\d+)\\s+(\\d+)$',
                                'replacement'       => '$1$2 $10$2 0$2 $2',
                            ),
                            'phone_filter' => array(
                                'type'              => 'pattern_capture',
                                'preserve_original' => 0,
                                'patterns'          => array(
                                    '(\\+\\d+)',
                                    '(\\d+)',
                                ),
                            ),
                        ),
                        'analyzer' => array(
                            'title_content_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => array(
                                    'standard',
                                    'stop',
                                    'lowercase',
                                    'asciifolding',
                                    'edge_ngram_filter_4',
                                ),
                            ),
                            'text_content_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => array('standard', 'stop', 'lowercase', 'asciifolding'),
                            ),
                            'name_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'asciifolding', 'edge_ngram_filter_3'),
                            ),
                            'email_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array('lowercase', 'email_filter', 'unique'),
                            ),
                            'phone_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array('phone_filter_leading_zero', 'phone_filter', 'ngram_filter_5'),
                            ),
                        ),
                    ),
                ),
                'types' => array(
                    'article' => array(
                        'mappings' => array(
                            'title'        => array('analyzer' => 'title_content_analyzer'),
                            'content'      => array('analyzer' => 'text_content_analyzer'),
                            'status'       => array(),
                            'category_ids' => array('type' => 'integer'),
                            'labels'       => array('analyzer' => 'title_content_analyzer'),
                            'sticky_words' => array(),
                            'date_created' => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Article',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.article_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\ArticleRepository',
                        ),
                    ),
                    'news' => array(
                        'mappings' => array(
                            'title'        => array('analyzer' => 'title_content_analyzer'),
                            'labels'       => array('analyzer' => 'title_content_analyzer'),
                            'sticky_words' => array(),
                            'content'      => array('analyzer' => 'text_content_analyzer'),
                            'status'       => array(),
                            'category_id'  => array('type' => 'integer'),
                            'date_created' => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\News',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.news_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\NewsRepository',
                        ),
                    ),
                    'download' => array(
                        'mappings' => array(
                            'title'        => array('analyzer' => 'title_content_analyzer'),
                            'labels'       => array('analyzer' => 'title_content_analyzer'),
                            'sticky_words' => array(),
                            'content'      => array('analyzer' => 'text_content_analyzer'),
                            'status'       => array(),
                            'category_id'  => array('type' => 'integer'),
                            'date_created' => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Download',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.download_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\DownloadRepository',
                        ),
                    ),
                    'feedback' => array(
                        'mappings' => array(
                            'title'        => array('analyzer' => 'title_content_analyzer'),
                            'labels'       => array('analyzer' => 'title_content_analyzer'),
                            'sticky_words' => array(),
                            'content'      => array('analyzer' => 'text_content_analyzer'),
                            'status'       => array(),
                            'category_id'  => array('type' => 'integer'),
                            'date_created' => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Feedback',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.feedback_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\FeedbackRepository',
                        ),
                    ),
                    'organization' => array(
                        'mappings' => array(
                            'name'          => array('type' => 'string', 'analyzer' => 'name_analyzer'),
                            'email_domains' => array('type' => 'string', 'analyzer' => 'email_analyzer'),
                            'labels'        => array('type' => 'string'),
                            'date_created'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'   => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Organization',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.org_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\OrganizationRepository',
                        ),
                    ),
                    'chat_conversation' => array(
                        'mappings' => array(
                            'subject'       => array(),
                            'labels'        => array(),
                            'department_id' => array('type' => 'integer'),
                            'is_agent'      => array('type' => 'boolean'),
                            'agent_id'      => array('type' => 'integer'),
                            'messages'      => array(),
                            'date_created'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'   => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\\DeskPRO\\Entity\\ChatConversation',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.chat_conversation_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\ChatConversationRepository',
                        ),
                    ),
                    'person' => array(
                        'mappings' => array(
                            'name'          => array('type' => 'string', 'analyzer' => 'name_analyzer'),
                            'first_name'    => array('type' => 'string', 'analyzer' => 'name_analyzer'),
                            'last_name'     => array('type' => 'string', 'analyzer' => 'name_analyzer'),
                            'labels'        => array('type' => 'string'),
                            'emails'        => array('type' => 'string', 'analyzer' => 'email_analyzer'),
                            'phone_numbers' => array('type' => 'string', 'analyzer' => 'phone_analyzer'),
                            'date_created'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'   => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Person',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.person_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\PersonRepository',
                        ),
                    ),
                    'ticket' => array(
                        'mappings' => array(
                            'subject'         => array(),
                            'ref'             => array(),
                            'department'      => array('type' => 'integer'),
                            'agent'           => array('type' => 'integer'),
                            'agent_team'      => array('type' => 'integer'),
                            'organization_id' => array('type' => 'integer'),
                            'person_id'       => array('type' => 'integer'),
                            'labels'          => array(),
                            'participants'    => array(),
                            'messages'        => array(),
                            'date_created'    => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'     => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Ticket',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.ticket_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\TicketRepository',
                        ),
                    ),
                ),
            ),
        ),
    )
);

############################################################################
# dp_enc
############################################################################
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\DpEnc');
$definition->setFactoryClass('Application\\DeskPRO\\Encryption\\StandardEncFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('dp_enc', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncTextType');
$definition->setArguments(array(new Reference('dp_enc')));
$definition->addTag('form.type', array('alias' => 'dp_enc_text'));
$container->setDefinition('dp_enc.form.type.dp_enc_text', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncPasswordType');
$definition->setArguments(array(new Reference('dp_enc')));
$definition->addTag('form.type', array('alias' => 'dp_enc_password'));
$container->setDefinition('dp_enc.form.type.dp_enc_password', $definition);

############################################################################
# DeskPRO Configuration
############################################################################

$container->loadFromExtension('deskpro_search', array());
