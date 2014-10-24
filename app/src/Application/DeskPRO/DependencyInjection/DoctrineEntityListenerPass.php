<?php
/**
 * https://gist.github.com/vadim2404/9538227
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEntityListenerPass implements CompilerPassInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		$ems = $container->getParameter('doctrine.entity_managers');
		foreach ($ems as $name => $em) {
			$container->getDefinition(sprintf('doctrine.orm.%s_configuration', $name))
				->addMethodCall('setEntityListenerResolver', array(new Reference('dp.doctrine.entity_listener_resolver')))
			;
		}

		$definition = $container->getDefinition('dp.doctrine.entity_listener_resolver');
		$services = $container->findTaggedServiceIds('doctrine.entity_listener');

		foreach ($services as $service => $attributes) {
			$definition->addMethodCall(
				'addMapping',
				array($container->getDefinition($service)->getClass(), $service)
			);
		}
	}
}
