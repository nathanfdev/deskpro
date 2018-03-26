<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}
use Application\AgentBundle\Service\AgentCaptchaGenerator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$loader->import(__DIR__.'/config.shared.php');
$loader->import(__DIR__.'/config.captcha.yml');
$loader->import(__DIR__.'/config.legacy.yml');
$loader->import(__DIR__.'/updater/loggers.yml');
$loader->import(__DIR__.'/updater/services.yml');

//###########################################################################
// Parameters
//###########################################################################

$container->setParameter('kernel.include_core_classes', false);
$container->setParameter('controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\ControllerResolver');
$container->setParameter('debug.controller_resolver.class', 'Application\\DeskPRO\\HttpKernel\\Controller\\TraceableControllerResolver');
$container->setParameter('session.class', 'Application\\DeskPRO\\HttpFoundation\\Session');
$container->setParameter('router.options.generator_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('router.options.generator_base_class', 'Application\\DeskPRO\\Routing\\Generator\\UrlGenerator');
$container->setParameter('validator.mapping.class_metadata_factory.class', 'Application\\DeskPRO\\Validator\\Mapping\\ClassMetadataFactory');
$container->setParameter('router.options.generator_dumper_class', 'Application\\DeskPRO\\Routing\\Generator\\Dumper\\PhpGeneratorDumper');
$container->setParameter('router.options.matcher_dumper_class', 'Application\\DeskPRO\\Routing\\Matcher\\Dumper\\PhpMatcherDumper');
$container->setParameter('form.type_extension.csrf.enabled', false);
$container->setParameter('file_locator.class', 'DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator');
$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/doctrine-proxies');
$container->setParameter('doctrine.dbal.connection_factory.class', 'DeskPRO\\Bundle\\AppBundle\\Doctrine\\ConnectionFactory');
$container->setParameter('gregwar_captcha.captcha_generator.class', AgentCaptchaGenerator::class);

// standard-symfony changes to templating
$container->setParameter('templating.engine.delegating.class', 'Application\\DeskPRO\\Templating\\Engine');
$container->setParameter('twig.class', 'Application\\DeskPRO\\Twig\\Environment');

// candidates to be moved to config.share.php below:
$container->setParameter('templating.globals.class', 'Application\\DeskPRO\\Templating\\GlobalVariables');
$container->setParameter('twig.extension.trans.class', 'Application\\DeskPRO\\Twig\\Extension\\TranslationExtension');

//###########################################################################
// Services
//###########################################################################

// session.storage
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\HttpFoundation\\SessionStorage\\SessionEntityStorage');
$definition->setArguments(
    [
        new Reference('doctrine.orm.entity_manager'),
        '%session.storage.options%',
        new Reference('settings_resolver'),
    ]
);
$container->setDefinition('session.storage', $definition);

// twig.helpers.deskpro_templating
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\TemplatingExtension');
$definition->setArguments([
    new Reference('service_container'),
]);
$definition->addTag('twig.extension', []);
$container->setDefinition('twig.helpers.deskpro_templating', $definition);

// twig.helpers.deskpro_userdate
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Twig\\Extension\\UserDateExtension');
$definition->setArguments([
    new Reference('service_container'),
]);
$definition->addTag('twig.extension');
$container->setDefinition('twig.helpers.deskpro_userdate', $definition);

// deskpro.interface_value
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\InterfaceValue');
$container->setDefinition('deskpro.interface_value', $definition);

// doctrine.orm.default_result_cache
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\PreloadedMysqlCache');
$definition->setArguments([
    new Reference('database_connection'),
]);
$definition->addMethodCall('setPrefix', ['dres', new Reference('deskpro.interface_value')]);
$container->setDefinition('default_result_cache', $definition);

$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\ORM\\ContainerAwareEntityListenerResolver');
$definition->setArguments([
    new Reference('service_container'),
]);
$container->setDefinition('dp.doctrine.entity_listener_resolver', $definition);

// deskpro.logging.null_handler
$definition = new Definition();
$definition->setClass('Orb\\Logger\\Handler\\NullHandler');
$container->setDefinition('deskpro.logging.null_handler', $definition);

// deskpro.service_urls
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Settings\\ServiceUrls');
$definition->addMethodCall('loadPack', ['%kernel.root_dir%/config/service-urls.php']);
$container->setDefinition('deskpro.service_urls', $definition);

//###########################################################################
// Validators and Constraints
//###########################################################################

// deskpro.constraint_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Validator\\Constraints\\ConstraintFactory');
$definition->setArguments([new Reference('service_container')]);
$container->setDefinition('deskpro.constraint_factory', $definition);

foreach ([
    'Application\\DeskPRO\\Validator\\Constraints\\AgentGroupValidator',
    'Application\\DeskPRO\\Validator\\Constraints\\AgentTeamValidator',
] as $class) {
    $parts     = explode('\\', $class);
    $base_name = array_pop($parts);

    $alias = $class::getAlias();

    $definition = new Definition();
    $definition->setClass($class);
    $definition->setFactory([new Reference('deskpro.constraint_factory'), 'get'.ucfirst($base_name)]);
    $definition->addTag('validator.constraint_validator', ['alias' => $alias]);
    $container->setDefinition('validator.deskpro.'.strtolower($alias), $definition);
}

//###########################################################################
// Framework Configuration
//###########################################################################

$container->loadFromExtension('framework', [
    'secret'     => 'irrelevant - compiler pass will override this',
    'templating' => [
        'engines'          => ['twig', 'php', 'jsonphp'],
        'assets_base_urls' => 'http://bogus/SET_IN_ASSET_PACKAGE_PASS',
        'packages'         => [
            'app_assets'    => ['base_url' => 'http://bogus/SET_IN_ASSET_PACKAGE_PASS'],
            'appsrc_assets' => ['base_url' => 'http://bogus/SET_IN_ASSET_PACKAGE_PASS'],
        ],
    ],
    'validation' => ['enabled' => true, 'static_method' => ['loadValidatorMetadata'], 'api' => '2.4'],
    'session'    => [],
    'form'       => ['enabled' => true],
    'router'     => [
        'resource' => DP_ROOT.'/sys/config/routing.php',
    ],
]);

// templating.engine.jsonphp
$definition = new Definition();
$definition->setClass('Orb\\Templating\\Engine\\PhpVarJsonEngine');
$definition->setArguments(
    [
        new Reference('templating.name_parser'),
        new Reference('service_container'),
        new Reference('templating.loader'),
        new Reference('templating.globals'),
    ]
);
$definition->addTag('templating.engine', ['alias' => 'jsonphp']);
$container->setDefinition('templating.engine.jsonphp', $definition);

//###########################################################################
// Twig Configuration
//###########################################################################

$container->loadFromExtension('twig', [
    'form' => [
        'resources' => [
            'DeskPRO:Form:form_div_layout.html.twig',
        ],
    ],
    'globals' => [
        'experimental_admin_features' => false,
    ],
]);

//###########################################################################
// DeskPRO Configuration
//###########################################################################

$container->loadFromExtension('deskpro_core', []);
//$container->loadFromExtension('deskpro_search', array()); -- already included in config.shared.php
if ($container->hasExtension('deskpro_api_core')) {
    $container->loadFromExtension('deskpro_api_core', []);
}
