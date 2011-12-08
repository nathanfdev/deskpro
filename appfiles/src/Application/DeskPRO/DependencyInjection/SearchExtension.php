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

class SearchExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
    {
		$definition = new Definition('Application\\DeskPRO\\Search\\Adapter\\AbstractAdapter');
		$definition->setFactoryClass('Application\\DeskPRO\\StaticLoader\\SearchAdapter');
		$definition->setFactoryMethod('getSearchAdapter');
		$container->setDefinition('deskpro.search_adapter', $definition);

		$definition = new Definition('Application\\DeskPRO\\Search\\EntityListener', array(new Reference('deskpro.search_adapter')));
		$definition->addTag('kernel.listener', array('event' => 'Doctrine_onPostUpdate'));
		$definition->addTag('kernel.listener', array('event' => 'Doctrine_onPostPersist'));
		$definition->addTag('kernel.listener', array('event' => 'Doctrine_onPostRemove'));
		$container->setDefinition('deskpro.search_adapter_entity_listener', $definition);

		// Doctrine listener to support search engine
		$definition = new Definition('Application\\DeskPRO\\Search\\EntityWatcher\\EntityWatcher', array(new Reference('service_container')));
		$definition->addTag('doctrine.event_subscriber');
		$container->setDefinition('deskpro.search.entity_listener', $definition);
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
        return 'deskpro_search';
    }
}
