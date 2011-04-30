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

		// Set phputf8 strings
		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');

		// Lazyload exception listener for the error handler
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			if (!App::has('exception_listener')) {
				return;
			}

			$listener = App::get('exception_listener');
			$listener->handleError($errno, $errstr, $errfile, $errline);
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
}