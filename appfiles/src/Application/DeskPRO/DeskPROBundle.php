<?php

namespace Application\DeskPRO;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeskPROBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function __construct()
	{
		$this->name = 'DeskPRO';
	}

	public function build(ContainerBuilder $container)
    {
        // register the extension(s) found in DependencyInjection/ directory
        parent::build($container);

        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\CoreExtension());
        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\CacheExtension());
        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\SwiftmailerExtension());
        $container->registerExtension(new \Application\DeskPRO\DependencyInjection\SearchExtension());
    }

	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getPath()
	{
		return __DIR__;
	}
}
