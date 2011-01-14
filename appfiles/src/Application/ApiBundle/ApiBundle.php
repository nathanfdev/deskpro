<?php

namespace Application\ApiBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function registerExtensions(ContainerBuilder $container)
    {
		$container->registerExtension(new \Application\ApiBundle\DependencyInjection\CoreExtension());
    }
}
