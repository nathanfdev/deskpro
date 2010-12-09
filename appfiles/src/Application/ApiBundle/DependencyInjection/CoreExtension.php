<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers basic core stuff
 */
class CoreExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		$definition = new \Symfony\Component\DependencyInjection\Definition(
			'Application\ApiBundle\Listener\ApiKeyListener',
			array(
				new Reference('service_container')
			)
		);
		$definition->addTag('kernel.listener');
		$container->setDefinition('deskpro.api.apikeylistener', $definition);
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
        return 'deskpro_api_core';
    }
}
