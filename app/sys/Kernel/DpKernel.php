<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;

use Application\DeskPRO\App;

class DpKernel extends AbstractKernel
{
	/**
	 * @var bool
	 */
	private $has_booted = false;

	/**
	 * @var string
	 */
	private $interface;


	/**
	 * @param string $environment
	 * @param bool $debug
	 * @param string $interface
	 */
	public function __construct($environment, $debug, $interface = 'unknown')
	{
		parent::__construct($environment, $debug);

		$name = explode("\\", get_class($this));
		$name = array_pop($name);
		$this->name = $name;
		$this->interface = $interface;

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
		if ($this->has_booted) return;
		$this->has_booted = true;

		parent::boot();

		$this->container->kernel = $this;
		App::$container = $this->container;

		if ($this->container->has('deskpro.sys_events_loader')) {
			$this->container->get('deskpro.sys_events_loader');
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function initializeContainer()
	{
		if ($this->environment == 'dev') {
			$routing_cache_cleaner = new \Application\DeskPRO\Routing\CacheCleaner();
			if (!$routing_cache_cleaner->isFresh()) {
				$routing_cache_cleaner->clearCache();
			}
		}

		if ($this->environment == 'prod' && !defined('DP_BUILDING') && !defined('DPC_IS_CLOUD')) {
			// If the container doesnt exist and we're in prod, then means we're installing an update.
			// Halt now. This prevents the system from trying to generate the cache itself,
			// even though the new files will be installed in a second.
			$cache_file = $this->getCacheDir().$this->getContainerClass().'.php';
			if (!is_file($cache_file)) {
				echo HelpdeskOfflineMessage::getOfflinePage('Currently installing updates' . $cache_file);
				exit;
			}
		}

		// entity loader required to construct symfony container
		// so enable it temporarily while the container builds
		$v = libxml_disable_entity_loader(false);

		parent::initializeContainer();

		libxml_disable_entity_loader($v);
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


	/**
	 * @return string
	 */
	public function getRootDir()
	{
		return DP_ROOT.'/sys';
	}


	/**
	 * @return string
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
	 * @deprecated Use dp_get_log_dir()
	 * @return string
	 */
	public function getLogDir()
	{
		if (!function_exists('dp_get_log_dir')) {
			require_once DP_ROOT . '/sys/load_config.php';
		}
		return dp_get_log_dir();
	}


	/**
	 * {@inheritDoc}
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
	public function getInterface()
	{
		return $this->interface;
	}


	/**
	 * {@inheritDoc}
	 */
	protected function getContainerBaseClass()
	{
		return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
	}


	/**
	 * {@inheritDoc}
	 */
	public function registerBundleDirs()
	{
		$bundle_dirs = array(
			'Application'        => DP_ROOT.'/src/Application',
			'Bundle'             => DP_ROOT.'/src/Bundle',
		);

		if (defined('DPC_IS_CLOUD')) {
			$bundle_dirs['Cloud'] = DP_ROOT.'/src/Cloud';
		}

		return $bundle_dirs;
	}


	/**
	 * @param LoaderInterface $loader
	 */
	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/config_'.$this->getEnvironment().'.php');
	}


	/**
	 * {@inheritDoc}
	 */
	public function loadClassCache($name = 'classes', $extension = '.php')
	{
		// Nothing, we handle the class cache as part of the build and include it in KernelBooter
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

	####################################################################################################################

	/**
	 * {@inheritDoc}
	 */
	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\MonologBundle\MonologBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
			new \FOS\ElasticaBundle\FOSElasticaBundle(),

			new \Application\DeskPRO\DeskPROBundle(),
			new \Application\AdminInterfaceBundle\AdminInterfaceBundle(),
			new \Application\AgentBundle\AgentBundle(),
			new \Application\ReportsInterfaceBundle\ReportsInterfaceBundle(),
			new \Application\UserBundle\UserBundle(),
			new \Application\ApiBundle\ApiBundle(),
			new \Application\ImportBundle\ImportBundle(),
		);

		if (defined('DPC_IS_CLOUD')) {
			$bundles = array_merge($bundles, array(
				new \Cloud\ApiBundle\CloudApiBundle(),
				new \Cloud\AdminInterfaceBundle\CloudAdminInterfaceBundle(),
			));
		}

		return $bundles;
	}

	####################################################################################################################
	# DeskPRO Specific
	####################################################################################################################

	/**
	 * Returns a Response if the kernel shouldnt route and pass control off to a controller.
	 * Returns null if things should progress normally.
	 *
	 * @param Request $request
	 * @param int $type
	 * @param bool $catch
	 * @return null|RedirectResponse
	 */
	protected function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($this->interface == 'user' && $this->container) {
			if (
				!preg_match('#^/widget/#', $request->getPathInfo())
				&& !preg_match('#^/chat/#', $request->getPathInfo())
				&& !preg_match('#^/tickets/new-simple#', $request->getPathInfo())
				&& !preg_match('#^/tickets/new/thanks-simple/#', $request->getPathInfo())
				&& !preg_match('#^/accept-temp-upload$#', $request->getPathInfo())
				&& !preg_match('#^/logout#', $request->getPathInfo())
				&& !preg_match('#^/login#', $request->getPathInfo())
				&& (!isset($_REQUEST['_partial']) || $_REQUEST['_partial'] != 'overlayWidget')
			) {
				try {
					if (!$this->container->getSetting('user.portal_enabled')) {
						$response = new \Symfony\Component\HttpFoundation\Response('<!-- Portal Offline -->');
						return $response;
					}
				} catch (\Exception $e) {
				}
			}
		}

		if (
			(isset($GLOBALS['DP_CONFIG']['disable_url_corrections']) && $GLOBALS['DP_CONFIG']['disable_url_corrections'])
			|| strpos($request->getPathInfo(), '/admin/') === 0
			|| strpos($request->getPathInfo(), '/api/') === 0
			|| (defined('DP_INTERFACE') && DP_INTERFACE == 'admin')
		) {
			return null;
		}

		// Exclude ajax requests
		if ($request->isXmlHttpRequest()) {
			return null;
		}

		$path = $request->getPathInfo();

		$qs = $request->getQueryString();
		if ($qs) {
			$path .= '?' . $qs;
		}

		if (isset($GLOBALS['DP_CONFIG']['rewrite_urls']) && $GLOBALS['DP_CONFIG']['rewrite_urls']) {
			// Force no index.php
			if (strpos($request->getRequestUri(), '/index.php') !== false) {
				$response = new RedirectResponse(rtrim($request->getBasePath(), '/') . $path, 301);
				return $response;
			}
		} else {
			// Force index.php
			if (strpos($request->getRequestUri(), '/index.php') === false) {
				$response = new RedirectResponse(rtrim($request->getBasePath(), '/') . '/index.php' . $path, 301);
				return $response;
			}
		}

		$is_installed = App::getSetting('core.setup_initial');
		if (!$is_installed) {
			return null;
		}

		$redirect_corrections = App::getSetting('core.redirect_correct_url');
		if (!$redirect_corrections) {
			return null;
		}

		if (!$this->shouldApplyUrlCorrections($request)) {
			return null;
		}

		$now_path = $request->getPathInfo();
		if (strpos($request->getRequestUri(), '/index.php/') !== false) {
			$now_path = '/index.php' . $now_path;
		}

		$urlinfo        = parse_url(App::getSetting('core.deskpro_url'));
		if (!$urlinfo || empty($urlinfo['host']) || empty($urlinfo['scheme'])) {
			return null;
		}

		$correct_host   = strtolower($urlinfo['host']);
		if (!empty($urlinfo['port'])) {
			$correct_host .= ':' . $urlinfo['port'];
		}
		$correct_scheme = strtolower($urlinfo['scheme']);
		$now_host       = strtolower($request->getHttpHost());
		$now_scheme     = strtolower($request->getScheme());

		$do_correction = false;
		if ($correct_scheme == 'https' && $now_scheme != 'https') {
			$do_correction = true;
		} elseif ($now_host != $correct_host) {
			$do_correction = true;
		}

		if ($do_correction) {
			$url = App::getSetting('core.deskpro_url') . ltrim($now_path, '/');
			$response = new RedirectResponse($url, 301);
			return $response;
		}

		return null;
	}


	/**
	 * Executed after the requests is handled, right before it is returned to the user.
	 *
	 * @param Response $response
	 * @param Request $request
	 */
	protected function postResponseHandled(Response $response, Request $request)
	{
		global $DP_CONFIG;

		if (!(defined('install') && DP_INTERFACE == 'install')) {
			if (session_id() != '') {
				if ($this->container->isServiceInitialized('session')) {
					$this->container->get('session')->save();
				}
				session_write_close();
			}
		}

		if (isset($DP_CONFIG['debug']['enable_log_tpl_use']) && $DP_CONFIG['debug']['enable_log_tpl_use']) {
			$loc = $this->container->get('templating.locator');
			$write = array();

			$write[] = sprintf("=== BEGIN REQUEST %s ===\nURL: %s", date('D, jS M Y H:i:s'), defined('DP_REQUEST_URL') ? DP_REQUEST_URL : 'unknown');

			foreach ($loc->getLoadedTemplates() as $x => $info) {
				$info['origin'] = str_replace(DP_ROOT, '', $info['origin']);
				$write[] = sprintf("%3d: {$info['key']} \n     -> {$info['origin']}", $x);
			}

			$write[] = '';
			$write[] = '';
			$write = implode("\n", $write);
			file_put_contents($this->getLogDir() . '/template_use.log', $write, \FILE_APPEND);
		}
	}


	/**
	 * @param Request $request
	 * @return bool
	 */
	protected function shouldApplyUrlCorrections(Request $request)
	{
		switch ($this->interface) {
			case 'admin':
			case 'api':
			case 'cli':
				return false;

			case 'agent':
				return true;

			case 'user':
				// Dont apply redirects on URLs loaded from widget
				// (eg loading an article iframe)
				if (isset($_GET['parent_url'])) {
					return false;
				}
				// Set when viewing through an iframe
				if (!empty($_COOKIE['dp_o_uri']) || !empty($_GET['dp_website_url'])) {
					return false;
				}

				// Dont auto-redirect these URLs that are used
				// in widgets and callbacks
				if (
					preg_match('#^/widget/#', $request->getPathInfo())
					|| preg_match('#^/chat/#', $request->getPathInfo())
					|| preg_match('#^/tickets/new-simple#', $request->getPathInfo())
					|| preg_match('#^/tickets/new/thanks-simple/#', $request->getPathInfo())
					|| preg_match('#^/accept-temp-upload$#', $request->getPathInfo())
					|| preg_match('#^/logout#', $request->getPathInfo())
					|| preg_match('#^/login#', $request->getPathInfo())
					|| isset($_REQUEST['_partial'])
				) {
					return false;
				}

			default:
				return false;
		}

		return true;
	}


	/**
	 * Checks settings/triggers to see if the helpdesk is offline
	 *
	 * @return bool
	 */
	public function isHelpdeskOffline()
	{
		if (isset($GLOBALS['DP_HELPDESK_DISABLED']) && $GLOBALS['DP_HELPDESK_DISABLED']) {
			return true;
		}

		// Offline setting applies to all but admin
		if (App::getSetting('core.helpdesk_disabled') && DP_INTERFACE == 'user') {
			return true;
		}

		// Offline file is inserted on cmdline upgrade,
		// we want to disable all access
		if (is_file(dp_get_data_dir() . '/helpdesk-offline.trigger')) {
			return true;
		}

		return false;
	}


	/**
	 * Checks settings to see if an auto-upgrade is pending
	 *
	 * @return bool
	 */
	public function isUpgradePending()
	{
		// Make sure filesystem and db builds are the same, or else the upgrader needs to run
		if (App::getSetting('core.deskpro_build') < DP_BUILD_TIME) {
			return true;
		}

		return false;
	}
}
