<?php if (!defined('DP_ROOT')) exit('No access');
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

require_once __DIR__ . "/config.shared.php";

############################################################################
# Parameters
############################################################################

$container->setParameter('kernel.include_core_classes', false);
$container->setParameter('http_kernel.class', 'Application\\DeskPRO\\HttpKernel\\HttpKernel');
$container->setParameter('controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\ControllerResolver');
$container->setParameter('debug.controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\TraceableControllerResolver');
$container->setParameter('session.class', 'Application\\DeskPRO\\HttpFoundation\\Session');
$container->setParameter('swiftmailer.class', 'Application\\DeskPRO\\Mail\\Mailer');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('validator.mapping.class_metadata_factory.class', 'Application\\DeskPRO\\Validator\\Mapping\\ClassMetadataFactory');
$container->setParameter('router.class', 'Application\\DeskPRO\\Routing\\Router');
$container->setParameter('router.options.generator_dumper_class', 'Application\\DeskPRO\\Routing\\Generator\\Dumper\\PhpGeneratorDumper');
$container->setParameter('router.options.matcher_dumper_class', 'Application\\DeskPRO\\Routing\\Matcher\\Dumper\\PhpMatcherDumper');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('form.type_extension.csrf.enabled', false);
$container->setParameter('file_locator.class', 'Application\\DeskPRO\\HttpKernel\\Config\\FileLocator');
$container->setParameter('routing.file_locator.class', 'Application\\DeskPRO\\HttpKernel\\Config\\FileLocator');

// standard-symfony changesn to templating
$container->setParameter('templating.engine.delegating.class', 'Application\\DeskPRO\\Templating\\Engine');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');

// candidates to be moved to config.share.php below:
$container->setParameter('templating.asset.url_package.class', 'Application\\DeskPRO\\Templating\\Asset\\UrlPackage');
$container->setParameter('templating.asset.path_package.class', 'Application\\DeskPRO\\Templating\\Asset\\PathPackage');
$container->setParameter('templating.globals.class', 'Application\\DeskPRO\\Templating\\GlobalVariables');
$container->setParameter('twig.extension.trans.class', 'Application\\DeskPRO\\Twig\\Extension\\TranslationExtension');

############################################################################
# Services
############################################################################

// session.storage
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\HttpFoundation\\SessionStorage\\SessionEntityStorage');
$definition->setArguments(
    array(
        new Reference('doctrine.orm.entity_manager'),
        '%session.storage.options%',
        new Reference('settings_resolver')
    )
);
$container->setDefinition('session.storage', $definition);

// twig.helpers.deskpro_templating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\TemplatingExtension');
$definition->setArguments(
    array(
        new Reference('service_container')
    )
);
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_templating', $definition);

// twig.helpers.deskpro_user_templating
// TODO: is this even necessary? might not be used with the new portal in place. commenting out. (oct 2014)
//$definition = new Definition();
//$definition->setClass('Application\\UserBundle\\Twig\\Extension\\UserTemplatingExtension');
//$definition->setArguments(array(
//	new Reference('service_container')
//));
//$definition->addTag('twig.extension', array());
//$container->setDefinition('twig.helpers.deskpro_user_templating', $definition);


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
    //TODO: make this secret a config.php responsibility. at least give an option to change it.
    'secret' => 'mube224etsmhxky1gvwixc4b',
    'templating' => array(
        'engines' => array('twig', 'php'/*, 'jsonphp'*/),
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
    ),
    'globals' => array(
        'experimental_admin_features' => false
    )
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
                            'ngram_filter'              => array(
                                'type'        => 'nGram',
                                'min_gram'    => 2,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'ngram_filter_3'            => array(
                                'type'        => 'nGram',
                                'min_gram'    => 3,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'ngram_filter_4'            => array(
                                'type'        => 'nGram',
                                'min_gram'    => 4,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'ngram_filter_5'            => array(
                                'type'        => 'nGram',
                                'min_gram'    => 5,
                                'max_gram'    => 20,
                                'token_chars' => array('letters', 'digit', 'punctuation', 'symbol')
                            ),
                            'email_filter'              => array(
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
                            'ngram_analyzer'      => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'asciifolding', 'ngram_filter')
                            ),
                            'ngram_analyzer_3'    => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'asciifolding', 'ngram_filter_3')
                            ),
                            'whitespace_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'asciifolding')
                            ),
                            'email_analyzer'      => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array("email_filter", "lowercase", "unique")
                            ),
                            'phone_analyzer'      => array(
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                                'filter'    => array("phone_filter_leading_zero", "phone_filter", 'ngram_filter_5')
                            )
                        )
                    )
                ),

                'types'    => array(
                    'article'           => array(
                        'mappings'    => array(
                            'title'        => array(),
                            'content'      => array(),
                            'status'       => array(),
                            'category_ids' => array('type' => 'integer'),
                            'labels'       => array(),
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
                            'title'        => array(),
                            'labels'       => array(),
                            'sticky_words' => array(),
                            'content'      => array(),
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
                            'title'        => array(),
                            'labels'       => array(),
                            'sticky_words' => array(),
                            'content'      => array(),
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
                            'title'        => array(),
                            'labels'       => array(),
                            'sticky_words' => array(),
                            'content'      => array(),
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
                            'name'          => array('type' => 'string'),
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
                            'name'          => array(),
                            'first_name'    => array(),
                            'last_name'     => array(),
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
