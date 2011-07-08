<?php

namespace Application\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class UserBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getPath()
	{
		return __DIR__;
	}
}
