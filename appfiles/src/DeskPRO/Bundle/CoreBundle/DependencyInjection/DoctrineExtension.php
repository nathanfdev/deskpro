<?php

namespace DeskPRO\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Resource\FileResource;

class DoctrineExtension extends \Symfony\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension
{
	protected function loadOrmDefaults(array $config, ContainerBuilder $container)
	{
		parent::loadOrmDefaults($config, $container);
		$container->setParameter('doctrine.orm.entity_manager_class', 'DeskPRO\ORM\EntityManager');
	}

	protected function loadOrmEntityManager(array $entityManager, ContainerBuilder $container)
	{
		parent::loadOrmEntityManager($entityManager, $container);

		$key = sprintf('doctrine.orm.%s_entity_manager', $entityManager['name']);
		$def = $container->getDefinition($key);
		$def->addArgument(null);
		$def->addArgument(new Reference('service_container'));
	}
}