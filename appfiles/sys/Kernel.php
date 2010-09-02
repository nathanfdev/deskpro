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
	/**
	 * Get config data from the config.php file. This config file is meant to be user-editable.
	 * This is unlike the other config files that build the DI container and routing etc.
	 *
	 * @param string $key The config key to get, null to get the whole array
	 * @staticvar string $CONFIG
	 * @return array
	 */
	public static function getUserConfig($key = null)
	{
		static $CONFIG = null;

		if ($CONFIG === null) {
			require(DP_ROOT.'/config.php');
		}

		if ($key) {
			return isset($CONFIG[$key]) ? $CONFIG[$key] : null;
		}

		return $CONFIG;
	}

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
		$dir = self::getUserConfig('cache_dir');

		if ($dir === null) {
			return parent::getCacheDir();
		}

		$dir = str_replace('%env%', $this->environment, $dir);

		return $dir;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$container = new ContainerBuilder();

		$loader->load($this->getLocalConfigurationFile($this->getEnvironment()));

		return $container;
	}
}
