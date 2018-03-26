<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
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
        $def->addMethodCall('setDb', [new Reference('database_connection')]);
    }
}
