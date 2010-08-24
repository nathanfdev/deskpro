<?php

require_once DP_ROOT.'/autoload.php';

use Symfony\Components\DependencyInjection\Loader\LoaderInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class DeskproKernel extends Symfony\Framework\Kernel
{
    public function registerRootDir()
    {
        return DP_ROOT.'/deskpro';
    }

    public function registerBundles()
    {
        $bundles = array(
			new Symfony\Framework\KernelBundle(),
			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),

			new Symfony\Bundle\ZendBundle\ZendBundle(),
			new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),

			new DeskPRO\Bundle\Core\CoreBundle(),

            new Application\TechBundle\TechBundle(),
            new Application\UserBundle\UserBundle(),
        );

        if ($this->isDebug()) {
			$bundles[] = new Application\DevBundle\DevBundle();
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'        => DP_ROOT.'/src/Application',
            'Bundle'             => DP_ROOT.'/src/Bundle',
			'DeskPRO\\Bundle'    => DP_ROOT.'/src/DeskPRO/Bundle',
            'Symfony\\Bundle'    => DP_ROOT.'/vendor/symfony/src/Symfony/Bundle',
        );
    }

	protected function getLocalConfigurationFile($environment)
	{
		$basePath = __DIR__.'/config/config_';
		$file = $basePath.$environment.'_local.yml';

		if(\file_exists($file))
		{
			return $file;
		}

		return $basePath.$environment.'.yml';
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$container = new ContainerBuilder();

		$loader->load($this->getLocalConfigurationFile($this->getEnvironment()));

		return $container;
	}
}
