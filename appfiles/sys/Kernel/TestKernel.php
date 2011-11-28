<?php

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

use Application\DeskPRO\App;

class TestKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array();

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/test/config_'.$this->getEnvironment().'.yml');
	}
}