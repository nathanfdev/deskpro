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

namespace Application\UserBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Application\DeskPRO\App;

class UserExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
    {
		$definition = new Definition('Application\\DeskPRO\\PageDisplay\\Page\\PortalPageLoader', array(
			new Reference('doctrine.orm.default_entity_manager')
		));
		$container->setDefinition('deskpro.user_portal_page_loader', $definition);

		$definition = new Definition('Application\\DeskPRO\\PageDisplay\\Page\\PortalPage', array(
			new Reference('service_container'),
			new Reference('deskpro.session_person')
		));
		$definition->addMethodCall('setLazyLoader', array(new Reference('deskpro.user_portal_page_loader')));
		$container->setDefinition('deskpro.user_portal_page', $definition);
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
        return 'user';
    }
}