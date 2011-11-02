<?php

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

use Application\DeskPRO\App;

class ReportKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\ReportBundle\ReportBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/report/config_'.$this->getEnvironment().'.yml');
	}
}