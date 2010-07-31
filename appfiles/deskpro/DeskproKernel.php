<?php

require_once DP_ROOT.'/src/autoload.php';

use Symfony\Framework\Kernel;
use Symfony\Components\DependencyInjection\Loader\LoaderInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class DeskproKernel extends Kernel
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
			new Symfony\Bundle\TwigBundle\Bundle(),

			new DeskPRO\DeskPROBundle(),

            new Application\DevBundle\DevBundle(),
            new Application\UserCoreBundle\UserCoreBundle(),
        );

        if ($this->isDebug()) {

        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'        => DP_ROOT.'/src/Application',
            'Bundle'             => DP_ROOT.'/src/Bundle',
			'DeskPRO'            => DP_ROOT.'/src/DeskPRO',
            'Symfony\\Bundle'    => DP_ROOT.'/vendor/symfony/src/Symfony/Framework',
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
