<?php

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Resource\FileResource;

class DoctrineExtension extends \Symfony\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension
{
	protected function loadDbalConnection(array $connection, ContainerBuilder $container)
	{
		if (!isset($connection['dp_from_user_config'])) {
			parent::loadDbalConnection($connection, $container);
			return;
		}

		// previously registered?
		if ($container->hasDefinition(sprintf('doctrine.dbal.%s_connection', $connection['name']))) {
			$driverDef = $container->getDefinition(sprintf('doctrine.dbal.%s_connection', $connection['name']));
			$arguments = $driverDef->getArguments();
			$driverOptions = $arguments[0];
		} else {
			$containerClass = isset($connection['configuration_class']) ? $connection['configuration_class'] : 'Doctrine\DBAL\Configuration';
			$containerDef = new Definition($containerClass);
			$containerDef->addMethodCall('setSqlLogger', array(new Reference('doctrine.dbal.logger')));
			$container->setDefinition(sprintf('doctrine.dbal.%s_connection.configuration', $connection['name']), $containerDef);

			$eventManagerDef = new Definition($connection['event_manager_class']);
			$container->setDefinition(sprintf('doctrine.dbal.%s_connection.event_manager', $connection['name']), $eventManagerDef);

			$driverOptions = array();
			$driverDef = new Definition('Application\\DeskPRO\\StaticLoader\\DatabaseConnection');
			$driverDef->setFactoryMethod('getConnection');
			$container->setDefinition(sprintf('doctrine.dbal.%s_connection', $connection['name']), $driverDef);
		}

		if (isset($connection['driver'])) {
			$driverOptions['driverClass'] = sprintf('Doctrine\\DBAL\\Driver\\%s\\Driver', $connection['driver']);
		}
		if (isset($connection['wrapper_class'])) {
			$driverOptions['wrapperClass'] = $connection['wrapper_class'];
		}
		if (isset($connection['options'])) {
			$driverOptions['driverOptions'] = $connection['options'];
		}
		foreach (array('dbname', 'host', 'user', 'password', 'path', 'memory', 'port', 'unix_socket', 'charset') as $key) {
			if (isset($connection[$key])) {
				$driverOptions[$key] = $connection[$key];
			}
		}

		$driverDef->setArguments(array(
			$driverOptions,
			new Reference(sprintf('doctrine.dbal.%s_connection.configuration', $connection['name'])),
			new Reference(sprintf('doctrine.dbal.%s_connection.event_manager', $connection['name']))
		));
	}
}