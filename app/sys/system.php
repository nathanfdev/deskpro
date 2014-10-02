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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Orb\Util\Arrays;
use Orb\Util\Env;

use Application\DeskPRO\App;

require_once DP_ROOT.'/sys/DpShutdown.php';
require_once DP_ROOT.'/sys/Kernel/KernelErrorHandler.php';
require_once DP_ROOT.'/sys/Kernel/HelpdeskOfflineMessage.php';

abstract class AbstractKernel extends BaseKernel
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
		} catch (\Doctrine\DBAL\DBALException $e) {
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
				$db_build   = App::getSetting('core.deskpro_build');
				$file_build = DP_BUILD_TIME;

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
					<div style=\"padding-top: 35px; font-size: 11px; color: #777;text-align: center;\">
						<div style='display: inline-block; background: #ddd; padding: 5px 8px; border-radius: 3px;'>
							Database build: $db_build
							&nbsp;
							|
							&nbsp;
							Files build: $file_build
						</div>
					</div>
				", "DeskPRO Upgrade");
				exit;

			// Standard offline mode for users
			} else {
				$GLOBALS['DP_HELPDESK_DISABLED'] = true;
			}
		}

		if (defined('DP_INTERFACE') && DP_INTERFACE == 'user') {
			$website_url = '';
			if (!empty($_REQUEST['dp_website_url'])) {
				$website_url = $_REQUEST['dp_website_url'];
			} else if (!empty($_COOKIE['dp_o_uri'])) {
				$website_url = @base64_decode($_COOKIE['dp_o_uri'], false);
			}

			if ($website_url == 'DP_UNSET') {
				$website_url = '';
			}

			if ($website_url && (empty($_COOKIE['dp_o_uri']) || $_COOKIE['dp_o_uri'] != $website_url)) {
				setcookie('dp_o_uri', base64_encode($website_url), null, '/', null, null, true);
			} else if (!$website_url && !empty($_COOKIE['dp_o_uri'])) {
				setcookie('dp_o_uri', '', -3600, '/', null, null, true);
			}

			$GLOBALS['DP_WEBSITE_URL'] = $website_url;
		}

		if ((defined('DP_INTERFACE') && DP_INTERFACE == 'user') && $this->isHelpdeskOffline()) {
			$cache_dir = dp_get_tmp_dir() . '/page-cache';
			$base = substr(preg_replace('#[^a-z0-9_-]#i', '_', $request->getRequestUri()), 0, 35);
			$scheme_host = $request->getScheme().'://'.$request->getHttpHost();
			$cache_base_filename = $base . '-' . md5($scheme_host . $request->getRequestUri()) . '.cache';
			try {
				$language = App::getLanguage();
				$language_id = $language->id;
			} catch (\Exception $e) {
				$language_id = 1;
			}
			$cache_filename = $language_id . '-' . $cache_base_filename;
			$cache_file = $cache_dir . '/' . $cache_filename;

			if (file_exists($cache_file)) {
				// helpdesk is offline - always serve this instead
				$output = @unserialize(file_get_contents($cache_file));
				if (is_array($output)) {
					if ($output['compressed']) {
						$output['content'] = gzuncompress($output['content']);
					}

					$message = HelpdeskOfflineMessage::getOfflineMessage();
					$output['content'] = KernelBooter::prepareCachedOutputForOffline($output['content'], $message);

					return new Response($output['content'], 200, $output['headers']);
				}
			}
		}

		// Make sure we arent banned ip
		if (!preg_match('#^/admin/?#', $path)) {
			$ip = dp_get_user_ip_address();
			$ip_long = sprintf("%u", ip2long($ip));

			$banned = App::getDb()->fetchColumn("
				SELECT banned_ip
				FROM ban_ips
				WHERE banned_ip = ? OR (ip_start <= ? AND ip_end >= ?)
				LIMIT 1
			", array($ip, $ip_long, $ip_long));

			if ($banned) {
				$response = new Response();
				$response->setContent(HelpdeskOfflineMessage::getOfflinePage('The helpdesk is currently unavailable.'));
				return $response;
			}
		}

		// Make sure we arent offline
		if (!preg_match('#^/admin/?#', $path) && $this->isHelpdeskOffline()) {
			$response = new Response();
			$response->setContent(HelpdeskOfflineMessage::getOfflinePage());
			return $response;
		}

		if (!isset($GLOBALS['DP_CONFIG']['rewrite_urls'])) {
			$GLOBALS['DP_CONFIG']['rewrite_urls'] = App::getSetting('core.rewrite_urls');
		}

		// Kernels might have work to do before loading a page
		// This is where index.php checks take place
		$res = $this->preResponseHandled($request, $type, $catch);
		if ($res) {
			if (($loc = $res->headers->get('Location')) && isset($_GET['parent_url'])) {
				if (strpos($loc, '?') === false) {
					$loc .= '?';
				} else {
					$loc .= '&';
				}
				$loc .= 'parent_url=' . urlencode($_GET['parent_url']);
				$res->headers->set('Location', $loc);
			}
			return $res;
		}

		// Verify that we arent at the max vars limit which could be a problem
		if ($request->getMethod() == 'POST' && $_POST) {
			$max = Env::getMaxPostVars();
			if ($max) {
				$count = Arrays::valueCount($_POST);
				if ($count >= $max) {
					throw new \RuntimeException("Server max post vars set to {$max} and this form posted {$count} variables");
				}
			}
		}

		if (License::getLicense()->isPastExpireDate()) {
			define('DP_BILLING_ERROR', true);
		}

		/** @var $response \Symfony\Component\HttpFoundation\Response */
		$response = $this->getHttpKernel()->handle($request, $type, $catch);

		#------------------------------
		# License checks
		#------------------------------

		$is_page_load = strpos($response->headers->get('content-type'), 'text/html') !== false
			&& $type == HttpKernelInterface::MASTER_REQUEST
			&& !preg_match('#^/admin/load-view#', $path)
			&& !preg_match('#^/reports/load-view#', $path);

		if ($is_page_load
			&& (!isset($GLOBALS['DP_USING_TESTING_CONFIG']) || !$GLOBALS['DP_USING_TESTING_CONFIG'])
			&& !preg_match('#^/admin/start#', $path)
			&& !preg_match('#^/admin/login#', $path)
			&& !preg_match('#^/agent/login#', $path)
		) {
			#------------------------------
			# No license
			#------------------------------

			// If we dont have a license or not completed installs, then we are allowed to view exactly:
			// 1) /admin/login             Logging in
			// 1) /agent/login             Logging in
			// 2) /admin/start             Initial config
			$is_installed = App::getSetting('core.setup_initial');
			if (!License::getLicense()->hasLicense() || !$is_installed) {
				$response = new RedirectResponse($request->getBaseUrl() . '/admin/start');
				return $response;

			} else {

				#------------------------------
				# Max agent checks
				#------------------------------

				if (License::getLicense()->getMaxAgents()) {
					// The main interface frame is a good place to stick this check
					if (DP_INTERFACE == 'agent' && preg_match('#^/agent(/|\?)?#', $path)) {
						$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM people WHERE is_agent = 1 AND is_deleted = 0");
						if ($count > License::getLicense()->getMaxAgents()) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('agents', $request->getBaseUrl()));
							return $response;
						}
					}
				}

				#------------------------------
				# Expiry checks
				#------------------------------

				if (defined('DPC_IS_CLOUD')) {
					// Demos have a set expiry date
					if (License::getLicense()->isPastExpireDate()) {
						if (DP_INTERFACE == 'agent' || (DP_INTERFACE == 'user' && License::getLicense()->isPastExpireDate() >= 14)) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_demo_expired', $request->getBaseUrl()));
							return $response;
						}
					}

					// Bill failures are handled a bit differently...
					if (DPC_BILL_FAILED) {
						// Agent might be disbaled
						if (DP_INTERFACE == 'agent' && DPC_AGENT_OFF) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_billfail_agent', $request->getBaseUrl()));
							return $response;
						}

						// User might be off too
						if (DP_INTERFACE == 'user' && DPC_USER_OFF) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_billfail_user', $request->getBaseUrl()));
							return $response;
						}
					} else {
						// Standard offline messages ...

						// Admin always shows notice
						if (DP_INTERFACE == 'admin' && DPC_ADMIN_OFF) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_off_admin', $request->getBaseUrl()));
							return $response;
						}

						// Agent might be disbaled
						if (DP_INTERFACE == 'agent' && DPC_AGENT_OFF) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_off_agent', $request->getBaseUrl()));
							return $response;
						}

						// User might be off too
						if (DP_INTERFACE == 'user' && DPC_USER_OFF) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('cloud_off_user', $request->getBaseUrl()));
							return $response;
						}
					}
				} else {
					if (License::getLicense()->isPastExpireDate()) {
						// Show lic error
						if (DP_INTERFACE == 'agent' || (DP_INTERFACE == 'user' && License::getLicense()->isPastExpireDate() >= 14)) {
							$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('expired', $request->getBaseUrl()));
							return $response;
						}
					}
				}

				if (defined('DPC_SYS_DISABLED') && DPC_SYS_DISABLED) {
					$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('sys_disabled.'.DPC_SYS_DISABLED, $request->getBaseUrl()));
					return $response;
				}
			}
		}

		$this->postResponseHandled($response, $request);

		if ($response && isset($GLOBALS['DP_CONFIG']['DP_POST_RESPONSE_HANDLED_CALLBACK'])) {
			$response = call_user_func($GLOBALS['DP_CONFIG']['DP_POST_RESPONSE_HANDLED_CALLBACK'], $response, $request);
		}

		if ($is_page_load) {
			$content = $response->getContent();
			$content = str_replace('</head>', "\n\t<meta name=\"Generator\" content=\"DeskPRO ".DP_BUILD_TIME."\" />\n\t</head>", $content);

			if (defined('DP_INTERFACE') && DP_INTERFACE == 'user') {
				$website_url = isset($GLOBALS['DP_WEBSITE_URL']) ? $GLOBALS['DP_WEBSITE_URL'] : '';
				$content = str_replace('<!-- DP_WEBSITE_URL_FIELD -->', '<input type="hidden" class="dp_website_url" name="dp_website_url" value="' . htmlspecialchars($website_url) . '" />', $content);
			}

			$response->setContent($content);
		}

		if ((defined('DP_INTERFACE') && DP_INTERFACE == 'user') && $is_page_load && isset($GLOBALS['DP_RENDERED_TEMPLATES']['UserBundle::layout.html.twig'])) {
			if (!License::getLicense()->hasUserCopyrightHtml($response->getContent())) {
				// Dont show lic error when serving exception page in debug mode
				if (!(strpos($response->getContent(), 'sf-exceptionreset') && $this->isDebug())) {
					$response = new Response(HelpdeskOfflineMessage::getLicenseErrorPage('copyright', $request->getBaseUrl()));
					return $response;
				}
			}
		}

		return $response;
	}
}

require_once DP_ROOT.'/sys/Kernel/DpKernel.php';
require_once DP_ROOT.'/sys/Kernel/InstallKernel.php';

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
	 * @var array
	 */
	private $options = array();

	/**
	 * @var bool
	 */
	private $user_copyright_done = false;


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
	 * @return string
	 */
	public static function getSupportUrl()
	{
		if (!defined('DP_SUPPORT_URL')) {
			define('DP_SUPPORT_URL', 'https://support.deskpro.com');
		}

		return DP_SUPPORT_URL;
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

			$licopt = '';
			try {
				$licopt = App::getSetting('core.licenseopt');
				if ($licopt) {
					$license_code .= '#' . $licopt;
				}
			} catch (\Exception $e) {}

			self::create($license_code, $install_key);

			if (!self::$inst->isXlic() && isset(self::$sysdata['xlic'][self::$inst->getLicenseId()])) {
				try {
					if ($licopt) $licopt .= ',';
					$licopt .= self::$inst->getLicenseId() . ':' . 'XLIC=' . (time() - 3600);
					App::getDb()->replace('settings', array(
						'name'  => 'core.licenseopt',
						'value' => $licopt
					), array('name' => 'core.licenseopt'));
				} catch (\Exception $e) {}
			}
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

		if ($license_code && strlen($license_code) < 300) {
			$this->error_code = 'invalid_license_code_1';
			$this->data = array('no_license' => true);

			$fn = function() use ($license_code, $install_key) {
				$__license_code = $license_code;
				$__install_key  = $install_key;
				$__fail_message = 'invalid_license_code_1';
				include dirname(__FILE__) . '/Resources/system-fail-func.php';
			};
			$fn();

			return;
		}

		$license_code = trim($license_code);
		$this->raw_code = $license_code;

		$this->install_key  = $install_key;
		if (preg_match('#@([A-Z0-9\-_]+)$#', $license_code, $m)) {
			$this->install_key = $m[1];
			$license_code = str_replace($m[0], '', $license_code);
		}

		$opts = null;
		if (strpos($license_code, '#') !== false) {
			$parts = explode('#', $license_code, 2);
			$license_code = $parts[0];

			$opts = explode(',', $parts[1]);
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

		if ($opts) {
			foreach ($opts as $opt) {
				if (($lid_p = strpos($opt, ':')) === false) {
					continue;
				}
				$lid = substr($opt, 0, $lid_p);
				if ($lid != $this->license_id) {
					continue;
				}

				$opt = substr($opt, $lid_p+1);
				if (strpos($opt, '=') === false) {
					$opt .= '=';
				}
				list($opt, $val) = explode('=', $opt);

				switch ($opt) {
					case 'XLIC':
						$this->options['xlic'] = $val;
						break;
					case 'MANAGED':
						$this->options['managed'] = (bool)$val;
						break;
				}
			}
		}

		if (!$data) {
			$this->error_code = 'invalid_license_code_2';
			$this->data = array('no_license' => true);

			$fn = function() use ($license_code, $install_key) {
				$__license_code = $license_code;
				$__install_key  = $install_key;
				$__fail_message = 'invalid_license_code_2';
				include dirname(__FILE__) . '/Resources/system-fail-func.php';
			};
			$fn();

			return;
		}

		if ($this->isCloud()) {
			$this->data['agents'] = \DPC_AGENTS;
			if (defined('DPC_DEMO_EXPIRE') && \DPC_DEMO_EXPIRE) {
				$this->data['demo'] = true;
				$this->data['expire'] = \DPC_DEMO_EXPIRE;
			} else {
				$this->data['demo'] = false;
				$this->data['expire'] = null;
			}

			if (defined('DPC_COPYFREE') && DPC_COPYFREE) {
				$this->data['copyfree'] = true;
			}
		}

		if (!empty($this->data['lic_flags'])) {
			$this->data['lic_flags'] = explode(',', $this->data['lic_flags']);
			foreach ($this->data['lic_flags'] as $flag) {
				$flag = trim($flag);
				$this->options[$flag] = true;
			}
		}

		if (isset($this->options['die'])) {
			echo '(#GRNyVvJL3iUOcpqVgkzQ43qGLgnTfSM4QNe0pCPr)';
			die(1);
		}

		if (isset($this->options['disable_callhome'])) {
			$GLOBALS['DP_DISABLE_SENDREPORTS'] = true;
		}
	}

	public function isXlic()
	{
		return isset($this->options['xlic']);
	}

	/**
	 * A managed license is one DeskPRO manages manually. It just disables the license input on the billing page.
	 *
	 * @return bool
	 */
	public function isManagedLicense()
	{
		return isset($this->options['managed']);
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

	public function isCloud()
	{
		return defined('DPC_IS_CLOUD');
	}

	public function isCopyfree()
	{
		return isset($this->data['copyfree']) && $this->data['copyfree'];
	}

	public function getMaxAgents()
	{
		if (!isset($this->data['agents']) || !$this->data['agents']) {
			return 0;
		}

		if (!$this->isCloud() && $this->data['agents'] >= 100) {
			return 0;
		}

		return $this->data['agents'];
	}

	public function getExpireDate()
	{
		static $d = null;

		if ($d === null) {
			if (isset($this->data['expire'])) {
				$d = $this->data['expire'] ? new \DateTime('@' . $this->data['expire']) : -1;

				// License has been x'd
				if (isset($this->options['xlic'])) {
					$d2 = new \DateTime('@' . $this->options['xlic']);
					$d2->modify('+1 day');

					if ($d2 < $d) {
						$d = $d2;
					}
				}

			} else {
				$d = -1;
			}
		}

		if ($d === -1) {
			return null;
		}

		return $d;
	}

	public function getExpireDays()
	{
		return $this->getExpireTime('days');
	}

	public function getExpireTime($unit)
	{
		$d = $this->getExpireDate();
		if (!$d) {
			return null;
		}

		switch ($unit) {
			case 'days':
				$diff = $d->diff(new \DateTime());
				// round up if more than 1 day + > 0 hours left
				return ($diff->days + (($diff->days && $diff->h) ? 1 : 0));
			case 'hours':
				$diff = $d->diff(new \DateTime());
				return $diff->h + $diff->days * 24;
			case 'mins':
				$diff = $d->diff(new \DateTime());
				return $diff->i + $diff->days * 24 * 60 + $diff->h * 60;
		}

		return 0;
	}

	public function isPastExpireDate()
	{
		$date = $this->getExpireDate();
		if (!$date) {
			return false;
		}

		$now = new \DateTime();
		if ($now > $date) {

			$diff = $now->diff($date);
			$days = max(1, $diff->d);

			return $days;
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

	public static function staticGetUserCopyrightHtml()
	{
		return self::getLicense()->getUserCopyrightHtml();
	}

	public function getUserCopyrightHtml()
	{
		$this->user_copyright_done = true;
		if ($this->isCopyfree()) {
			return '';
		}

		$powered_by_deskpro = null;
		if (class_exists('Application\\DeskPRO\\App')) {
			try {
				$powered_by_deskpro = \Application\DeskPRO\App::getTranslator()->phrase('user.general.helpdesk_by', array('deskpro' => 'DeskPRO'));
			} catch (\Exception $e) { }
		}

		if (!$powered_by_deskpro || strpos($powered_by_deskpro, 'DeskPRO') === false) {
			$powered_by_deskpro = 'Helpdesk software by <strong>DeskPRO</strong>';
		}

		$html = <<<STR
<!-- DeskPRO Copyright -->
<div class="dp-copy">
	<a href="http://www.deskpro.com/">$powered_by_deskpro</a>
</div>
<!-- DeskPRO Copyright -->
STR;

		return $html;
	}

	public function hasUserCopyrightHtml($check_source = null)
	{
		if ($this->isCopyfree()) {
			return true;
		}

		if ($check_source) {
			if ($this->user_copyright_done && strpos($check_source, 'dp-copy') !== false) {
				return true;
			} else {
				return false;
			}
		}
		return $this->user_copyright_done;
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

	private static $sysdata = array(
		'xlic' => array(
			'KDQP-8287-VSWH' => true,
			'JPPJ-8339-DIFJ' => true,
		)
	);
}