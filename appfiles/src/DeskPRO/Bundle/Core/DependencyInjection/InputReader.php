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

namespace DeskPRO\Bundle\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Sets up a new input reader and cleaner
 */
class InputReader extends \Symfony\Component\DependencyInjection\Extension\Extension
{
	public function configLoad($config, ContainerBuilder $container)
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
		$definition = new Definition('Orb\Input\Reader\Source\Superglobal', array(new Reference('deskpro.core.input_cleaner')));
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
        return 'deskpro_inputreader';
    }
}