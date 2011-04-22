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

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Application\DeskPRO\App;

/**
 * Registers basic core stuff
 */
class CoreExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
    {
		$definition = new Definition('Application\\DeskPRO\\ConfigServiceLoader');
		$container->setDefinition('deskpro.config_service_loader', $definition);

		$definition = new Definition('Symfony\\Component\\HttpFoundation\\Response');
		$container->setDefinition('response', $definition);

		$definition = new Definition('Application\\DeskPRO\\DBAL\\Logging\\CacheInvalidator');
		$container->setDefinition('deskpro.dbal.logger.cache_invalidator', $definition);

		$definition = new Definition('Application\\DeskPRO\\DBAL\\Logging\\QueryLogger');
		$definition->addMethodCall('addSlowLogRule', array(31, 0));
		$container->setDefinition('deskpro.dbal.logger.query_logger', $definition);

		$definition = new Definition('Application\\DeskPRO\\DBAL\\Logging\\DelegateLogger');
		$definition->addMethodCall('addLogger', array(new Reference('deskpro.dbal.logger.cache_invalidator')));
		//$definition->addMethodCall('addLogger', array(new Reference('deskpro.dbal.logger.query_logger')));
		$container->setDefinition('doctrine.dbal.logger', $definition);

		$this->loadInputReader($container);
		$this->loadTranslation($container);
		$this->loadSettings($container);
		$this->loadDoctrineCaches($container);
    }


	protected function loadSession(ContainerBuilder $container)
	{
		// TODO: Change this to proper Entity storage when its finished
		$definition = new Definition('Symfony\\Component\\HttpFoundation\\SessionStorage\\NativeSessionStorage', array());
		$container->setDefinition('session.storage', $definition);

		$definition = new Definition('Application\\DeskPRO\\HttpFoundation\\Session', array(
			new Reference('doctrine.orm.entity_manager'),
			new Reference('session.storage'),
		));
		$container->setDefinition('session', $definition);
	}


	/**
	 * Sets up the translater
	 */
	protected function loadTranslation(ContainerBuilder $container)
	{
		// BundleLoader
		$definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\BundleLoader', array(array(
			'core' => DP_ROOT . '/src/Application/DeskPRO/Resources/language',
			'api' => DP_ROOT . '/src/Application/AgentBundle/Resources/language',
			'agent' => DP_ROOT . '/src/Application/AgentBundle/Resources/language',
			'user' => DP_ROOT . '/src/Application/UserBundle/Resources/language',
			'dev'  => DP_ROOT . '/src/Application/DevBundle/Resources/language',
		)));
		$container->setDefinition('deskpro.core.translate_loder_bundle', $definition);

		// DbLoader
		$definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\DbLoader', array(new Reference('database_connection')));
		$container->setDefinition('deskpro.core.translate_loder_db', $definition);

		// CombinationLoader
		$definition = new Definition('Application\\DeskPRO\\Translate\\Loader\\CombinationLoader');
		$definition->addMethodCall('addLoader', array(new Reference('deskpro.core.translate_loder_bundle')));
		$definition->addMethodCall('addLoader', array(new Reference('deskpro.core.translate_loder_db')));
		$container->setDefinition('deskpro.core.translate_loder', $definition);

		// Add the cacher to the CombinationLoader if we want
		$definition->addMethodCall('setCache', array(new Reference('deskpro.cache.phrases', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)));

		// Now create the translate object
		$definition = new Definition('Application\\DeskPRO\\Translate\\Translate', array(
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



	/**
	 * Sets up the settings loader
	 */
	protected function loadSettings(ContainerBuilder $container)
	{
		$definition = new Definition('Application\\DeskPRO\\Settings\\Settings', array(
			array(
				'core' => DP_ROOT . '/src/Application/DeskPRO/Resources/settings',
				'tech' => DP_ROOT . '/src/Application/AgentBundle/Resources/settings',
				'user' => DP_ROOT . '/src/Application/UserBundle/Resources/settings',
				'dev'  => DP_ROOT . '/src/Application/DevBundle/Resources/settings',
			),
			new Reference('database_connection')
		));
		$definition->addMethodCall('loadGroups', array('core'));
		$container->setDefinition('deskpro.core.settings', $definition);
	}

	protected function loadDoctrineCaches(ContainerBuilder $container)
	{
		if (App::getConfig('doctrine_cache_type') == 'sqlite') {
			$definition = new Definition('Orb\\Doctrine\\Common\\Cache\\SqliteCache');
			$definition->addMethodCall('setDbFile', array(
				$container->getParameter('kernel.cache_dir') . '/doctrinecache.db',
				'query_cache',
				'doctrinecache'
			));
			$container->setDefinition('doctrine.orm.default_query_cache', $definition);

			$definition = new Definition('Orb\\Doctrine\\Common\\Cache\\SqliteCache');
			$definition->addMethodCall('setDbFile', array(
				$container->getParameter('kernel.cache_dir') . '/doctrinecache.db',
				'metadata_cache',
				'doctrinecache'
			));
			$container->setDefinition('doctrine.orm.default_metadata_cache', $definition);
		}
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
