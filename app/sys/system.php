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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Application\DeskPRO\App;

###############################################################################
# BaseAbstractKernel
###############################################################################

abstract class BaseAbstractKernel extends \Symfony\Component\HttpKernel\Kernel
{
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

		App::setKernel($this);
	}

	public function init()
	{
		set_error_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleError', E_ALL | E_STRICT);
		set_exception_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleException');
	}

	public function boot()
	{
		static $has_booted = false;
		if ($has_booted) return;
		$has_booted = true;

		parent::boot();
		App::setContainer($this->container, 'default');
		$this->container->kernel = $this;
	}


	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted) {
			$this->boot();
		}

		$response = $this->preResponseHandled($request, $type, $catch);
		if ($response) {
			return $response;
		}

		$response = $this->getHttpKernel()->handle($request, $type, $catch);

		$this->postResponseHandled($response);

		return $response;
	}

	protected function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		return null;
	}

	protected function postResponseHandled($response)
	{
		if (session_id() !== '') {
			session_write_close();
		}
	}

	protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
	{
		// Make sure the cache dirs exist
		$env_dir = realpath($this->getCacheDir() . '/../');
		if (!file_exists($env_dir . '/doctrine-proxies')) mkdir($env_dir . '/doctrine-proxies', 0777, true);
		if (!file_exists($env_dir . '/twig-compiled')) mkdir($env_dir . '/twig-compiled', 0777, true);

		// cache the container
		$dumper = new PhpDumper($container);
		$content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
		if (!$this->debug) {
			$content = self::stripComments($content);
		}

		// Re-write absolute paths to use DP_ROOT instead
		$content = str_replace("'" . DP_ROOT, 'DP_ROOT.\'', $content);

		$cache->write($content, $container->getResources());
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

	public function getCacheDir()
	{
		static $cache_dir = null;

		if ($cache_dir === null) {
			$cache_dir = DP_ROOT . '/sys/cache/%env%/';
			$cache_dir = str_replace('%env%', $this->environment, $cache_dir);
		}

		return $cache_dir;
	}

	public function getUserLogDir()
	{
		return dp_get_log_dir();
	}

	public function getLogDir()
	{
		return $this->getUserLogDir();
	}

	public function getBackupDir()
	{
		return dp_get_backup_dir();
	}

	public function getBlobDir()
	{
		return dp_get_blob_dir();
	}

	protected function getKernelParameters()
	{
		$params = parent::getKernelParameters();
		$params['DP_ROOT'] = DP_ROOT;

		return $params;
	}

	protected function getContainerBaseClass()
	{
		return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
	}

	public function registerBundleDirs()
	{
		return array(
			'Application'        => DP_ROOT.'/src/Application',
			'Bundle'             => DP_ROOT.'/src/Bundle',
			'Symfony\\Bundle'    => DP_ROOT.'/vendor/symfony/src/Symfony/Bundle',
		);
	}

	public function loadClassCache($name = 'classes', $extension = '.php')
	{

	}

	public function setClassCache(array $classes)
	{
		if (defined('DP_BUILDING')) {
			parent::setClassCache($classes);
		}
	}
}


###############################################################################
# AbstractKernel
###############################################################################

/**
 * Abstract kernel defines the basic kernel features
 * used by all others.
 */
abstract class AbstractKernel extends BaseAbstractKernel
{
	public function boot()
	{
		parent::boot();
		$this->container->get('deskpro.sys_events_loader');
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted) {
			$this->boot();
		}

		if (!deskpro_install_check_pdo_mysql()) {
			$response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
			return $response;
		}

		try {
			App::getSetting('core.license');
		} catch (\PDOException $e) {
			global $DP_CONFIG;
			if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
				$response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
				return $response;
			} else {
				throw $e;
			}
		}

        if(!App::getSetting('core.install_timestamp')) {
            $response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
            return $response;
        }

		// Make sure we arent offline
		if ($this->isHelpdeskOffline()) {
			$response = new Response();
			$response->setContent(file_get_contents(DP_ROOT . '/src/Application/DeskPRO/Resources/views/helpdesk-disabled.html'));
			return $response;
		}

		if (!isset($GLOBALS['DP_CONFIG']['rewrite_urls'])) {
			$GLOBALS['DP_CONFIG']['rewrite_urls'] = App::getSetting('core.rewrite_urls');
		}

		// Do version check
		if (isset($GLOBALS['DP_CONFIG']['debug']['dev']) && $GLOBALS['DP_CONFIG']['debug']['dev'] && file_exists(DP_ROOT.'/sys/VERSION')) {
			$file_v = (int)file_get_contents(DP_ROOT.'/sys/VERSION');
			$db_v   = (int)App::getSetting('core.deskpro_version');

			if ($db_v && $db_v < $file_v) {
				$url = $request->getBaseUrl() . '/index.php?_sys=dev_run_migrations&_=' . md5_file(DP_CONFIG_FILE);
				$response = new RedirectResponse($url);
				return $response;
			}
		}

		// Kernels might have work to do before loading a page
		// This is where index.php checks take place
		$res = $this->preResponseHandled($request, $type, $catch);
		if ($res) {
			return $res;
		}

		/** @var $response \Symfony\Component\HttpFoundation\Response */
		$response = $this->getHttpKernel()->handle($request, $type, $catch);

		#------------------------------
		# License checks
		#------------------------------

		if ($response->headers->get('content-type') == 'text/html' && $type == HttpKernelInterface::MASTER_REQUEST) {
			$path = $request->getPathInfo();

			#------------------------------
			# No license
			#------------------------------

			// If we dont have a license or not completed installs, then we are allowed to view exactly four sections:
			// 1) /admin/login               Logging in
			// 2) /admin/welcome             Initial config
			// 3) /admin/setup/default-smtp  Setting up outgoing email
			// 4) /admin/license             Setting up the license

			$setup_step = App::getSetting('core.setup_initial');
			$is_installed = ($setup_step < 30 ? false : true);
			if (
				(!License::getLicense()->hasLicense() || !$is_installed)
				&& !preg_match('#^/admin/login#', $path)
				&& !preg_match('#^/admin/welcome#', $path)
				&& !preg_match('#^/admin/setup/default-smtp#', $path)
				&& !preg_match('#^/admin/license#', $path)
				&& !preg_match('#^/admin/welcome#', $path)
			) {
				$response = new RedirectResponse($request->getBaseUrl() . '/admin/welcome');
				return $response;
			}


			#------------------------------
			# Max agent checks
			#------------------------------

			if (License::getLicense()->getMaxAgents()) {
				// The main interface frame is a good place to stick this check
				if (DP_INTERFACE == 'agent' && preg_match('#^/agent(/|\?)?#', $path)) {
					$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM people WHERE is_agent = 1");
					if ($count > License::getLicense()->getMaxAgents()) {
						die('[LIC ERR 1] Too many agents');
					}
				}

				// On every admin page, redirect them to agents management, dont let them do anything else
				// Also let them use the license page to update the license!
				if (DP_INTERFACE == 'admin' && !preg_match('#^/admin/agents#', $path) && !preg_match('#^/admin/license#', $path) && !preg_match('#^/admin/login#', $path)) {
					$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM people WHERE is_agent = 1");
					if ($count > License::getLicense()->getMaxAgents()) {
						$response = new RedirectResponse($request->getBaseUrl() . '/admin/agents');
						return $response;
					}
				}
			}

			#------------------------------
			# Expiry checks
			#------------------------------

			if (License::getLicense()->isPastExpireDate()) {
				// On every admin page, redirect them to license management
				if (DP_INTERFACE == 'admin' && !preg_match('#^/admin/license#', $path) && !preg_match('#^/admin/login#', $path)) {
					$response = new RedirectResponse($request->getBaseUrl() . '/admin/license');
					return $response;
				} else {
					die('[LIC ERR 2] License has expired');
				}
			}
		}

		$this->postResponseHandled($response);

		if ($response->headers->get('Content-Type') == 'text/html') {
			$content = $response->getContent();
			$content = str_replace('<head>', "<head>\n\t<meta name=\"Generator\" content=\"DeskPRO ".DP_BUILD_TIME."\" />", $content);
			$response->setContent($content);
		}

		return $response;
	}

	public function isHelpdeskOffline()
	{
		if (App::getSetting('core.helpdesk_disabled') || is_file(DP_ROOT.'/helpdesk-offline.trigger')) {
			return true;
		}

		return false;
	}

	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\MonologBundle\MonologBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
			new \Application\DeskPRO\DeskPROBundle(),
		);

		$bundles = array_merge($bundles, $this->registerAdditionalBundles());

		if ($this->isDebug()) {
			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
			$bundles[] = new \Application\DevBundle\DevBundle();
			$bundles[] = new \Profiler\LiveBundle\ProfilerLiveBundle();
		}

		return $bundles;
	}

	abstract protected function registerAdditionalBundles();


	/**
	 * Returns a Response if the kernel shouldnt route and pass control off to a controller.
	 * Returns null if things should progress normally.
	 *
	 * @return \Symfony\Component\HttpFoundation\Request|null
	 */
	protected function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$path = $request->getPathInfo();

		// Excluse ajax requests
		if ($request->isXmlHttpRequest()) {
			return null;
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

		return null;
	}
}


###############################################################################
# AdminKernel
###############################################################################

class AdminKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AdminBundle\AdminBundle(),

			// Needed for templates etc
			new \Application\UserBundle\UserBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/admin/config_'.$this->getEnvironment().'.php');
	}
}


###############################################################################
# AdminKernel
###############################################################################

class BillingKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\BillingBundle\BillingBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/billing/config_'.$this->getEnvironment().'.php');
	}
}


###############################################################################
# AgentKernel
###############################################################################

class AgentKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AgentBundle\AgentBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/agent/config_'.$this->getEnvironment().'.php');
	}
}


###############################################################################
# CliKernel
###############################################################################

class CliKernel extends AgentKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = parent::registerAdditionalBundles();
		$bundles[] = new \Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle();

		return $bundles;
	}

	public function boot($mode = 'cli')
	{
		parent::boot();

		if ($mode == 'cron') {
			$this->runCronBootChecks();
		}
	}

	protected function runCronBootChecks()
	{
		$server_check = new \Application\InstallBundle\Install\ServerChecks();
		$server_check->setMode('cron');
		$server_check->checkServer();

		try {
			$server_check->checkDatabase(App::getConfig('db'));
		} catch (\Exception $e) {}

		if ($server_check->hasFatalErrors()) {
			$this->handleCronBootErrors($server_check);
			exit(1);
		}
	}

	protected function handleCronBootErrors(\Application\InstallBundle\Install\ServerChecks $server_check)
	{
		$errors = $server_check->getFatalErrors();

		$error_messages = array();
		$error_codes = array();
		foreach ($errors as $k => $e) {
			$error_messages[] = $e['message'];
			$error_codes[] = $k;
		}

		$msg = "There are problems with your server that prevent DeskPRO from executing this command:\n\n";
		$msg .= '- ' . implode("\n- ", $error_messages);
		$msg .= "\n\n###\n\n";

		foreach ($error_codes as $code) {
			$msg .= "error:$code\n";
		}

		$ini_path = deskpro_install_guess_phpini_path();
		if ($ini_path) {
			$msg .= "ini_path: $ini_path\n";
		}

		echo $msg;

		$db_write = false;
		if (!$server_check->hasErrorType('pdo_ext') && !$server_check->hasErrorType('pdo_mysql_ext') && !$server_check->hasDbErrors()) {
			try {
				$db = App::getDb();
				$db->replace('install_data', array(
					'build' => 1,
					'name'  => 'cron_run_errors',
					'data' => $msg
				));
				$db_write = true;
			} catch (\Exception $e) {
				$db_write = false;
			}
		}

		if (!$db_write) {
			@file_put_contents(dp_get_log_dir().'/cron-boot-errors.log', $msg);
		}
	}
}


###############################################################################
# InstallKernel
###############################################################################

class InstallKernel extends \DeskPRO\Kernel\BaseAbstractKernel
{
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

	/**
	 * The installer should be okay with a log dir that isnt writable because the user will
	 * be told about it on the next page. The log dir before that is used in dev mode when the install
	 * kernel is being built, so for that time we can just use the cache dir.
	 *
	 * @return string
	 */
	public function getLogDir()
	{
		$log_dir = parent::getLogDir();
		if (!is_writable($log_dir)) {
			return $this->getCacheDir();
		}

		return $log_dir;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/install/config.php');
	}

	public function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		return null;
	}
}


###############################################################################
# ReportKernel
###############################################################################

class ReportKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\ReportBundle\ReportBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/report/config_'.$this->getEnvironment().'.php');
	}
}


###############################################################################
# UserKernel
###############################################################################

class UserKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AgentBundle\AgentBundle(), // so templates can work when notiying
			new \Application\UserBundle\UserBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/user/config_'.$this->getEnvironment().'.php');
	}


	public function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$setup_step = App::getSetting('core.setup_initial');
		$is_installed = ($setup_step < 30 ? false : true);
		if (!$is_installed) {
			return null;
		}

		$response = parent::preResponseHandled($request, $type, $catch);
		if ($response) {
			return $response;
		}

		$redirect_corrections = App::getSetting('core.redirect_correct_url');
		if (!$redirect_corrections) {
			return null;
		}

		$now_path = $request->getPathInfo();
		if (strpos($request->getRequestUri(), '/index.php/') !== false) {
			$now_path = '/index.php' . $now_path;
		}

		$urlinfo        = parse_url(App::getSetting('core.deskpro_url'));
		$now_host       = strtolower($urlinfo['host']);
		$now_scheme     = strtolower($urlinfo['scheme']);
		$correct_host   = strtolower($request->getHttpHost());
		$correct_scheme = strtolower($request->getScheme());

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
}


###############################################################################
# ExceptionHandler
###############################################################################

class KernelErrorHandler
{
	public static $is_logging = false;
	public static $wrote_log_file = false;
	public static $wrote_php_log = false;

	public static function handleError($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			return;
		}

		$errinfo = self::getErrorInfo($errno, $errstr, $errfile, $errline);
		self::logErrorInfo($errinfo);

		if ($errinfo['display']) {
			echo $errinfo['summary'];

			if (isset($GLOBALS['DP_IS_IN_CLI'])) {
				if (self::$wrote_log_file) echo "\n(Refer to " . self::$wrote_log_file . " for details)\n";
				if (self::$wrote_php_log) echo "\n(Refer to the PHP erorr log for details)\n";
			}
		}

		try {
			if (!empty($GLOBALS['DP_ERR_LOGGER'])) {
				$logger = $GLOBALS['DP_ERR_LOGGER'];
				$logger->log($errinfo['summary'] . "\n" . $errinfo['trace'], 'ERR', array('errinfo' => $errinfo));
			}
		} catch (\Exception $e) {}

		if ($errinfo['die']) {
			self::tryCleanup();
			exit(1);
		}
	}

	public static function handleException(\Exception $exception)
	{
		$errinfo = self::getExceptionInfo($exception);
		self::logErrorInfo($errinfo);

		if ($errinfo['display']) {
			echo $errinfo['summary'];

			if (isset($GLOBALS['DP_IS_IN_CLI'])) {
				if (self::$wrote_log_file) echo "\n(Refer to " . self::$wrote_log_file . " for details)\n";
				if (self::$wrote_php_log) echo "\n(Refer to the PHP erorr log for details)\n";
			}
		}

		try {
			if (!empty($GLOBALS['DP_ERR_LOGGER'])) {
				$logger = $GLOBALS['DP_ERR_LOGGER'];
				$logger->log($errinfo['summary'] . "\n" . $errinfo['trace'], 'ERR', array('errinfo' => $errinfo));
			}
		} catch (\Exception $e) {}

		if ($errinfo['die']) {

			self::tryCleanup();

			$code = (int)$errinfo['exception']->getCode();
			if ($code > 255) $code = 255;
			if ($code == 0) $code = 1;
			exit($code);
		}
	}

	public static function tryCleanup()
	{
		if (class_exists('Application\DeskPRO\App')) {
			try {
				$db = App::getDb();
				if ($db->isTransactionActive()) {
					$db->rollback();
				}
			} catch (\Exception $e) {}
		}
	}

	public static function logErrorInfo(array $errinfo)
	{
		if (self::$is_logging) return;;
		self::$is_logging = true;

		if (!class_exists('Application\DeskPRO\App')) {
			return null;
		}

		self::logToFile($errinfo);
		unset($errinfo['exception']);

		try {
			$logger = App::createNewLogger('error_log', null);
			$logger->log($errinfo['summary'], $errinfo['pri'], $errinfo);
		} catch (\Exception $e) {}

		try {
			if (App::getConfig('debug.email_on_error')) {
				$message = App::getMailer()->createMessage();
				$message->setTo(App::getConfig('debug.email_on_error'));
				$message->setSubject("[DeskPRO Error] {$errinfo['summary']}");
				$message->setBody(print_r($errinfo, true));
				App::getMailer()->send($message);
			}
		} catch (\Exception $e) {}

		self::$is_logging = false;
	}

	public static function logToFile(array $errinfo)
	{
		self::$wrote_log_file = false;
		self::$wrote_php_log = false;

		$str = array();
		if ($errinfo['type'] == 'exception') {
			$e = $errinfo['exception'];
			$str[] = sprintf("[%s] Exception %s %s\n", date('Y-m-d H:i:s'), $e->getCode(), $e->getMessage());
			$str[] = sprintf("\t-> Type: %s\n", $errinfo['exception_type']);
			$str[] = sprintf("\t-> Line %d on file %s\n", $errinfo['errline'], $errinfo['errfile']);
		} else {
			$str[] = sprintf("[%s] Error %s\n", date('Y-m-d H:i:s'), $errinfo['errstr']);
			$str[] = sprintf("\t-> Type: %s\n", $errinfo['errname']);
			$str[] = sprintf("\t-> Line %d on file %s\n", $errinfo['errline'], $errinfo['errfile']);
		}

		$errinfo['trace'] = trim($errinfo['trace']);
		if ($errinfo['trace']) {
			$lines = explode("\n", $errinfo['trace']);
			foreach ($lines as $l) {
				$str[] = sprintf("\t-> %s\n", trim($l));
			}
		}

		$str = implode('', $str);

		$written = false;
		if (class_exists('Application\DeskPRO\App') && App::getLogDir() && ($fh = @fopen(App::getLogDir() . '/error.log', 'a')) !== false) {
			$written = @fwrite($fh, $str);
			@fclose($fh);

			if ($written) {
				self::$wrote_log_file = App::getLogDir() . '/error.log';
			}
		}

		// Try to write to php error log instead
		if (!$written) {
			@ini_set('log_errors_max_len', strlen($str));
			@error_log($str, 0);

			self::$wrote_php_log = true;
		}
	}

	public static function getExceptionInfo(\Exception $exception)
	{
		$errno = $exception->getCode();
		$errstr = $exception->getMessage();
		$errfile = self::stripPathPrefix($exception->getFile());
		$errline = $exception->getLine();

		$backtrace = $exception->getTrace();
		$trace = self::formatBacktrace($backtrace);
		$trace = self::stripPathPrefix($trace);

		$type = get_class($exception);
		$summary = "[EXCEPTION] $type:$errno $errstr ($errfile:$errline)";

		$display = true;
		if (!(error_reporting() & E_ERROR)) {
			$display = false;
		}

		return array(
			'type'           => 'exception',
			'session_name'   => isset($exception->_dp_sn) ? $exception->_dp_sn : null,
			'exception'      => $exception,
			'exception_type' => get_class($exception),
			'die'            => true,
			'pri'            => 'ERR',
			'trace'          => $trace,
			'summary'        => $summary,
			'errstr'         => $errstr,
			'errname'        => 'EXCEPTION',
			'errno'          => $errno,
			'errfile'        => $errfile,
			'errline'        => $errline,
			'display'        => $display,
		);
	}

	public static function getErrorInfo($errno, $errstr, $errfile, $errline)
	{
		$die = false;
		switch ($errno) {
			case E_ERROR:
				$die = true;
				$pri = 'ERR';
				$errname = "E_ERROR";
				break;

			case E_WARNING:
			case E_USER_WARNING:
				$pri = 'WARN';
				$errname = "E_WARNING";
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$pri = 'NOTICE';
				$errname = "E_NOTICE";
				break;

			case E_STRICT:
				$pri = 'STRICT';
				$errname = "E_STRICT";
				break;

			case E_RECOVERABLE_ERROR:
				$pri = 'ERR';
				$errname = "E_RECOVERABLE_ERROR";
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$pri = 'NOTICE';
				$errname = "E_DEPRECATED";
				break;
		}

		$display = true;
		if (!(error_reporting() & $errno)) {
			$display = false;
		}

		$errfile = self::stripPathPrefix($errfile);

		$backtrace = debug_backtrace();
		$trace = self::formatBacktrace($backtrace);
		$trace = self::stripPathPrefix($trace);

		$summary = "[$errname:$errno] $errstr ($errfile:$errline)";

		return array(
			'type'         => 'error',
			'session_name' => null,
			'die'          => $die,
			'pri'          => $pri,
			'trace'        => $trace,
			'summary'      => $summary,
			'errstr'       => $errstr,
			'errname'      => $errname,
			'errno'        => $errno,
			'errfile'      => $errfile,
			'errline'      => $errline,
			'display'      => $display,
		);
	}

	public static function stripPathPrefix($content)
	{
		$prefix = DP_ROOT . '/';
		$content = str_replace($prefix, '', $content);

		$prefix = DP_WEB_ROOT . '/';
		$content = str_replace($prefix, '', $content);

		return $content;
	}

	public static function formatBacktrace(array $backtrace)
	{
		$trace = '';

		$longest = 100;

		foreach($backtrace as $k=>$v){

			$prefix = "#$k ";
			$line = '';

			if (!empty($v['file'])) {
				$prefix .= "[{$v['file']}:{$v['line']}] ";
			}

			if (isset($v['object'])) {
				$line .= get_class($v['object']) . "::";
			} elseif (isset($v['class'])) {
				$line .= $v['class'] . "::";
			}

			$line .= "{$v['function']}(";

			if (!empty($v['args'])) {
				$line .= self::varToString($v['args']);
			}

			$line .= ")";

			$trace .= $prefix . ' ' . trim($line) . "\n";
		}

		return trim($trace);
	}

	public static function varToString($var, $_depth = 0)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
				if ($_depth > 8) {
					$a[] = sprintf('%s => %s', $k, '(string)');
				} else {
					$a[] = sprintf('%s => %s', $k, self::varToString($v, $_depth+1));
				}
            }
            return sprintf("[array](%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }
        return str_replace("\n", '', var_export((string) $var, true));
    }
}

###############################################################################
# License
###############################################################################

final class License
{
	/**
	 * @var \DeskPRO\Kernel\License
	 */
	static private $inst;

	/**
	 * @var string
	 */
	private $license_id;

	/**
	 * @var string
	 */
	private $license_salt;

	/**
	 * @var string
	 */
	private $install_key;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * When non-null, then it means there was a problem with the license (ie bad format).
	 * The License class goes into unlicensed mode in these cases, but if there was
	 * a license code but it was just invalid, then you can always check this.
	 * @var string
	 */
	private $error_code = null;


	/**
	 * @static
	 * @return string
	 */
	public static function getLicServer()
	{
		if (!defined('DP_LIC_SERVER')) {
			define('DP_LIC_SERVER', 'http://dev.deskprodev.com/lic/index.php');
		}

		return DP_LIC_SERVER;
	}


	/**
	 * @static
	 * @param $license_code
	 * @return \DeskPRO\Kernel\License
	 */
	public static function create($license_code, $install_key = '')
	{
		self::getLicServer();

		$inst = new self($license_code, $install_key);

		// First invocation always the singleton used for lic checks
		if (!self::$inst) {
			self::$inst = $inst;
		}

		return $inst;
	}


	/**
	 * @static
	 * @return \DeskPRO\Kernel\License
	 */
	public static function getLicense()
	{
		if (!self::$inst) {
			if (defined('DP_LIC_FILE')) {
				$license_code = file_get_contents(DP_LIC_FILE);
			} elseif (defined('DP_LIC_STR')) {
				$license_code = DP_LIC_STR;
			} else {
				$license_code = App::getSetting('core.license');
				if (!$license_code) $license_code = null;
			}


			if (defined('DP_INSTALL_KEY')) {
				$install_key = DP_INSTALL_KEY;
			} else {
				$install_key = App::getSetting('core.install_key');
			}

			self::create($license_code, $install_key);
		}

		return self::$inst;
	}


	/**
	 * $license_code is a combined string in the form of:
	 *
	 *     <license id><license salt><encrypted license code>
	 *
	 * The license id is like: ASDD-2000-GGHF (14 chars)
	 * The license salt is like: JKHNNSDSD90809SJHDJK (20 chars)
	 * The encrypted bit is a base64 encoded string (remaining)
	 *
	 * @param $license_code
	 */
	private function __construct($license_code, $install_key = '')
	{
		// "no license" mode
		if ($license_code === null) {
			$this->data = array('no_license' => true);
			return;
		}

		if (strlen($license_code) < 300) {
			$this->error_code = 'invalid_license_code_1';
			$this->data = array('no_license' => true);
			return;
		}

		$license_code = trim($license_code);
		$license_code = str_replace(array("\n", "\r", " ", "\t"), "", $license_code);
		$license_code = base64_decode($license_code);

		$this->license_id   = substr($license_code, 0, 14);
		$this->license_salt = substr($license_code, 14, 20);
		$this->install_key  = $install_key;

		$enc  = substr($license_code, 34);
		$enc = strrev($enc);

		$key  = sha1($this->license_id . $this->license_salt . $this->install_key . '5hIT4WRxHRDP70afPyBwph3wMeAGOVK69zIL62zcS') . '7ucrx3ghJwt7m3MNwvhXcddAskF0tLTMpIU3GMK6X';
		$key .= sha1($this->license_id . $this->license_salt . $this->install_key . 'aPRfHzg1EHDXtQdXYOlRGrvKJmP7G0UPo4SmLIqt4') . 'djqhyJa40ucOWDGhQ3taSppI8D5Gpyeoc9BlcIlYv';
		$key  = $key . strrev($key);

		$enc = $this->xorString($enc, $key);

		$enc = base64_decode($enc);
		$data = @unserialize($enc);

		$this->data = $data;

		if (!$data) {
			$this->error_code = 'invalid_license_code_2';
			$this->data = array('no_license' => true);
			return;
		}
	}

	public function getLicenseId()
	{
		return $this->license_id;
	}

	public function isDemo()
	{
		return isset($this->data['demo']) && $this->data['demo'];
	}

	public function getMaxAgents()
	{
		if (!isset($this->data['agents']) || !$this->data['agents']) {
			return 0;
		}

		return $this->data['agents'];
	}

	public function getExpireDate()
	{
		if (!isset($this->data['expire']) || !$this->data['expire']) {
			return null;
		}

		return new \DateTime("@" . $this->data['expire']);
	}

	public function isPastExpireDate()
	{
		$date = $this->getExpireDate();
		if (!$date) {
			return false;
		}

		$now = new \DateTime();
		if ($now > $date) {
			return true;
		}

		return false;
	}

	public function hasLicense()
	{
		return !isset($this->data['no_license']);
	}

	public function isLicenseCodeError()
	{
		return $this->error_code !== null;
	}

	public function getLicenseCodeError()
	{
		return $this->error_code;
	}

	public function get($key, $default = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}

	public function has($key)
	{
		return isset($this->data[$key]);
	}

	private function xorString($string, $key)
	{
		$string_len  = strlen($string);
		$key_len     = strlen($key);
		$new_string  = array();

		for ($i = 0, $j = 0; $i < $string_len; $i++, $j++) {
			if ($j >= $key_len) $j = 0;

			$new_string[] = chr(ord($string[$i]) ^ ord($key[$j]));
		}

		$new_string = implode('', $new_string);

		return $new_string;
	}
}
