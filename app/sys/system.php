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

require_once DP_ROOT.'/sys/DpShutdown.php';
require_once DP_ROOT.'/sys/Kernel/KernelErrorHandler.php';
require_once DP_ROOT.'/sys/Kernel/BaseAbstractKernel.php';

abstract class AbstractKernel extends BaseAbstractKernel
{
	final public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted) {
			$this->boot();
		}

		$path = $request->getPathInfo();

		if (!deskpro_install_check_pdo_mysql()) {
			$response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
			return $response;
		}

		try {
			App::getSetting('core.license');
		} catch (\PDOException $e) {
			global $DP_CONFIG;
			if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
				// This will show an error page if already installed, so the redirect to install wont happen
				deskpro_handle_boot_db_exception($e);

				$response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
				return $response;
			} else {
				// Ignore connection related errors on installer
				if (DP_INTERFACE != 'install') {
					throw $e;
				}
			}
		}

		if (!App::getSetting('core.install_build') && strpos($request->getRequestUri(), '/index.php/install/') === false) {
			$response = new RedirectResponse($request->getBasePath() . '/index.php/install/');
			return $response;
		}

		// Make sure filesystem and db builds are the same, or else the upgrader needs to run
		if (App::getSetting('core.deskpro_build') < DP_BUILD_TIME) {
			// Show info to agent/admin interface
			if (preg_match('#^/admin/?#', $path) || preg_match('#^/agent/?#', $path)) {
				echo deskpro_install_basic_error("
					<p>
						It appears as though you have recently upgraded the DeskPRO source files, but you have not performed the required database upgrades.
					</p>
					<p style='margin: 6px 0 6px 0;'>
						To complete the upgrade process, execute the upgrade command to bring your database up to date:
					</p>
					<div style=\"font-family: 'Monaco', 'Courier New', monaco; background: #fff; padding: 8px; border: 1px solid #999; \">
						/path/to/php /path/to/deskpro/upgrade.php --run-db-upgrade
					</div>
				", "DeskPRO Upgrade");
				exit;

			// Standard offline mode for users
			} else {
				$GLOBALS['DP_HELPDESK_DISABLED'] = true;
			}
		}

		// Make sure we arent offline
		if (!preg_match('#^/admin/?#', $path) && $this->isHelpdeskOffline()) {
			$response = new Response();

			$offline_message = null;
			if (file_exists(dp_get_tmp_dir() . '/helpdesk-offline-message.txt')) {
				$offline_message = file_get_contents(dp_get_tmp_dir() . '/helpdesk-offline-message.txt');
			} else {
				try {
					$offline_message = App::getSetting('core.helpdesk_disabled_message');
				} catch (\Exception $e) {}
			}

			if (!$offline_message) {
				$offline_message = 'The helpdesk is currently offline for maintenance. Please try again soon.';
			}

			$page_html = file_get_contents(DP_ROOT . '/src/Application/DeskPRO/Resources/views/helpdesk-disabled.html');
			$page_html = str_replace('{{ OFFLINE_MESSAGE }}', $offline_message, $page_html);

			$response->setContent($page_html);
			return $response;
		}

		if (!isset($GLOBALS['DP_CONFIG']['rewrite_urls'])) {
			$GLOBALS['DP_CONFIG']['rewrite_urls'] = App::getSetting('core.rewrite_urls');
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

			#------------------------------
			# No license
			#------------------------------

			// If we dont have a license or not completed installs, then we are allowed to view exactly four sections:
			// 1) /admin/login               Logging in
			// 2) /admin/welcome             Initial config
			// 3) /admin/setup/default-smtp  Setting up outgoing email
			// 4) /billing                   Setting up the license

			$is_installed = App::getSetting('core.setup_initial');
			if (
				(!License::getLicense()->hasLicense() || !$is_installed)
				&& !preg_match('#^/admin/login#', $path)
				&& !preg_match('#^/admin/welcome#', $path)
				&& !preg_match('#^/admin/setup/default-smtp#', $path)
				&& !preg_match('#^/billing#', $path)
				&& !preg_match('#^/admin/welcome#', $path)
			) {
				$response = new RedirectResponse($request->getBaseUrl() . '/admin/welcome');
				return $response;
			}

			if (
				!preg_match('#^/admin/login#', $path)
				&& !preg_match('#^/billing#', $path)
				&& !preg_match('#^/admin/upgrade#', $path)
			) {
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
					if (DP_INTERFACE == 'admin' && !preg_match('#^/admin/agents#', $path) && !preg_match('#^/billing#', $path) && !preg_match('#^/admin/login#', $path)) {
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
					if (DP_INTERFACE == 'admin' && !preg_match('#^/billing#', $path) && !preg_match('#^/billing/login#', $path)) {
						$response = new RedirectResponse($request->getBaseUrl() . '/billing');
						return $response;
					} else {
						die('[LIC ERR 2] License has expired');
					}
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
}

require_once DP_ROOT.'/sys/Kernel/AdminKernel.php';
require_once DP_ROOT.'/sys/Kernel/AgentKernel.php';
require_once DP_ROOT.'/sys/Kernel/BillingKernel.php';
require_once DP_ROOT.'/sys/Kernel/CliKernel.php';
require_once DP_ROOT.'/sys/Kernel/InstallKernel.php';
require_once DP_ROOT.'/sys/Kernel/ReportKernel.php';
require_once DP_ROOT.'/sys/Kernel/UserKernel.php';

###############################################################################
# License
###############################################################################

final class License
{
	/**
	 * @var string
	 */
	private $raw_code;

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
		if (!defined('DP_MA_SERVER')) {
			define('DP_MA_SERVER', 'http://www.deskpro.com/members');
		}

		return DP_MA_SERVER;
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
		$this->raw_code = $license_code;

		$this->install_key  = $install_key;
		if (preg_match('#@([A-Z0-9]{10})$#', $license_code, $m)) {
			$this->install_key = $m[1];
			$license_code = str_replace($m[0], '', $license_code);
		}

		$license_code = str_replace(array("\n", "\r", " ", "\t"), "", $license_code);
		$license_code = base64_decode($license_code);

		$this->license_id   = substr($license_code, 0, 14);
		$this->license_id   = rtrim($this->license_id, '-');
		$this->license_salt = substr($license_code, 14, 20);
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

	public function getLicenseCode()
	{
		return $this->raw_code;
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
