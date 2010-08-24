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

namespace Application\TechBundle\DependencyInjection;

use Symfony\Components\DependencyInjection\ContainerBuilder;
use Symfony\Components\DependencyInjection\Reference;

/**
 * Registers basic core stuff
 */
class CoreExtension extends \Symfony\Components\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		$definition = new \Symfony\Components\DependencyInjection\Definition(
			'Application\TechBundle\Listener\LoginListener',
			array(
				new Reference('service_container')
			)
		);
		$definition->addTag('kernel.listener');
		$container->setDefinition('deskpro.tech.loginlistener', $definition);
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
        return 'deskpro_tech_core';
    }
}
