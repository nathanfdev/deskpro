<?php

namespace DeskPRO\Bundle\Core;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class CoreBundle extends \Symfony\Framework\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
        $container->registerExtension(new \DeskPRO\Bundle\Core\DependencyInjection\TwigExtension());
        $container->registerExtension(new \DeskPRO\Bundle\Core\DependencyInjection\CoreExtension());
        $container->registerExtension(new \DeskPRO\Bundle\Core\DependencyInjection\RequestUser());
    }
}
