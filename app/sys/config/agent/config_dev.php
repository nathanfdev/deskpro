<?php if (!defined('DP_ROOT')) exit('No access');
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Resource\FileResource;

$loader->import(DP_ROOT.'/sys/config/config.php');

$container->setParameter('kernel.debug', true);

$container->loadFromExtension('framework', array(
	'router' => array(
		'resource' => DP_ROOT.'/sys/config/agent/routing_dev.php'
	),
));

$container->loadFromExtension('twig', array(
	'debug' => true
));

$container->loadFromExtension('monolog', array(
	'handlers' => array(
		'main' => array(
			'type' => 'stream',
			'path' => '%kernel.logs_dir%/%kernel.environment%.agent.log',
			'level' => 'WARNING'
		)
	)
));
