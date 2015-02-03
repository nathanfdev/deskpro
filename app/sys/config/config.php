<?php if (!defined('DP_ROOT')) exit('No access');
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

############################################################################
# Parameters
############################################################################

$container->setParameter('kernel.include_core_classes', false);
$container->setParameter('http_kernel.class', 'Application\\DeskPRO\\HttpKernel\\HttpKernel');
$container->setParameter('controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\ControllerResolver');
$container->setParameter('debug.controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\TraceableControllerResolver');
$container->setParameter('session.class', 'Application\\DeskPRO\\HttpFoundation\\Session');
$container->setParameter('swiftmailer.class', 'Application\\DeskPRO\\Mail\\Mailer');
$container->setParameter('twig.loader.filesystem.class', 'Application\\DeskPRO\\Twig\\Loader\\HybridLoader');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');
$container->setParameter('file_locator.class', 'Application\\DeskPRO\\HttpKernel\\Config\\FileLocator');
$container->setParameter('routing.file_locator.class', 'Application\\DeskPRO\\HttpKernel\\Config\\FileLocator');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('templating.globals.class', 'Application\\DeskPRO\\Templating\\GlobalVariables');
$container->setParameter('templating.name_parser.class', 'Application\\DeskPRO\\Templating\\TemplateNameParser');
$container->setParameter('templating.asset.url_package.class', 'Application\\DeskPRO\\Templating\\Asset\\UrlPackage');
$container->setParameter('templating.asset.path_package.class', 'Application\\DeskPRO\\Templating\\Asset\\PathPackage');
$container->setParameter('templating.cache_warmer.template_paths.class', 'Application\\DeskPRO\\CacheWarmer\\TemplatePathsCacheWarmer');
$container->setParameter('validator.mapping.class_metadata_factory.class', 'Application\\DeskPRO\\Validator\\Mapping\\ClassMetadataFactory');
$container->setParameter('router.class', 'Application\\DeskPRO\\Routing\\Router');
$container->setParameter('router.options.generator_dumper_class', 'Application\\DeskPRO\\Routing\\Generator\\Dumper\\PhpGeneratorDumper');
$container->setParameter('router.options.matcher_dumper_class', 'Application\\DeskPRO\\Routing\\Matcher\\Dumper\\PhpMatcherDumper');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/../doctrine-proxies');
$container->setParameter('twig.options', array('cache' => '%kernel.cache_dir%/../twig-compiled', 'charset' => 'UTF-8', 'debug' => '%kernel.debug%', 'auto_reload' => '%kernel.debug%'));
$container->setParameter('doctrine.orm.entity_manager.class', 'Application\\DeskPRO\\ORM\\EntityManager');
$container->setParameter('templating.locator.class', 'Application\\DeskPRO\\Templating\\Loader\\TemplateLocator');
$container->setParameter('templating.engine.twig.class', 'Application\\DeskPRO\\Twig\\TwigEngine');
$container->setParameter('debug.templating.engine.twig.class', 'Application\\DeskPRO\\Twig\\TwigEngine');
$container->setParameter('twig.cache_warmer.class', 'Application\\DeskPRO\\Twig\\CacheWarmer\\TemplateCacheCacheWarmer');
$container->setParameter('twig.extension.trans.class', 'Application\\DeskPRO\\Twig\\Extension\\TranslationExtension');
$container->setParameter('twig.extension.routing.class', 'Application\\DeskPRO\\Twig\\Extension\\RoutingExtension');
$container->setParameter('templating.engine.delegating.class', 'Application\\DeskPRO\\Templating\\Engine');
$container->setParameter('form.type_extension.csrf.enabled', false);

############################################################################
# Services
############################################################################

// templating.engine.jsonphp
$definition = new Definition();
$definition->setClass('Orb\\Templating\\Engine\\PhpVarJsonEngine');
$definition->setArguments(array(
    new Reference('templating.name_parser'),
    new Reference('service_container'),
    new Reference('templating.loader'),
    new Reference('templating.globals'),
));
$definition->addTag('templating.engine', array('alias' => 'jsonphp'));
$container->setDefinition('templating.engine.jsonphp', $definition);

// twig.helpers.deskpro_templating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\TemplatingExtension');
$definition->setArguments(array(
    new Reference('service_container')
));
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_templating', $definition);

// twig.helpers.deskpro_user_templating
$definition = new Definition();
$definition->setClass('Application\\UserBundle\\Twig\\Extension\\UserTemplatingExtension');
$definition->setArguments(array(
    new Reference('service_container')
));
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_user_templating', $definition);

// twig.helpers.deskpro_user_templating
$definition = new Definition();
$definition->setClass('Application\\UserBundle\\Twig\\Extension\\UserTemplatingExtension');
$definition->setArguments(array(
    new Reference('service_container')
));
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_user_templating', $definition);

// session.storage
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\HttpFoundation\\SessionStorage\\SessionEntityStorage');
$definition->setArguments(array(
    new Reference('doctrine.orm.entity_manager'),
    '%session.storage.options%'
));
$container->setDefinition('session.storage', $definition);

// deskpro.mail_logger
$definition = new Definition();
$definition->setClass('Orb\\Log\\Logger');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailLoggerService');
$definition->setFactoryMethod('create');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('deskpro.mail_logger', $definition);

// swiftmailer.mailer
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Mailer');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\MailerFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(array(
    new Reference('service_container')
));
$container->setDefinition('swiftmailer.mailer', $definition);

// swiftmailer.transport.dp_delegating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Mail\\Transport\\DelegatingTransport');
$definition->setArguments(array(
    new Reference('swiftmailer.mailer.default.transport.eventdispatcher')
));
$container->setDefinition('swiftmailer.mailer.transport.dp_delegating', $definition);

// doctrine.dbal.connection_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\DBAL\\ConnectionFactory');
$definition->setArguments(array(
    '%doctrine.dbal.connection_factory.types%'
));
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$container->setDefinition('doctrine.dbal.connection_factory', $definition);

// doctrine.orm.default_query_cache
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\ArrayFileCache');
$definition->setFactoryClass('Application\\DeskPRO\\DependencyInjection\\SystemServices\\ArrayFileCacheFactory');
$definition->setFactoryMethod('create');
$definition->setArguments(array('dql'));
$definition->addMethodCall('registerShutdownCommit');
$container->setDefinition('doctrine.orm.default_query_cache', $definition);

// deskpro.exception_logger
$definition = new Definition();
$definition->setClass('Application\DeskPRO\HttpKernel\ExceptionListener');
$definition->addTag('kernel.event_listener', array('event' => 'kernel.exception', 'method' => 'onKernelException', 'priority' => -128));
$container->setDefinition('deskpro.exception_logger', $definition);

// deskpro.interface_value
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\InterfaceValue');
$container->setDefinition('deskpro.interface_value', $definition);

// doctrine.orm.default_result_cache
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\PreloadedMysqlCache');
$definition->setArguments(array(
    new Reference('database_connection')
));
$definition->addMethodCall('setPrefix', array('dres', new Reference('deskpro.interface_value')));
$container->setDefinition('default_result_cache', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments(array(
    new Reference('service_container')
));
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// browser_sniffer
$definition = new Definition();
$definition->setClass('Browser');
$container->setDefinition('browser_sniffer', $definition);

// deskpro.service_urls
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Settings\\ServiceUrls');
$definition->addMethodCall('loadPack', array('%kernel.root_dir%/config/service-urls.php'));
$container->setDefinition('deskpro.service_urls', $definition);

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
$definition->setArguments(array(
    '',
    new Reference('fos_elastica.indexable'),
    '',
    array(),
    new Reference('doctrine')
));
$definition->setAbstract(true);
$container->setDefinition('fos_elastica.provider.prototype.orm', $definition);

$definition = new Definition();
$definition->setClass('Application\\ApiBundle\\Service\\DashboardPermissions');
$definition->setArguments(array(new Reference('doctrine.orm.entity_manager')));
$container->setDefinition('dashboard.permissions.service', $definition);

$definition = new Definition();
$definition->setClass('Application\\ApiBundle\\Service\\DashboardWidget');
$definition->setArguments(array(new Reference('doctrine.orm.entity_manager')));
$container->setDefinition('dashboard.widget.service', $definition);

$definition = new Definition();
$definition->setClass('Application\\ApiBundle\\Service\\Dashboard');
$definition->setArguments(array(new Reference('doctrine.orm.entity_manager'), new Reference('dashboard.widget.service')));
$container->setDefinition('dashboard.service', $definition);


############################################################################
# Validators and Constraints
############################################################################

// deskpro.constraint_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Validator\\Constraints\\ConstraintFactory');
$definition->setArguments(array(new Reference('service_container')));
$container->setDefinition('deskpro.constraint_factory', $definition);

foreach (array(
    'Application\\DeskPRO\\Validator\\Constraints\\AgentGroupValidator',
    'Application\\DeskPRO\\Validator\\Constraints\\AgentTeamValidator',
) as $class) {
    $parts = explode('\\', $class);
    $base_name = array_pop($parts);

    $alias = $class::getAlias();

    $definition = new Definition();
    $definition->setClass($class);
    $definition->setFactoryService('deskpro.constraint_factory');
    $definition->setFactoryMethod('get' . ucfirst($base_name));
    $definition->addTag('validator.constraint_validator', array('alias' => $alias));
    $container->setDefinition('validator.deskpro.' . strtolower($alias), $definition);
}

############################################################################
# Framework Configuration
############################################################################

$container->loadFromExtension('framework', array(
    'secret' => 'mube224etsmhxky1gvwixc4b',
    'templating' => array(
        'engines' => array('twig', 'php', 'jsonphp'),
        'assets_base_urls' => 'CONFIG_HTTP'
    ),
    'validation' => array('enabled' => true, 'static_method' => array('loadValidatorMetadata'), 'api' => '2.4'),
    'session' => array(),
    'form' => array('enabled' => true),
    'router' => array(
        'resource' => DP_ROOT.'/sys/config/routing.php'
    )
));

// Monolog default logging, turn off unless specifically enabled (eg in some _dev configs)
$container->loadFromExtension('monolog', array(
    'handlers' => array(
        'main' => array(
            'type' => 'null'
        )
    )
));

############################################################################
# Twig Configuration
############################################################################

$container->loadFromExtension('twig', array(
    'form' => array(
        'resources' => array(
            'DeskPRO:Form:form_div_layout.html.twig'
        )
    )
));

############################################################################
# Doctrine Configuration
############################################################################

$container->loadFromExtension('doctrine', array(
    'orm' => array(
        'auto_generate_proxy_classes' => false,
        'default_entity_manager' => 'default',
        'entity_managers' => array(
            'default' => array('mappings' => array('DeskPRO' => array('type' => 'staticphp')), 'class_metadata_factory_name' => 'Orb\\Doctrine\\ORM\\Mapping\\StaticClassMetadataFactory')
        )
    ),
    'dbal' => array(
        'default_connection' => 'default',
        'connections' => array(
            'default' => array('host' => 'from_user_config.db', 'logging' => true),
            'read' => array('host' => 'from_user_config.db_read', 'logging' => true)
        )
    )
));

############################################################################
# Swiftmailer Configuration
############################################################################

$container->loadFromExtension('swiftmailer', array(
    'transport' => 'dp_delegating'
));

############################################################################
# FOS Elastica Configuration
############################################################################

$container->loadFromExtension(
    'fos_elastica', array(
        'clients' => array(
            'default' => array('host' => 'DEFAULT', 'port' => 9200)
        ),

        'indexes' => array(
            'deskpro' => array(
                'settings' => array(
                    'analysis' => array(
                        'filter'   => array(
                            'ngram_filter_3'  => array(
                                'type'        => 'nGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'edge_ngram_filter_3'  => array(
                                'type'        => 'edgeNGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'edge_ngram_filter_4'  => array(
                                'type'        => 'edgeNGram',
                                'min_gram'    => 4,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'ngram_filter_5'  => array(
                                'type'        => 'nGram',
                                'min_gram'    => 5,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'email_filter'    => array(
                                'type'              => 'pattern_capture',
                                'preserve_original' => 1,
                                'patterns'          => array(
                                    "(\\w+)",
                                    "(\\p{L}+)",
                                    "(\\d+)",
                                    "@(.+)"
                                )
                            ),
                            'phone_filter_leading_zero' => array(
                                'type'              => 'pattern_replace',
                                'preserve_original' => 1,
                                'pattern'           => '^(\\+\\d+)\\s+(\\d+)$',
                                'replacement'       => '$1$2 $10$2 0$2 $2'
                            ),
                            'phone_filter'              => array(
                                'type'              => 'pattern_capture',
                                'preserve_original' => 0,
                                'patterns'          => array(
                                    "(\\+\\d+)",
                                    "(\\d+)"
                                )
                            ),
                        ),
                        'analyzer' => array(
                            'title_content_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => array('standard', 'stop', 'lowercase', 'asciifolding', 'edge_ngram_filter_4')
                            ),
                            'text_content_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => array('standard', 'stop', 'lowercase', 'asciifolding')
                            ),
                            'name_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'asciifolding', 'edge_ngram_filter_3')
                            ),
                            'email_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array('lowercase', 'email_filter', 'unique')
                            ),
                            'phone_analyzer'      => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array('phone_filter_leading_zero', 'phone_filter', 'ngram_filter_5')
                            )
                        )
                    )
                ),

                'types'    => array(
                    'article'           => array(
                        'mappings'    => array(
                            'title'        => array('analyzer' => 'title_content_analyzer'),
                            'content'      => array('analyzer' => 'text_content_analyzer'),
                            'status'       => array(),
                            'category_ids' => array('type' => 'integer'),
                            'labels'       => array('analyzer' => 'title_content_analyzer'),
                            'sticky_words' => array(),
                            'date_created' => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
                            'date_active'  => array('type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss')
                        ),
                        'persistence' => array(
                            'driver'                        => 'orm',
                            'model'                         => 'Application\DeskPRO\Entity\Article',
                            'provider'                      => array(),
                            'finder'                        => array(),
                            'elastica_to_model_transformer' => array('ignore_missing' => true),
                            'model_to_elastica_transformer' => array('service' => 'deskpro.search.article_to_elastica_transformer'),
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\ArticleRepository'
                        )
                    ),
                    'news'              => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\NewsRepository'
                        )
                    ),
                    'download'          => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\DownloadRepository'
                        )
                    ),
                    'feedback'          => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\FeedbackRepository'
                        )
                    ),
                    'organization'      => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\OrganizationRepository'
                        )
                    ),
                    'chat_conversation' => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\ChatConversationRepository'
                        )
                    ),
                    'person'            => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\PersonRepository'
                        )
                    ),
                    'ticket'            => array(
                        'mappings'    => array(
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
                            'repository'                    => 'Application\DeskPRO\NewSearch\Repository\TicketRepository'
                        )
                    ),
                )
            )
        )
    )
);

############################################################################
# DeskPRO Configuration
############################################################################

$container->loadFromExtension('deskpro_core', array());
$container->loadFromExtension('deskpro_search', array());
$container->loadFromExtension('deskpro_api_core', array());
