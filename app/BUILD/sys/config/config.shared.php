<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

$loader->import(__DIR__.'/config.shared.yml');
$loader->import(__DIR__.'/config.software_services.yml');
$loader->import(__DIR__.'/services.yml');
$loader->import(__DIR__.'/event_listeners.yml');

/* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/doctrine-proxies');
$container->setParameter('secret', 'irrelevant - compiler pass will override this');
$container->setParameter('locale', 'en');

//###################################################################
// This config is shared between kernels (DpKernel, PortalKernel and ApiKernel)
//###################################################################

// app secret (NOTE; see intall/config.php, as this is copy/pasted to that file)
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\AppSecret\AppSecret');
$container->setDefinition('app_secret', $definition);

// settings
$definition = new Definition();
$definition->setClass('Application\DeskPRO\NewSettings\SettingsResolver');
$definition->setFactory('Application\DeskPRO\DependencyInjection\SystemServices\SettingsResolverService::create');
$definition->setArguments(
    [
        new Reference('service_container'),
    ]
);
$container->setDefinition('settings_resolver', $definition);

//###########################################################################
// Listeners
//###########################################################################

$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\SecurityHeadersResponseListener');
$definition->setArguments([new Reference('service_container')]);
$definition->addTag('kernel.event_subscriber');
$container->setDefinition('listener.security_headers', $definition);

$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\RequestIdResponseListener');
$definition->addTag('kernel.event_subscriber');
$container->setDefinition('listener.response_id_header', $definition);

//###########################################################################
// Form Type
//###########################################################################

$definition = new Definition();
$definition->setClass('Application\DeskPRO\Form\Type\CleanerExtension');
$definition->setArguments([new Reference('deskpro.core.input_cleaner')]);
$definition->addTag('form.type_extension', ['extended_type' => 'Symfony\Component\Form\Extension\Core\Type\FormType']);
$container->setDefinition('form.cleaner_extension', $definition);

//###########################################################################
// Object Router
//###########################################################################

$loader->import(__DIR__.'/../../src/DeskPRO/Bundle/AppBundle/Resources/config/services/object_router.yml');

//###########################################################################
// Ticket Public ID Resolver
//###########################################################################

$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver');
$definition->setArguments([new Reference('settings_resolver')]);
$container->setDefinition('ticket.public_id_resolver', $definition);

//###########################################################################
// Input
//###########################################################################

// Init readers
$requestStackReference = new Reference('request_stack');

$definition = new Definition(
    'Orb\Input\Reader\Source\Superglobal',
    ['_REQUEST', ['accept_json_post' => true], $requestStackReference]
);
$container->setDefinition('deskpro.core.input_reader_req', $definition);

$definition = new Definition(
    'Orb\Input\Reader\Source\Superglobal',
    ['_POST', ['accept_json_post' => true], $requestStackReference]
);
$container->setDefinition('deskpro.core.input_reader_post', $definition);

$definition = new Definition('Orb\Input\Reader\Source\Superglobal', ['_GET']);
$container->setDefinition('deskpro.core.input_reader_get', $definition);

$definition = new Definition('Orb\Input\Reader\Source\Superglobal', ['_COOKIE']);
$container->setDefinition('deskpro.core.input_reader_cookie', $definition);

// Cleaner plugin: XssCleaner
$definition = new Definition('Orb\Input\Cleaner\CleanerPlugin\BasicXss');
$container->setDefinition('deskpro.core.input_cleaner_plugin_xss', $definition);

// Cleaner plugin: HTML Purifier
$definition = new Definition('Orb\Input\Cleaner\CleanerPlugin\HtmlPurifier');
$container->setDefinition('deskpro.core.input_cleaner_plugin_html_purifier', $definition);

// Init cleaner
$definition = new Definition('Orb\Input\Cleaner\Cleaner');
$definition->addMethodCall('addCleaner', [new Reference('deskpro.core.input_cleaner_plugin_xss')]);
$definition->addMethodCall('addCleaner', [new Reference('deskpro.core.input_cleaner_plugin_html_purifier')]);
$container->setDefinition('deskpro.core.input_cleaner', $definition);

// Init reader
$definition = new Definition('Application\DeskPRO\Input\Reader', [new Reference('deskpro.core.input_cleaner')]);
$definition->addMethodCall('addSource', ['req', new Reference('deskpro.core.input_reader_req')]);
$definition->addMethodCall('addSource', ['post', new Reference('deskpro.core.input_reader_post')]);
$definition->addMethodCall('addSource', ['get', new Reference('deskpro.core.input_reader_get')]);
$definition->addMethodCall('addSource', ['cookie', new Reference('deskpro.core.input_reader_cookie')]);
$definition->addMethodCall('setArrayStringSeparator', ['.']);
$container->setDefinition('deskpro.core.input_reader', $definition);

//###########################################################################
// Legacy reports services
//###########################################################################

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Reports\\ReportsWidgetService');
$definition->setArguments([new Reference('doctrine.orm.entity_manager')]);
$container->setDefinition('reports.widget.service', $definition);

//###########################################################################
// Doctrine services
//###########################################################################

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments(
    [
        new Reference('service_container'),
    ]
);
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// slug listener (sets slugs on content)
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineContentSlugListener');
$definition->setArguments([new Reference('content_slug_manager')]);
$definition->addTag('doctrine.event_subscriber');
$container->setDefinition('doctrine_listener.content_slug', $definition);
// a service to set the correct slug on a content object
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\Content\ContentSlugManager');
$definition->setArguments([new Reference('service_container')]);
$container->setDefinition('content_slug_manager', $definition);

// slug listener (sets slugs on category)
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Content\DoctrineCategorySlugListener');
$definition->setArguments([new Reference('category_slug_manager')]);
$definition->addTag('doctrine.event_subscriber');
$container->setDefinition('doctrine_listener.category_slug', $definition);
// a service to set the correct slug on a category object
// NOTE: this is duplicated in the InstallExtension so that the install process can use it
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\Content\CategorySlugManager');
$definition->setArguments([new Reference('service_container')]);
$container->setDefinition('category_slug_manager', $definition);

$definition = new Definition();
$definition->setClass('DeskPRO\\Bundle\\AppBundle\\Assets\\PackagesFactory');
$definition->setArguments([
    new Reference('deskpro.app_env'),
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
$definition->setFactory('Application\\DeskPRO\\DependencyInjection\\SystemServices\\ArrayFileCacheFactory::create');
$definition->setArguments(['dql']);
$container->setDefinition('doctrine.orm.default_query_cache', $definition);

// entity listeners
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonChangeLogListener');
$definition->setArguments([new Reference('service_container')]);
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_changelog', $definition);
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonContactDataChangeLogListener');
$definition->setArguments([new Reference('service_container')]);
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_contact_data_changelog', $definition);
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\PersonCustomDataChangeLogListener');
$definition->setArguments([new Reference('service_container')]);
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_custo_data_changelog', $definition);
$definition = new Definition();
$definition->setClass('Application\DeskPRO\Entity\EventListener\ProblemListener');
$definition->setArguments([new Reference('service_container')]);
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.problem', $definition);
$definition = new Definition();
$definition->setClass('DeskPRO\Bundle\AppBundle\EventListener\Person\PersonOnboardingListener');
$definition->addTag('doctrine.entity_listener');
$container->setDefinition('dp.entity_lister.person_onboarding', $definition);

//###########################################################################
// Doctrine Configuration
//###########################################################################

$container->loadFromExtension(
    'doctrine',
    [
        'orm' => [
            'auto_generate_proxy_classes' => 'FILE_NOT_EXISTS',
            'default_entity_manager'      => 'default',

            'entity_managers' => [

                'default' => [
                    'metadata_cache_driver' => ['type' => 'service', 'cache_provider' => 'metadata_cache'],
                    'mappings'              => [

                        'DeskPRO' => [
                            'type' => 'staticphp',
                        ],

                        'EmailBundle' => [
                            'type' => 'staticphp',
                        ],

                        'AppBundle' => [
                            'type'      => 'annotation',
                            'is_bundle' => false,
                            'dir'       => '%kernel.root_dir%/../src/DeskPRO/Bundle/AppBundle/Entity',
                            'prefix'    => 'DeskPRO\Bundle\AppBundle\Entity',
                        ],
                    ],
                ],

                'system' => [
                    'metadata_cache_driver' => ['type' => 'service', 'cache_provider' => 'metadata_cache'],
                    'connection'            => 'system',
                    'mappings'              => [
                        'SystemBundle' => null,
                    ],
                ],

                'audit' => [
                    'metadata_cache_driver' => ['type' => 'service', 'cache_provider' => 'metadata_cache'],
                    'connection'            => 'audit',
                    'mappings'              => [
                        'AuditBundle' => [
                            'type'      => 'annotation',
                            'is_bundle' => false,
                            'dir'       => '%kernel.root_dir%/../src/DeskPRO/Bundle/AuditBundle/Entity',
                            'prefix'    => 'DeskPRO\Bundle\AuditBundle\Entity',
                        ],
                    ],
                ],

                'voice' => [
                    'metadata_cache_driver' => ['type' => 'service', 'cache_provider' => 'metadata_cache'],
                    'connection'            => 'voice',
                    'mappings'              => [
                        'VoiceBundle' => [
                            'type'      => 'annotation',
                            'is_bundle' => false,
                            'dir'       => '%kernel.root_dir%/../src/DeskPRO/Bundle/VoiceBundle/Entity',
                            'prefix'    => 'DeskPRO\Bundle\VoiceBundle\Entity',
                        ],
                    ],
                ],

            ],
        ],

        'dbal' => [
            'default_connection' => 'default',

            'connections' => [
                'default'      => ['host' => 'see DbalConnectionPass'],
                'system'       => ['host' => 'see DbalConnectionPass'],
                'audit'        => ['host' => 'see DbalConnectionPass'],
                'voice'        => ['host' => 'see DbalConnectionPass'],
                'read'         => ['host' => 'see DbalConnectionPass'],
                'read_reports' => ['host' => 'see DbalConnectionPass'],
                'read_search'  => ['host' => 'see DbalConnectionPass'],
            ],

            'types' => [
                'dpblob'      => 'Application\\DeskPRO\\DBAL\\Types\\DpBlobType',
                'dpblob_file' => 'Application\\DeskPRO\\DBAL\\Types\\DpBlobFileType',
                'dp_json_obj' => 'Application\\DeskPRO\\DBAL\\Types\\DpJsonObject',
                'array'       => 'Application\\DeskPRO\\DBAL\\Types\\DpArrayType',
                'object'      => 'Application\\DeskPRO\\DBAL\\Types\\DpObjectType',
            ],
        ],
    ]
);

$container->register(
    'dp.entity_listener.person_changelog',
    'Application\DeskPRO\Entity\EventListener\PersonChangeLogListener'
)->addArgument(new Reference('service_container'))->addTag('doctrine.entity_listener');

//###########################################################################
// Cache services
//###########################################################################

//# NOTE: duplicated in install bundle's DI
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\SimpleArrayCache');
$definition->setArguments([]);
$container->setDefinition('cache.simple_array', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Cache\\Adapter\\ExpiringDoctrineCache');
$secondsInOneDay = 86400;
$definition->setArguments([new Reference('doctrine.orm.default_entity_manager'), $secondsInOneDay]);
$container->setDefinition('cache.one_day_doctrine', $definition);

// make an alias so we can easily swap out the underlying adapter for a diff implementation of the same concept
$container->setAlias('cache.one_day', 'cache.one_day_doctrine');

//###########################################################################
// Swiftmailer Configuration
//###########################################################################

// swiftmailer.transport.dp_delegating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Transport\\DelegatingTransport');
$definition->setArguments(
    [
        new Reference('swiftmailer.mailer.default.transport.eventdispatcher'),
    ]
);
$container->setDefinition('swiftmailer.mailer.transport.dp_delegating', $definition);

$container->loadFromExtension(
    'swiftmailer',
    [
        'transport' => 'dp_delegating',
    ]
);

// deskpro.mail_logger
$definition = new Definition();
$definition->setClass('Orb\\Log\\Logger');
$definition->setFactory('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailLoggerService::create');
$definition->setArguments([new Reference('service_container')]);
$container->setDefinition('deskpro.mail_logger', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\People\\ActivityLogger\\ActivityLogger', [
        new Reference('doctrine.orm.entity_manager'),
    ]
);
$container->setDefinition('deskpro.person_activity_logger', $definition);

$definition = new Definition('Application\\DeskPRO\\People\\ActivityLogger\\EventListener', [new Reference('service_container')]);
$definition->addTag('doctrine.event_subscriber');
$container->setDefinition('deskpro.orm.event_listener.activity_stream', $definition);

$definition = new Definition(
    'Application\DeskPRO\Log\Handler\LogEventHandler',
    [new Reference('doctrine.orm.entity_manager')]
);
$container->setDefinition('deskpro.log_handler.log_event', $definition);

$definition = new Definition('Application\DeskPRO\Monolog\Logger', ['changelog']);
$definition->addMethodCall('pushHandler', [new Reference('deskpro.log_handler.log_event')]);
$container->setDefinition('deskpro.logger.changelog', $definition);

//###########################################################################
// Global config and Monolog handler
//###########################################################################

$definition = new Definition('DeskPRO\Bundle\AppBundle\Logging\DeskproFilesystemHandler');
$definition->addArgument(new Expression("service('deskpro.app_env').getUserLogsDir()"));
$definition->addArgument(new Expression("service('deskpro.app_env').getConfig('logs.log_level')"));
$definition->addArgument('%kernel.name%');
$definition->addArgument('%kernel.environment%');
$container->setDefinition('monolog.handler.deskpro_filesystem', $definition);

$definition = new Definition('Monolog\Handler\FingersCrossedHandler');
$definition->addArgument(new Reference('monolog.handler.deskpro_filesystem'));
$definition->addArgument(new Expression("service('deskpro.app_env').getConfig('logs.log_level_threshold')"));
$container->setDefinition('monolog.handler.deskpro_fingers_crossed', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\Settings\\Settings', [
        DP_ROOT.'/sys/config/settings.php',
        new Reference('database_connection'),
    ]
);
$container->setDefinition('deskpro.core.settings', $definition);

$definition = new Definition(
    'Application\\DeskPRO\\Groups\\GroupsReposFactory',
    [new Reference('doctrine.orm.entity_manager')]
);
$definition->setFactory('Application\\DeskPRO\\Groups\\GroupsReposFactory::createFromEntityManager');
$container->setDefinition('deskpro.people.groups_repos_factory', $definition);

$definition = new Definition('Application\\DeskPRO\\People\\AgentGroups');
$definition->setFactory([new Reference('deskpro.people.groups_repos_factory'), 'createAgentGroups']);
$container->setDefinition('deskpro.people.agent_groups', $definition);

$definition = new Definition('Application\\DeskPRO\\People\\UserGroups');
$definition->setFactory([new Reference('deskpro.people.groups_repos_factory'), 'createUserGroups']);
$container->setDefinition('deskpro.people.user_groups', $definition);

$container
    ->register('dp.custom_fields.manager', 'Application\DeskPRO\Service\CustomFieldManager')
    ->addArgument(new Reference('doctrine.orm.entity_manager'))
    ->addArgument(new Reference('form.factory'));

//###########################################################################
// Search
//###########################################################################

$definition = new Definition();
$definition->setClass('Application\DeskPRO\NewSearch\SearchEngine\SearchEngine');
$definition->setFactory('Application\DeskPRO\DependencyInjection\SystemServices\SearchEngineService::create');
$definition->setArguments(
    [
        new Reference('service_container'),
    ]
);
$container->setDefinition('search_engine', $definition);

// deskpro.search_manager.elasticsearch
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Manager\\Elasticsearch');
$definition->addMethodCall('setContainer', [new Reference('service_container')]);
$container->setDefinition('deskpro.search_manager.elasticsearch', $definition);

// deskpro.search_manager.doctrine
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Manager\\Doctrine');
$definition->addMethodCall('setContainer', [new Reference('service_container')]);
$definition->addMethodCall('setEntityManager', [new Reference('doctrine.orm.entity_manager')]);
$definition->addMethodCall('setSettings', [new Reference('deskpro.core.settings')]);
$container->setDefinition('deskpro.search_manager.doctrine', $definition);

// deskpro.search.ticket_to_elastica_transformer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Transformer\\TicketToElasticaTransformer');
$container->setDefinition('deskpro.search.ticket_to_elastica_transformer', $definition);
$definition->addMethodCall('setApacheTika', [new Reference('deskpro.apache_tika.client_manager')]);

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

// deskpro.search.topic_to_elastica_transformer
$definition = new Definition();
$definition->setClass(\Application\DeskPRO\NewSearch\Transformer\TopicToElasticaTransformer::class);
$container->setDefinition('deskpro.search.topic_to_elastica_transformer', $definition);

// fos_elastica.provider.prototype.orm
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\NewSearch\\Provider\\Doctrine');
$definition->setArguments(
    [
        '',
        new Reference('fos_elastica.indexable'),
        '',
        [],
        new Reference('doctrine'),
    ]
);
$definition->setAbstract(true);
$container->setDefinition('fos_elastica.provider.prototype.orm', $definition);

//###########################################################################
// FOS Elastica Configuration
//###########################################################################

$container->loadFromExtension(
    'fos_elastica',
    [
        'clients' => [
            'default' => ['host' => 'DEFAULT', 'port' => 9200],
        ],
        'indexes' => [
            'deskpro' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'ngram_filter_3' => [
                                'type'        => 'nGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => ['letters', 'digit', 'punctuation', 'symbol'],
                            ],
                            'edge_ngram_filter_3' => [
                                'type'        => 'edgeNGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => ['letters', 'digit', 'punctuation', 'symbol'],
                            ],
                            'edge_ngram_filter_4' => [
                                'type'        => 'edgeNGram',
                                'min_gram'    => 4,
                                'max_gram'    => 20,
                                'token_chars' => ['letters', 'digit', 'punctuation', 'symbol'],
                            ],
                            'ngram_filter_5' => [
                                'type'        => 'nGram',
                                'min_gram'    => 5,
                                'max_gram'    => 20,
                                'token_chars' => ['letters', 'digit', 'punctuation', 'symbol'],
                            ],
                            'email_filter' => [
                                'type'              => 'pattern_capture',
                                'preserve_original' => 1,
                                'patterns'          => [
                                    '(\\w+)',
                                    '(\\p{L}+)',
                                    '(\\d+)',
                                    '@(.+)',
                                ],
                            ],
                            'phone_filter_leading_zero' => [
                                'type'              => 'pattern_replace',
                                'preserve_original' => 1,
                                'pattern'           => '^(\\+\\d+)\\s+(\\d+)$',
                                'replacement'       => '$1$2 $10$2 0$2 $2',
                            ],
                            'phone_filter' => [
                                'type'              => 'pattern_replace',
                                'preserve_original' => 1,
                                'pattern'           => '[^0-9]',
                                'replacement'       => '',
                            ],
                            'filename_filter' => [
                                'type'              => 'pattern_capture',
                                'preserve_original' => 1,
                                'patterns'          => [
                                    '([^\\._\\s]+)',
                                ],
                            ],
                        ],
                        'analyzer' => [
                            'title_content_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'standard',
                                    'lowercase',
                                    'asciifolding',
                                ],
                            ],
                            'text_content_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => ['standard', 'lowercase', 'asciifolding'],
                            ],
                            'name_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => ['lowercase', 'asciifolding', 'edge_ngram_filter_3'],
                            ],
                            'email_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => ['lowercase', 'email_filter', 'unique'],
                            ],
                            'email_domain_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => ['standard', 'lowercase', 'edge_ngram_filter_4'],
                            ],
                            'phone_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => ['phone_filter_leading_zero', 'phone_filter', 'ngram_filter_5'],
                            ],
                            'filename_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => ['lowercase', 'asciifolding', 'filename_filter'],
                            ],
                        ],
                    ],
                ],
                'types' => [
                    'article' => [
                        'mappings' => [
                            'title'        => ['analyzer' => 'title_content_analyzer'],
                            'content'      => ['analyzer' => 'text_content_analyzer'],
                            'status'       => [],
                            'category_ids' => ['type' => 'integer'],
                            'labels'       => ['analyzer' => 'title_content_analyzer'],
                            'sticky_words' => [],
                            'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Article::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.article_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\ArticleRepository::class,
                        ],
                    ],
                    'news' => [
                        'mappings' => [
                            'title'        => ['analyzer' => 'title_content_analyzer'],
                            'labels'       => ['analyzer' => 'title_content_analyzer'],
                            'sticky_words' => [],
                            'content'      => ['analyzer' => 'text_content_analyzer'],
                            'status'       => [],
                            'category_id'  => ['type' => 'integer'],
                            'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\News::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.news_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\NewsRepository::class,
                        ],
                    ],
                    'download' => [
                        'mappings' => [
                            'title'        => ['analyzer' => 'title_content_analyzer'],
                            'labels'       => ['analyzer' => 'title_content_analyzer'],
                            'sticky_words' => [],
                            'content'      => ['analyzer' => 'text_content_analyzer'],
                            'status'       => [],
                            'category_id'  => ['type' => 'integer'],
                            'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Download::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.download_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\DownloadRepository::class,
                        ],
                    ],
                    'feedback' => [
                        'mappings' => [
                            'title'        => ['analyzer' => 'title_content_analyzer'],
                            'labels'       => ['analyzer' => 'title_content_analyzer'],
                            'sticky_words' => [],
                            'content'      => ['analyzer' => 'text_content_analyzer'],
                            'status'       => [],
                            'category_id'  => ['type' => 'integer'],
                            'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Feedback::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.feedback_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\FeedbackRepository::class,
                        ],
                    ],
                    'topic' => [
                        'mappings' => [
                            'title'        => ['analyzer' => 'title_content_analyzer'],
                            'labels'       => ['analyzer' => 'title_content_analyzer'],
                            'sticky_words' => [],
                            'content'      => ['analyzer' => 'text_content_analyzer'],
                            'status'       => [],
                            'guide_id'     => ['type' => 'integer'],
                            'date_created' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Topic::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.topic_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\TopicRepository::class,
                        ],
                    ],
                    'organization' => [
                        'mappings' => [
                            'name'          => ['type' => 'string', 'analyzer' => 'name_analyzer'],
                            'email_domains' => ['type' => 'string', 'analyzer' => 'email_domain_analyzer'],
                            'labels'        => ['analyzer' => 'title_content_analyzer'],
                            'date_created'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'   => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Organization::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.org_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\OrganizationRepository::class,
                        ],
                    ],
                    'chat_conversation' => [
                        'mappings' => [
                            'subject'       => [],
                            'labels'        => ['analyzer' => 'title_content_analyzer'],
                            'department_id' => ['type' => 'integer'],
                            'is_agent'      => ['type' => 'boolean'],
                            'person'        => ['type' => 'integer'],
                            'agent'         => ['type' => 'integer'],
                            'messages'      => [],
                            'date_created'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'   => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => 'Application\\DeskPRO\\Entity\\ChatConversation',
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.chat_conversation_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\ChatConversationRepository::class,
                        ],
                    ],
                    'person' => [
                        'mappings' => [
                            'name'          => ['type' => 'string', 'analyzer' => 'name_analyzer'],
                            'first_name'    => ['type' => 'string', 'analyzer' => 'name_analyzer'],
                            'last_name'     => ['type' => 'string', 'analyzer' => 'name_analyzer'],
                            'labels'        => ['analyzer' => 'title_content_analyzer'],
                            'emails'        => ['type' => 'string', 'analyzer' => 'email_analyzer'],
                            'email_domains' => ['type' => 'string', 'analyzer' => 'email_domain_analyzer'],
                            'phone_numbers' => ['type' => 'string', 'analyzer' => 'phone_analyzer'],
                            'date_created'  => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'   => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'is_agent'      => ['type' => 'boolean'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Person::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.person_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\PersonRepository::class,
                        ],
                    ],
                    'ticket' => [
                        'mappings' => [
                            'subject'         => [],
                            'ref'             => [],
                            'department'      => ['type' => 'integer'],
                            'agent'           => ['type' => 'integer'],
                            'agent_team'      => ['type' => 'integer'],
                            'organization_id' => ['type' => 'integer'],
                            'person_id'       => ['type' => 'integer'],
                            'labels'          => ['analyzer' => 'title_content_analyzer'],
                            'participants'    => [],
                            'messages'        => [],
                            'date_created'    => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'date_active'     => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
                            'attachments'     => ['type' => 'string', 'analyzer' => 'filename_analyzer'],
                            'attachment'      => ['type' => 'nested'],
                        ],
                        'persistence' => [
                            'driver'                        => 'orm',
                            'model'                         => \Application\DeskPRO\Entity\Ticket::class,
                            'provider'                      => [],
                            'finder'                        => [],
                            'elastica_to_model_transformer' => ['ignore_missing' => true],
                            'model_to_elastica_transformer' => ['service' => 'deskpro.search.ticket_to_elastica_transformer'],
                            'repository'                    => \Application\DeskPRO\NewSearch\Repository\TicketRepository::class,
                        ],
                    ],
                ],
            ],
        ],
    ]
);

//###########################################################################
// dp_enc
//###########################################################################
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\DpEnc');
$definition->setFactory('Application\\DeskPRO\\Encryption\\StandardEncFactory::create');
$definition->setArguments([new Reference('service_container')]);
$container->setDefinition('dp_enc', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncTextType');
$definition->setArguments([new Reference('dp_enc')]);
$definition->addTag('form.type', ['alias' => 'dp_enc_text']);
$container->setDefinition('dp_enc.form.type.dp_enc_text', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Encryption\\Form\\Type\\DpEncPasswordType');
$definition->setArguments([new Reference('dp_enc')]);
$definition->addTag('form.type', ['alias' => 'dp_enc_password']);
$container->setDefinition('dp_enc.form.type.dp_enc_password', $definition);

//###########################################################################
// DeskPRO Configuration
//###########################################################################

$container->loadFromExtension('deskpro_search', []);
