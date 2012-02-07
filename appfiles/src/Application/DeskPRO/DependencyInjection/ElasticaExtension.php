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
 * Registers Elastica-related services
 */
class ElasticaExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
    {
		#------------------------------
		# The manager
		#------------------------------

		$definition = new Definition('Application\\DeskPRO\\Elastica\\ElasticaManager');
		$definition->setFactoryService('deskpro.config_service_loader');
		$definition->setFactoryMethod('loadElasticaManager');

		$container->setDefinition('deskpro.elastica.manager', $definition);


		#------------------------------
		# Types
		#------------------------------

		// Article
		$definition = new Definition('Application\\DeskPRO\\Elastica\\Type\\ArticleType');
		$definition->setArguments(array(new Reference('deskpro.elastica.manager')));
		$container->setDefinition('deskpro.elastica.types.article', $definition);

		// Download
		$definition = new Definition('Application\\DeskPRO\\Elastica\\Type\\DownloadType');
		$definition->setArguments(array(new Reference('deskpro.elastica.manager')));
		$container->setDefinition('deskpro.elastica.types.download', $definition);

		// Feedback
		$definition = new Definition('Application\\DeskPRO\\Elastica\\Type\\FeedbackType');
		$definition->setArguments(array(new Reference('deskpro.elastica.manager')));
		$container->setDefinition('deskpro.elastica.types.feedback', $definition);

		// News
		$definition = new Definition('Application\\DeskPRO\\Elastica\\Type\\NewsType');
		$definition->setArguments(array(new Reference('deskpro.elastica.manager')));
		$container->setDefinition('deskpro.elastica.types.news', $definition);
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
        return 'deskpro_elastica';
    }
}