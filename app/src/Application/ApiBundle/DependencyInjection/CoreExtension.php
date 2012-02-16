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

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Application\DeskPRO\App;

/**
 * Registers basic core stuff
 */
class CoreExtension extends Extension
{
	public function load($config, ContainerBuilder $container)
    {
		$service_name = 'deskpro.api.request_key';

		$definition = new \Symfony\Component\DependencyInjection\Definition(
			'Application\\ApiBundle\\StaticLoader\\RequestKey'
		);
		$definition->setFactoryMethod('getApiKeyFromRequest');
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
        return 'deskpro_api_core';
    }
}
