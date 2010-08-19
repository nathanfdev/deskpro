<?php

namespace DeskPRO;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class DeskPROBundle extends \Symfony\Framework\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
        $container->registerExtension(new \DeskPRO\DependencyInjection\TwigExtension());
        $container->registerExtension(new \DeskPRO\DependencyInjection\AppExtension());
        $container->registerExtension(new \DeskPRO\DependencyInjection\CacheExtension());
    }
}
