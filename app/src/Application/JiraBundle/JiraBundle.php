<?php

namespace Application\JiraBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * 
 */
class JiraBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
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