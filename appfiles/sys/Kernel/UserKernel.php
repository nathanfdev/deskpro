<?php

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

use Application\DeskPRO\App;

class UserKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\UserBundle\UserBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/user/config_'.$this->getEnvironment().'.yml');
	}
}