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

/* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$loader->import(__DIR__.'/config.shared.php');
$loader->import(__DIR__.'/config.legacy.yml');

############################################################################
# Parameters
############################################################################

$container->setParameter('kernel.include_core_classes', false);
$container->setParameter('http_kernel.class', 'Application\\DeskPRO\\HttpKernel\\HttpKernel');
$container->setParameter('controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\ControllerResolver');
$container->setParameter('debug.controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\TraceableControllerResolver');
$container->setParameter('session.class', 'Application\\DeskPRO\\HttpFoundation\\Session');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('validator.mapping.class_metadata_factory.class', 'Application\\DeskPRO\\Validator\\Mapping\\ClassMetadataFactory');
$container->setParameter('router.options.generator_dumper_class', 'Application\\DeskPRO\\Routing\\Generator\\Dumper\\PhpGeneratorDumper');
$container->setParameter('router.options.matcher_dumper_class', 'Application\\DeskPRO\\Routing\\Matcher\\Dumper\\PhpMatcherDumper');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('form.type_extension.csrf.enabled', false);
$container->setParameter('file_locator.class', 'DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator');
$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/../doctrine-proxies');

// standard-symfony changesn to templating
$container->setParameter('templating.engine.delegating.class', 'Application\\DeskPRO\\Templating\\Engine');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');

// candidates to be moved to config.share.php below:
$container->setParameter('templating.globals.class', 'Application\\DeskPRO\\Templating\\GlobalVariables');
$container->setParameter('twig.extension.trans.class', 'Application\\DeskPRO\\Twig\\Extension\\TranslationExtension');

############################################################################
# Services
############################################################################

// dp.cache_clearer.cachedir
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\CacheClearer\\CacheDirClearer');
$definition->addTag('kernel.cache_clearer');
$container->setDefinition('dp.cache_clearer.cachedir', $definition);

// session.storage
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\HttpFoundation\\SessionStorage\\SessionEntityStorage');
$definition->setArguments(
    array(
        new Reference('doctrine.orm.entity_manager'),
        '%session.storage.options%',
        new Reference('settings_resolver'),
    )
);
$container->setDefinition('session.storage', $definition);

// twig.helpers.deskpro_templating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\TemplatingExtension');
$definition->setArguments(array(
    new Reference('service_container'),
));
$definition->addTag('twig.extension', array());
$container->setDefinition('twig.helpers.deskpro_templating', $definition);

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
    new Reference('database_connection'),
));
$definition->addMethodCall('setPrefix', array('dres', new Reference('deskpro.interface_value')));
$container->setDefinition('default_result_cache', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments(array(
    new Reference('service_container'),
));
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// browser_sniffer
$definition = new Definition();
$definition->setClass('Browser');
$container->setDefinition('browser_sniffer', $definition);

// deskpro.logging.null_handler
$definition = new Definition();
$definition->setClass('Orb\\Logger\\Handler\\NullHandler');
$container->setDefinition('deskpro.logging.null_handler', $definition);

// deskpro.service_urls
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Settings\\ServiceUrls');
$definition->addMethodCall('loadPack', array('%kernel.root_dir%/config/service-urls.php'));
$container->setDefinition('deskpro.service_urls', $definition);

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
    $parts     = explode('\\', $class);
    $base_name = array_pop($parts);

    $alias = $class::getAlias();

    $definition = new Definition();
    $definition->setClass($class);
    $definition->setFactoryService('deskpro.constraint_factory');
    $definition->setFactoryMethod('get'.ucfirst($base_name));
    $definition->addTag('validator.constraint_validator', array('alias' => $alias));
    $container->setDefinition('validator.deskpro.'.strtolower($alias), $definition);
}

############################################################################
# Framework Configuration
############################################################################

$container->loadFromExtension('framework', array(
    'secret'     => 'irrelevant - compiler pass will override this',
    'templating' => array(
        'engines'          => array('twig', 'php', 'jsonphp'),
        'assets_base_urls' => 'SET_IN_ASSET_PACKAGE_PASS',
        'packages'         => array(
            'app_assets' => array('base_url' => 'SET_IN_ASSET_PACKAGE_PASS'),
        ),
    ),
    'validation' => array('enabled' => true, 'static_method' => array('loadValidatorMetadata'), 'api' => '2.4'),
    'session'    => array(),
    'form'       => array('enabled' => true),
    'router'     => array(
        'resource' => DP_ROOT.'/sys/config/routing.php',
    ),
));

// templating.engine.jsonphp
$definition = new Definition();
$definition->setClass('Orb\\Templating\\Engine\\PhpVarJsonEngine');
$definition->setArguments(
    array(
        new Reference('templating.name_parser'),
        new Reference('service_container'),
        new Reference('templating.loader'),
        new Reference('templating.globals'),
    )
);
$definition->addTag('templating.engine', array('alias' => 'jsonphp'));
$container->setDefinition('templating.engine.jsonphp', $definition);

// Monolog default logging, turn off unless specifically enabled (eg in some _dev configs)
$container->loadFromExtension('monolog', array(
    'handlers' => array(
        'main' => array(
            'type' => 'service',
            'id'   => 'deskpro.logging.null_handler',
        ),
        'email_log_collector' => array(
            'type'     => 'service',
            'id'       => 'email.log_collector',
            'channels' => array('dp.email.out.mailer', 'dp.email.out.transport', 'dp.email.out.queue', 'dp.email.out.raw_transport'),
        ),
    ),
));

############################################################################
# Twig Configuration
############################################################################

$container->loadFromExtension('twig', array(
    'form' => array(
        'resources' => array(
            'DeskPRO:Form:form_div_layout.html.twig',
        ),
    ),
    'globals' => array(
        'experimental_admin_features' => false,
    ),
));

############################################################################
# DeskPRO Configuration
############################################################################

$container->loadFromExtension('deskpro_core', array());
//$container->loadFromExtension('deskpro_search', array()); -- already included in config.shared.php
$container->loadFromExtension('deskpro_api_core', array());
