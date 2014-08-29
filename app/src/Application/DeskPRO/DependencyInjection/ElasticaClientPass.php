<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ElasticaClientPass implements CompilerPassInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		if (!$container->hasDefinition('fos_elastica.client.default')) {
			return;
		}

		$definition = $container->getDefinition('fos_elastica.client.default');
		$definition->setClass('Application\\DeskPRO\\Elastica\\Client');
		$definition->setFactoryService('deskpro.elastica.client_factory');
		$definition->setFactoryMethod('createSystemClientByConfig');

		$indexFactoryDef = new Definition('Application\\DeskPRO\\Elastica\\IndexFactory');
		$indexFactoryDef->setArguments(array(new Reference('fos_elastica.client.default')));
		$container->setDefinition('deskpro.elastica.default_index_factory', $indexFactoryDef);

		$indexDef = new Definition('Elastica\\Index');
		$indexDef->setFactoryService('deskpro.elastica.default_index_factory');
		$indexDef->setFactoryMethod('getIndex');
		$indexDef->setArguments(array('deskpro'));
		$container->setDefinition('fos_elastica.index.deskpro', $indexDef);
	}
}