<?php

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

use Application\DeskPRO\App;

/**
 * Abstract kernel defines the basic kernel features
 * used by all others.
 */
abstract class AbstractKernel extends \Symfony\Component\HttpKernel\Kernel
{
	public function __construct($environment, $debug)
	{
		// Normalize locale
		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

		parent::__construct($environment, $debug);

		$name = explode("\\", get_class($this));
		$name = array_pop($name);
		$this->name = $name;

		if ($this->isDebug()) {
			define('DP_DEBUG', true);
		} else {
			define('DP_DEBUG', false);
		}

		App::setKernel($this);
	}

	public function boot()
	{
		require(DP_ROOT.'/src/Application/DeskPRO/compat.php');

		parent::boot();
		App::setContainer($this->container, 'default');
		$this->container->get('deskpro.sys_events_loader');

		// Set phputf8 strings
		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');

		 // Lazyload exception listener for the generic handler
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			if (!App::has('deskpro.exception_logger')) {
				return;
			}

			$logger = App::get('deskpro.exception_logger');
			$logger->handleError($errno, $errstr, $errfile, $errline);
		}, E_ALL | E_STRICT);
	}

	protected function getContainerClass()
	{
		$parts = explode('\\', get_class($this));
		$basename = array_pop($parts);

		$container_name = $basename;
		if ($this->environment != 'prod') {
			$container_name .= ucfirst($this->environment);
		}
		if ($this->debug) {
			$container_name .= 'Debug';
		}
		$container_name .= 'Container';

		return $container_name;
	}

	public function getRootDir()
	{
		return DP_ROOT.'/sys';
	}

	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
			new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

			new \Application\DeskPRO\DeskPROBundle(),
		);

		$bundles = array_merge($bundles, $this->registerAdditionalBundles());

		if ($this->isDebug()) {

			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Application\DevBundle\DevBundle();
		}

		return $bundles;
	}

	abstract protected function registerAdditionalBundles();

	public function registerBundleDirs()
	{
		return array(
			'Application'        => DP_ROOT.'/src/Application',
			'Bundle'             => DP_ROOT.'/src/Bundle',
			'Symfony\\Bundle'    => DP_ROOT.'/vendor/symfony/src/Symfony/Bundle',
		);
	}

	public function getCacheDir()
	{
		return App::getCacheDir();
	}

	public function getLogDir()
	{
		return App::getLogDir();
	}

	protected function getKernelParameters()
	{
		$params = parent::getKernelParameters();
		$params['DP_ROOT'] = DP_ROOT;

		return $params;
	}

	/**
     * Returns the file path for a given resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     @<BundleName>/path/to/a/file.something
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * If $dir is passed, and the first segment of the path is "Resources",
     * this method will look for a file named:
     *
     *     $dir/<BundleName>/path/without/Resources
     *
     * before looking in the bundle resource folder.
     *
     * @param string  $name  A resource name to locate
     * @param string  $dir   A directory where to look for the resource first
     * @param Boolean $first Whether to return the first path or paths for all matching bundles
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe
     * @throws \RuntimeException         if a custom resource is hidden by a resource in a derived bundle
     */
    public function locateResource($name, $dir = null, $first = true)
    {
		$files = $this->locatePluginResource($name, $dir, $first);
		if ($files) {
			return $files;
		}

		return parent::locateResource($name, $dir, $first);
    }

	public function locatePluginResource($name, $dir = null, $first = true)
	{
		$name = substr($name, 1);
        list($bundleName, $path) = explode('/', $name, 2);
		$files = array();

		if (isset($this->bundleMap[$bundleName])) {
			return false;
		}

		// Plugin resources come from wherever the plugin says is the path to the resources dir
		if (strpos($path, '/Resources/') !== null AND $this->container->has('deskpro.plugin_manager')) {
			$plugin_manager = $this->container->get('deskpro.plugin_manager');
			if ($plugin_manager->hasPlugin($bundleName)) {
				$path = str_replace('Resources/', DP_ROOT . '/plugins' . $plugin_manager->getResourcesPath($bundleName), $path);

				if ($first) {
					return $path;
				}

				$files[] = $path;
			}

			return $files;
		}

		return null;
	}

	protected function getContainerBaseClass()
    {
        return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
    }
}
