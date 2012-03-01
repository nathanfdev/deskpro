<?php
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Resource\FileResource;


############################################################################
# Parameters
############################################################################

$container->setParameter('kernel.include_core_classes', false);
$container->setParameter('routing.file_locator.class', 'Application\\DeskPRO\\HttpKernel\\Config\\FileLocator');
$container->setParameter('templating.cache_warmer.template_paths.class', 'Application\\DeskPRO\\CacheWarmer\\TemplatePathsCacheWarmer');
$container->setParameter('doctrine.orm.proxy_dir', '%kernel.cache_dir%/../doctrine-proxies');


############################################################################
# Services
############################################################################

// doctrine.dbal.connection_factory
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\DBAL\\ConnectionFactory');
$definition->setArguments(array(
	'%doctrine.dbal.connection_factory.types%'
));
$definition->addMethodCall('setContainer', array(new Reference('service_container')));
$container->setDefinition('doctrine.dbal.connection_factory', $definition);

// deskpro.cache.annotations
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\ArrayFileCache');
$definition->setArguments(array(
	'%kernel.cache_dir%/../annotations.php'
));
$definition->addMethodCall('registerShutdownCommit');
$container->setDefinition('deskpro.cache.annotations', $definition);

// annotations.file_cache_reader
$definition = new Definition();
$definition->setClass('Doctrine\\Common\\Annotations\\CachedReader');
$definition->setPublic(false);
$definition->setArguments(array(
	new Reference('annotations.reader'),
	new Reference('deskpro.cache.annotations'),
	false
));
$container->setDefinition('annotations.file_cache_reader', $definition);

// deskpro.interface_value
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\InterfaceValue');
$container->setDefinition('deskpro.interface_value', $definition);

// doctrine.orm.default_query_cache
$definition = new Definition();
$definition->setClass('Orb\\Doctrine\\Common\\Cache\\PreloadedMysqlCache');
$definition->setArguments(array(
	new Reference('database_connection')
));
$definition->addMethodCall('setPrefix', array('dql', new Reference('deskpro.interface_value')));
$definition->addMethodCall('preloadPrefix', array(new Reference('deskpro.interface_value')));
$container->setDefinition('doctrine.orm.default_query_cache', $definition);

// deskpro.profiler.request_matcher
$definition = new Definition();
$definition->setClass('Application\\DeskPRO\\Profiler\\RequestMatcher');
$container->setDefinition('deskpro.profiler.request_matcher', $definition);

############################################################################
# Framework Configuration
############################################################################

$container->loadFromExtension('framework', array(
	'router' => array(
		'resource' => DP_ROOT.'/sys/config/install/routing.php'
	),
	'charset' => 'UTF-8',
	'secret' => 'mube224etsmhxky1gvwixc4b',
	'templating' => array(
		'engines' => array('php'),
		'assets_base_urls' => 'CONFIG_HTTP'
	),
	'validation' => array('enabled' => true),
	'session' => array(
		'default_locale' => 'en',
		'lifetime' => 3600,
	),
	'form' => array('enabled' => true)
));


############################################################################
# Doctrine Configuration
############################################################################

$container->loadFromExtension('doctrine', array(
	'orm' => array(
		'auto_generate_proxy_classes' => false,
		'default_entity_manager' => 'default',
		'entity_managers' => array(
			'default' => array('mappings' => array('DeskPRO' => array()))
		)
	),
	'dbal' => array(
		'default_connection' => 'default',
		'connections' => array(
			'default' => array('host' => 'from_user_config.db', 'logging' => true)
		)
	)
));


############################################################################
# DeskPRO Configuration
############################################################################

$container->loadFromExtension('install', array(

));

