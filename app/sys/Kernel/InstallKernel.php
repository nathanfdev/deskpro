<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use Application\DeskPRO\App;

class InstallKernel extends BaseKernel
{
	/**
	 * @param string $environment
	 * @param bool $debug
	 */
	public function __construct($environment, $debug)
	{
		parent::__construct($environment, $debug);

		$name = explode("\\", get_class($this));
		$name = array_pop($name);
		$this->name = $name;

		if (!defined('DP_DEBUG')) {
			if ($this->isDebug()) {
				define('DP_DEBUG', true);
			} else {
				define('DP_DEBUG', false);
			}
		}

		set_error_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleError', E_ALL | E_STRICT);
		set_exception_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleException');
	}


	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		parent::boot();

		$this->container->kernel = $this;
		App::$container = $this->container;
	}


	/**
	 * {@inheritDoc}
	 */
	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Application\DeskPRO\DeskPROBundle(),
			new \Application\InstallBundle\InstallBundle(),
		);

		return $bundles;
	}


	/**
	 * {@inheritDoc}
	 */
	protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
	{
		// Make sure the cache dirs exist
		$env_dir = realpath($this->getCacheDir() . '/../');
		if (!is_dir($this->getCacheDir())) {
			mkdir($this->getCacheDir(), 0777, true);
		}
		if (!file_exists($env_dir . '/doctrine-proxies')) {
			mkdir($env_dir . '/doctrine-proxies', 0777, true);
		}
		if (!file_exists($env_dir . '/twig-compiled')) {
			@mkdir($env_dir . '/twig-compiled', 0777, true);
		}

		@chmod($this->getCacheDir(), 0777);
		@chmod($env_dir . '/doctrine-proxies', 0777);
		@chmod($env_dir . '/twig-compiled', 0777);

		// Clear the dql cache when the container is regenerated as well
		$dql_cache = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . 'dql.cache';
		if (file_exists($dql_cache)) {
			@unlink($dql_cache);
		}

		// cache the container
		$dumper = new PhpDumper($container);
		$content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
		if (!$this->debug) {
			$content = self::stripComments($content);
		}

		// Re-write absolute paths to use DP_ROOT instead
		$content = str_replace("'" . DP_ROOT, 'DP_ROOT.\'', $content);
		// Correct double slash paths
		$content = str_replace('prod//', 'prod/', $content);
		// Empty logs dir that isn't used (we get it from conf)
		$content = preg_replace("#'kernel\\.logs_dir' => '(.*?)'#", "'kernel.logs_dir' => ''", $content);

		$cache->write($content, $container->getResources());
	}


	/**
	 * {@inheritDoc}
	 */
	protected function initializeContainer()
	{
		$v = libxml_disable_entity_loader(false);

		parent::initializeContainer();

		libxml_disable_entity_loader($v);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getRootDir()
	{
		return DP_ROOT.'/sys';
	}


	/**
	 * {@inheritDoc}
	 */
	public function getCacheDir()
	{
		static $cache_dir = null;

		if ($cache_dir === null) {
			if (defined('DPC_IS_CLOUD')) {
				$cache_dir = dp_get_cache_dir().'/'.$this->environment.'-cloud/';
			} else {
				$cache_dir = dp_get_cache_dir().'/'.$this->environment.'/';
			}
		}

		return $cache_dir;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getLogDir()
	{
		if (!function_exists('dp_get_log_dir')) {
			require_once DP_ROOT . '/sys/load_config.php';
		}
		return dp_get_log_dir();
	}


	/**
	 * @return array
	 */
	protected function getKernelParameters()
	{
		$params = parent::getKernelParameters();
		$params['DP_ROOT'] = DP_ROOT;

		return $params;
	}


	/**
	 * @return string
	 */
	protected function getContainerBaseClass()
	{
		return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
	}


	/**
	 * @return array
	 */
	public function registerBundleDirs()
	{
		return array(
			'Application'        => DP_ROOT.'/src/Application',
			'Bundle'             => DP_ROOT.'/src/Bundle',
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public function loadClassCache($name = 'classes', $extension = '.php')
	{

	}


	/**
	 * {@inheritDoc}
	 */
	public function setClassCache(array $classes)
	{
		if (defined('DP_BUILDING')) {
			parent::setClassCache($classes);
		}
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/install/config.php');
	}
}