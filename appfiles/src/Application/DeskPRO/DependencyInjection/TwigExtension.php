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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;



/**
 * This extends the default twig extension so we can use our custom hyrbid loader.
 */
class TwigExtension extends \Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension
{
	public function load(array $config, ContainerBuilder $container)
    {
		parent::load($config, $container);

		// And our loader class also needs the service container, because we
		// fetch a database connection from it
		$def = $container->getDefinition('twig.loader');
		$def->addMethodCall('setDb', array(new Reference('database_connection')));
    }
}