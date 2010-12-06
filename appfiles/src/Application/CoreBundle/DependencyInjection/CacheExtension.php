<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Application\DeskPRO\App;

/**
 * Registeres cache services
 */
class CacheExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function cachesLoad($config, ContainerBuilder $container)
    {
    	if (!$config) return;

    	foreach ($config as $name => $cache_options) {
    		$this->loadCacheService($name, $cache_options, $container);
    	}
    }

    protected function loadCacheService($name, $cache_options, ContainerBuilder $container)
    {
		$service_name = 'deskpro.cache.' . $name;

		if (!$cache_options) $cache_options = array();

		$definition = new \Symfony\Component\DependencyInjection\Definition(
			'Application\\DeskPRO\\StaticLoader\\Cache',
			array(
				$name,
				$cache_options,
				$container->getParameter('kernel.cache_dir')
			)
		);
		$definition->setFactoryMethod('getCache');
		$container->setDefinition($service_name, $definition);
    }

	public function getXsdValidationBasePath()
	{
		return null;
	}

	public function getNamespace()
	{
		return null;
	}

	public function getAlias()
    {
        return 'deskpro_cache';
    }
}
