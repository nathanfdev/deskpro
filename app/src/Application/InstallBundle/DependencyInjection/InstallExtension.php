<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class InstallExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
    {
		$definition = new Definition('Application\\DeskPRO\\ConfigServiceLoader');
		$container->setDefinition('deskpro.config_service_loader', $definition);

		$definition = new Definition('Application\\DeskPRO\\Settings\\Settings', array(
			array(
				'core'  => DP_ROOT . '/src/Application/DeskPRO/Resources/settings',
				'agent' => DP_ROOT . '/src/Application/AgentBundle/Resources/settings',
				'user'  => DP_ROOT . '/src/Application/UserBundle/Resources/settings',
				'dev'   => DP_ROOT . '/src/Application/DevBundle/Resources/settings',
			),
			new Reference('database_connection')
		));
		$definition->addMethodCall('loadGroups', array('core'));
		$container->setDefinition('deskpro.core.settings', $definition);

		$definition = new Definition('Application\\DeskPRO\\Search\\Adapter\\AbstractAdapter');
		$definition->setFactoryClass('Application\\DeskPRO\\StaticLoader\\SearchAdapter');
		$definition->setFactoryMethod('getSearchAdapter');
		$container->setDefinition('deskpro.search_adapter', $definition);

		$this->loadInputReader($container);
    }

	/**
	 * Sets up the input reader
	 */
	protected function loadInputReader(ContainerBuilder $container)
	{
		// Init readers
		$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_REQUEST'));
		$container->setDefinition('deskpro.core.input_reader_req', $definition);

		$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_POST'));
		$container->setDefinition('deskpro.core.input_reader_post', $definition);

		$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_GET'));
		$container->setDefinition('deskpro.core.input_reader_get', $definition);

		$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array('_COOKIE'));
		$container->setDefinition('deskpro.core.input_reader_cookie', $definition);

		// Init cleaner
		$definition = new Definition('Orb\Input\Cleaner\Cleaner');
		$container->setDefinition('deskpro.core.input_cleaner', $definition);

		// Init reader
		$definition = new Definition('Orb\Input\Reader\Reader', array(new Reference('deskpro.core.input_cleaner')));
		$definition->addMethodCall('addSource', array('req', new Reference('deskpro.core.input_reader_req')));
		$definition->addMethodCall('addSource', array('post', new Reference('deskpro.core.input_reader_post')));
		$definition->addMethodCall('addSource',array('get', new Reference('deskpro.core.input_reader_get')));
		$definition->addMethodCall('addSource', array('cookie', new Reference('deskpro.core.input_reader_cookie')));
		$definition->addMethodCall('setArrayStringSeparator', array('.'));
		$container->setDefinition('deskpro.core.input_reader', $definition);
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
        return 'install';
    }
}
