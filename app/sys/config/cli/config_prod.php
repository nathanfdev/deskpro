<?php if (!defined('DP_ROOT')) exit('No access');
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Resource\FileResource;

$loader->import(DP_ROOT.'/sys/config/config.php');
$container->loadFromExtension('framework', array(
	'router' => array(
		'resource' => DP_ROOT.'/sys/config/cli/routing.php'
	)
));

$container->loadFromExtension('fos_js_routing', array(
	'routes_to_expose' => array(
		'^api_',
	)
));

$definition = new Definition('Application\\ApiBundle\\StaticLoader\\RequestKey');
$definition->setFactoryClass('Application\\ApiBundle\\StaticLoader\\RequestKey');
$definition->setFactoryMethod('getApiKeyFromRequest');
$container->setDefinition('deskpro.api.request_key', $definition);

$definition = new Definition('Application\\ApiBundle\\StaticLoader\\RequestKey');
$definition->setFactoryClass('Application\\ApiBundle\\StaticLoader\\RequestKey');
$definition->setFactoryMethod('getApiTokenFromRequest');
$container->setDefinition('deskpro.api.request_token', $definition);