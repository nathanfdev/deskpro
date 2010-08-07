<?php

namespace DeskPRO;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class DeskPROBundle extends \Symfony\Framework\Bundle\Bundle
{
	public function buildContainer(ParameterBagInterface $parameterBag)
    {
        ContainerBuilder::registerExtension(new \DeskPRO\DependencyInjection\TwigExtension());
        ContainerBuilder::registerExtension(new \DeskPRO\DependencyInjection\AppExtension());
        ContainerBuilder::registerExtension(new \DeskPRO\DependencyInjection\CacheExtension());
    }

	public function boot(ContainerInterface $container)
	{
		parent::boot($container);
	}
}
