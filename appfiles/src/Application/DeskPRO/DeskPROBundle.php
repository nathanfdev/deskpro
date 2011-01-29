<?php

namespace Application\DeskPRO;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeskPROBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
		// TODO sort out Hybrid loader again for gold
		//$container->registerExtension(new \Application\DeskPRO\DependencyInjection\TwigExtension());

        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\CoreExtension());
        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\DoctrineExtension());
        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\CacheExtension());
    }

	public function getName()
    {
        return 'DeskPRO';
    }
}
