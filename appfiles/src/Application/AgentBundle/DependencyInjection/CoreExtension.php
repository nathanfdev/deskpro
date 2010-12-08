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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers basic core stuff
 */
class CoreExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {

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
