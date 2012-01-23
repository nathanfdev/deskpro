<?php

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
		// Normalize locale
		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

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
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		ErrorHandler::register();
		if ('cli' !== php_sapi_name()) {
			ExceptionHandler::register();
		}
	}

	public function boot()
	{
		parent::boot();
		App::setContainer($this->container, 'default');

		// Set phputf8 strings
		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');
	}


	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted) {
			$this->boot();
		}

		$response = $this->getHttpKernel()->handle($request, $type, $catch);

		$this->postResponseHandled($response);

		return $response;
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
		if (!file_exists($env_dir . '/annotations')) mkdir($env_dir . '/annotations', 0777, true);
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

		if (file_exists($this->getCacheDir() . '/annotations')) rmdir($this->getCacheDir() . '/annotations');
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
			global $DP_CONFIG;
			if (isset($DP_CONFIG['cache_dir'])) {
				$cache_dir = $DP_CONFIG['cache_dir'];
			} else {
				$name = explode('\\', get_class($this));
				$name = array_pop($name);
				$name = strtolower($name);
				$name = str_replace('kernel', '', $name);

				$cache_dir = DP_ROOT . '/sys/cache/%env%/' . $name;
			}
			$cache_dir = str_replace('%env%', $this->environment, $cache_dir);
		}

		return $cache_dir;
	}

	public function getLogDir()
	{
		static $log_dir = null;

		if ($log_dir === null) {
			global $DP_CONFIG;
			if (isset($DP_CONFIG['log_dir'])) {
				$log_dir = $DP_CONFIG['log_dir'];
			} else {
				$log_dir = DP_ROOT . '/sys/logs';
			}
		}

		return $log_dir;
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

		 // Lazyload exception listener for the generic handler
		set_error_handler(function($errno, $errstr, $errfile, $errline) {

			error_log("[$errno] $errstr ($errfile, line $errline)", 0);

			if (!App::has('deskpro.exception_logger')) {
				return;
			}

			$logger = App::get('deskpro.exception_logger');
			$logger->handleError($errno, $errstr, $errfile, $errline);
		}, E_ALL | E_STRICT);
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted) {
			$this->boot();
		}

		try {
			App::getSetting('core.license');
		} catch (\PDOException $e) {
			global $DP_CONFIG;
			if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
				$response = new RedirectResponse($request->getServerBaseUrl() . '/install/');
				return $response;
			} else {
				throw $e;
			}
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

			// If we dont have a license, then we are allowed to view exactly four sections:
			// 1) /admin/login               Logging in
			// 2) /admin/welcome             Initial config
			// 3) /admin/setup/default-smtp  Setting up outgoing email
			// 4) /admin/license             Setting up the license

			if (
				!License::getLicense()->hasLicense()
				&& !preg_match('#^/admin/login#', $path)
				&& !preg_match('#^/admin/welcome#', $path)
				&& !preg_match('#^/admin/setup/default-smtp#', $path)
				&& !preg_match('#^/admin/license#', $path)
			) {
				$response = new RedirectResponse($request->getServerBaseUrl() . '/admin/license');
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
						$response = new RedirectResponse($request->getServerBaseUrl() . '/admin/agents');
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
					$response = new RedirectResponse($request->getServerBaseUrl() . '/admin/license');
					return $response;
				} else {
					die('[LIC ERR 2] License has expired');
				}
			}
		}

		$this->postResponseHandled($response);

		return $response;
	}

	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
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
}


###############################################################################
# AgentKernel
###############################################################################

class AdminKernel extends AbstractKernel
{
	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AdminBundle\AdminBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/admin/config_'.$this->getEnvironment().'.php');
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

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/install/config.php');
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
# SysKernel
###############################################################################

class SysKernel extends \DeskPRO\Kernel\BaseAbstractKernel
{
	public function registerBundles()
	{
		$bundles = array(
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Application\DeskPRO\DeskPROBundle(),
			new \Application\SysBundle\SysBundle(),
		);

		if ($this->isDebug()) {
			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
			$bundles[] = new \Application\DevBundle\DevBundle();
			$bundles[] = new \Profiler\LiveBundle\ProfilerLiveBundle();
		}

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/sys/config_'.$this->getEnvironment().'.php');
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
			new \Application\UserBundle\UserBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/user/config_'.$this->getEnvironment().'.php');
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
	 * @param $license_code
	 * @return \DeskPRO\Kernel\License
	 */
	public static function create($license_code, $install_key = '')
	{
		if (!defined('DP_LIC_SERVER')) {
			define('DP_LIC_SERVER', 'http://dev.deskprodev.com/lic/index.php');
		}

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
