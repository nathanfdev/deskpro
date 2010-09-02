<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Kernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Kernel;

require(DP_ROOT.'/sys/autoload.php');

use \Symfony\Component\DependencyInjection\Loader\LoaderInterface;
use \Symfony\Component\DependencyInjection\ContainerBuilder;



/**
 * Kernel boots the app.
 */
class Kernel extends \Symfony\Framework\Kernel
{
    public function registerRootDir()
    {
        return DP_ROOT.'/sys';
    }

    public function registerBundles()
    {
        $bundles = array(
			new \Symfony\Framework\KernelBundle(),
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),

			new \Symfony\Bundle\ZendBundle\ZendBundle(),

			new \DeskPRO\Bundle\CoreBundle\CoreBundle(),

            new \Application\TechBundle\TechBundle(),
            new \Application\UserBundle\UserBundle(),
        );

        if ($this->isDebug()) {
			$bundles[] = new \Application\DevBundle\DevBundle();
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
	
	public function getCacheDir()
	{
		return '/home/chroder/dp400_cache' . '/' . $this->environment;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$container = new ContainerBuilder();

		$loader->load($this->getLocalConfigurationFile($this->getEnvironment()));

		return $container;
	}
}
