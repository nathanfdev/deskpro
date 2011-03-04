<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Kernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskPRO\Kernel;

require(DP_ROOT.'/sys/autoload.php');

use \Symfony\Component\DependencyInjection\Loader\LoaderInterface;
use \Symfony\Component\DependencyInjection\ContainerBuilder;

use \Application\DeskPRO\App;

/**
 * Kernel boots the app.
 */
class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
	public function __construct($environment, $debug)
	{
		// Normalize locale
		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

		parent::__construct($environment, $debug);
		App::setKernel($this);
	}

	public function boot()
	{
		require(DP_ROOT.'/src/Application/DeskPRO/compat.php');

		parent::boot();
		App::setContainer($this->container, 'default');

		// Set phputf8 strings
		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');

		// Lazyload exception listener for the error handler
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			if (!App::has('exception_listener')) {
				return false;
			}

			$listener = App::get('exception_listener');
			$listener->handleError($errno, $errstr, $errfile, $errline);
		}, E_ALL | E_STRICT);
	}


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
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),

			new \Symfony\Bundle\ZendBundle\ZendBundle(),
			new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

			new \Application\DeskPRO\DeskPROBundle(),
			new \Application\AdminBundle\AdminBundle(),
            new \Application\AgentBundle\AgentBundle(),
            new \Application\UserBundle\UserBundle(),
            new \Application\ApiBundle\ApiBundle(),
        );

        if ($this->isDebug()) {
			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Application\DevBundle\DevBundle();
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'        => DP_ROOT.'/src/Application',
            'Bundle'             => DP_ROOT.'/src/Bundle',
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
		return App::getCacheDir();
	}

	public function getLogDir()
    {
        return App::getLogDir();
    }

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$container = new ContainerBuilder();

		$loader->load($this->getLocalConfigurationFile($this->getEnvironment()));

		return $container;
	}
}

class KernelCli extends Kernel
{

}