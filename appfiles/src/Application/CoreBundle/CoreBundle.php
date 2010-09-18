<?php

namespace Application\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreBundle extends \Symfony\Framework\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
        $container->registerExtension(new \Application\CoreBundle\DependencyInjection\TwigExtension());
        $container->registerExtension(new \Application\CoreBundle\DependencyInjection\CoreExtension());
        $container->registerExtension(new \Application\CoreBundle\DependencyInjection\DoctrineExtension());
        $container->registerExtension(new \Application\CoreBundle\DependencyInjection\CacheExtension());
    }
}
