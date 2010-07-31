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

namespace DeskPRO\DependencyInjection;

use Symfony\Components\DependencyInjection\Extension\Extension;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\ContainerBuilder;
use Symfony\Components\DependencyInjection\Reference;



/**
 * This extends the default twig extension so we can use our custom hyrbid loader.
 */
class TwigExtension extends \Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension
{
	public function configLoad($config, ContainerBuilder $container)
    {
		parent::configLoad($config, $container);

		// Set our loader class
        $container->setParameter('twig.loader.class', 'DeskPRO\\Twig\\Loader\\Hybrid');

		// And our loader class also needs the service container, because we
		// fetch a database connection from it
		$def = $container->getDefinition('twig.loader');
		$def->setArguments(array(new Reference('service_container')));
    }
}