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

namespace DeskPRO\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use DeskPRO\App;

/**
 * This simply initiates teh App registry and sets the main app container.
 */
class CacheExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function coreLoad($config, ContainerBuilder $container)
    {
		$definition = new Symfony\Component\DependencyInjection\Definition(
			$container->getParameter('deskpro.core.cache.class'),
			$container->getParameter('deskpro.core.cache.args')
		);
		$container->setDefinition('deskpro.core.cache', $definition);
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
        return 'deskpro';
    }
}