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
use Symfony\Component\DependencyInjection\Reference;
use DeskPRO\App;

/**
 * This simply initiates teh App registry and sets the main app container.
 */
class RequestUser extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		$definition = new \Symfony\Component\DependencyInjection\Definition(
			'DeskPRO\\User\\UserLoader',
			array(
				new Reference('service_container'),
				new Reference('request')
			)
		);
		$definition->setFactoryMethod('getUserFromRequest');
		$container->setDefinition('deskpro.core.requestuser', $definition);
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
        return 'deskpro_requestuser';
    }
}