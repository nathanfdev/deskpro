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
use Symfony\Component\DependencyInjection\Definition;
use DeskPRO\App;

/**
 * Registers basic core stuff
 */
class CoreExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		$this->loadInputReader($container);
		$this->loadTranslation($container);
    }

	
	/**
	 * Sets up the translater
	 */
	protected function loadTranslation(ContainerBuilder $container)
	{
		// BundleLoader
		$definition = new Definition('DeskPRO\\Translate\\Loader\\BundleLoader', array(array(
			'core' => DP_ROOT . '/src/DeskPRO/Bundles/CoreBundle/Resources/language',
			'tech' => DP_ROOT . '/src/Application/TechBundle/Resources/language',
			'user' => DP_ROOT . '/src/Application/UserBundle/Resources/language',
			'dev'  => DP_ROOT . '/src/Application/DevBundle/Resources/language',
		)));
		$container->setDefinition('deskpro.core.translate_loder_bundle', $definition);

		// DbLoader
		$definition = new Definition('DeskPRO\\Translate\\Loader\\DbLoader', array(new Reference('database_connection')));
		$container->setDefinition('deskpro.core.translate_loder_db', $definition);

		// CombinationLoader
		$definition = new Definition('DeskPRO\\Translate\\Loader\\CombinationLoader');
		$definition->addMethodCall('addLoader', array(new Reference('deskpro.core.translate_loder_bundle')));
		$definition->addMethodCall('addLoader', array(new Reference('deskpro.core.translate_loder_db')));
		$container->setDefinition('deskpro.core.translate_loder', $definition);

		// Now create the translate object
		$definition = new Definition('DeskPRO\\Translate\\Translate', array(
			new Reference('deskpro.core.translate_loder'),
		));
		$container->setDefinition('deskpro.core.translate', $definition);
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
        return 'deskpro_core';
    }
}
