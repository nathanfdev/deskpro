<?php

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

use Application\DeskPRO\App;

class InstallKernel extends \Symfony\Component\HttpKernel\Kernel
{
	public function __construct($environment)
	{
		$debug = true;

		// Normalize locale
		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

		parent::__construct($environment, $debug);

		$name = explode("\\", get_class($this));
		$name = array_pop($name);
		$this->name = $name;

		define('DP_DEBUG', true);
		App::setKernel($this);
	}

	public function boot()
	{
		parent::boot();
		App::setContainer($this->container, 'default');

		// Set phputf8 strings
		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');
	}

	protected function getContainerClass()
	{
		$parts = explode('\\', get_class($this));
		$basename = array_pop($parts);

		$container_name = $basename;
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
			new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Application\DeskPRO\DeskPROBundle(),
			new \Application\InstallBundle\InstallBundle(),
		);

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

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/install/config.yml');
	}

	protected function getContainerBaseClass()
	{
		return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
	}
}
