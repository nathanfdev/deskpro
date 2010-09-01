<?php

namespace DeskPRO\Bundle\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreBundle extends \Symfony\Framework\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
        $container->registerExtension(new \DeskPRO\Bundle\CoreBundle\DependencyInjection\TwigExtension());
        $container->registerExtension(new \DeskPRO\Bundle\CoreBundle\DependencyInjection\CoreExtension());
        $container->registerExtension(new \DeskPRO\Bundle\CoreBundle\DependencyInjection\RequestUser());
        $container->registerExtension(new \DeskPRO\Bundle\CoreBundle\DependencyInjection\DoctrineExtension());
    }
}
