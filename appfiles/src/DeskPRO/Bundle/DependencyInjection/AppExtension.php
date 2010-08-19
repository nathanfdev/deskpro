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

namespace DeskPRO\Bundle\DependencyInjection;

use Symfony\Components\DependencyInjection\ContainerBuilder;
use DeskPRO\App;

/**
 * This simply initiates teh App registry and sets the main app container.
 */
class AppExtension extends \Symfony\Components\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		parent::configLoad($config, $container);

		App::set('container', $container);
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
        return 'deskpro.app';
    }
}