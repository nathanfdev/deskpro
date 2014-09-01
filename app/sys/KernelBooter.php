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

require_once DP_ROOT.'/sys/DpShutdown.php';
require_once DP_ROOT.'/sys/Kernel/HelpdeskOfflineMessage.php';

use Application\DeskPRO\App;
use Application\DeskPRO\Console\CronApplication;
use Doctrine\DBAL\DBALException;

class KernelBooter
{
	protected static $_cache_file = null;

	/**
	 * Builds the main $DP_CONFIG array from config.php
	 *
	 * @return mixed
	 */
	public static function bootstrapConfig()
	{
		static $has_loaded = false;
		if ($has_loaded) {
			return;
		}

		$has_loaded = true;

		#------------------------------
		# Load main config now
		#------------------------------

		global $DP_CONFIG;
		dp_load_config();

		if (isset($DP_CONFIG['debug']['enable_debug_trace']) && $DP_CONFIG['debug']['enable_debug_trace']) {
			if (!function_exists('xdebug_start_trace')) {
				exit('To use the `debug.enable_debug_trace` setting, the xdebug extension must be installed');
			}

			$debug_dir = dp_get_debug_dir();

			if (!is_dir($debug_dir) || !is_writable($debug_dir)) {
				exit('The debug output directory at ' . $debug_dir . ' does not exist or is not writable.');
			}

			$file = $debug_dir . DIRECTORY_SEPARATOR . date('YmdHis') . '-' . mt_rand(10000,99999);

			ini_set('xdebug.collect_params', 3);
			if (isset($DP_CONFIG['debug']['enable_debug_trace_format'])) {
				ini_set('xdebug.trace_format', $DP_CONFIG['debug']['enable_debug_trace_format']);
			}

			xdebug_start_trace($file);
			define('DP_DEBUG_TRACE_FILE', $file . '.xt');
		}
	}


	/**
	 * Includes the libraries and autoloading required for the system to boot
	 *
	 * @param $debug
	 * @return mixed
	 */
	public static function bootstrapLib($debug)
	{
		static $has_loaded = false;
		if ($has_loaded) {
			return;
		}

		$has_loaded = true;

		if ( ($debug || defined('DP_BUILDING') || !file_exists(DP_ROOT . '/sys/bootstrap.php')) && !defined('DP_USE_COMPILED_BOOTSTRAP')) {
			require(DP_ROOT . '/sys/bootstrap-dev.php');
		} else {
			require(DP_ROOT . '/sys/bootstrap.php');
		}

		if (isset($GLOBALS['DP_AUTOLOADER']) && !dp_get_config('no_use_classmap_file') && file_exists(DP_ROOT.'/sys/cache/classmap.php')) {
			$map = require DP_ROOT.'/sys/cache/classmap.php';
			if ($map) {
				$GLOBALS['DP_AUTOLOADER']->registerClassNames($map);
			}
		}

		require(DP_ROOT . '/sys/Kernel/compat.php');
		require(DP_ROOT . '/sys/system.php');

		if (array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER) && isset($GLOBALS['DP_USING_TESTING_CONFIG']) && $GLOBALS['DP_USING_TESTING_CONFIG']) {
			define('C3_CODECOVERAGE_MEDIATE_STORAGE', DP_ROOT.'/testing/logs/c3tmp');
			define('C3_CODECEPTION_CONFIG_PATH', DP_ROOT.'/testing/codeception.yml');
			define('C3_CODECOVERAGE_PROJECT_ROOT', DP_ROOT);
			require(DP_ROOT.'/testing/src/c3.php');
		}
	}


	/**
	 * Gets the environment ready for execution
	 */
	public static function bootstrapEnv()
	{
		#------------------------------
		# Normalize env
		#------------------------------

		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');
		libxml_disable_entity_loader(true);

		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor-src/php-utf8');

		#------------------------------
		# Undo magic quotes
		#------------------------------

		// Check exists since its gone in PHP 5.4
		if (function_exists('get_magic_quotes_gpc')) {
			ini_set('magic_quotes_runtime', 0);

			if (get_magic_quotes_gpc()) {
				$clean_fn = function(&$v) {
					$v = stripslashes($v);
				};

				array_walk_recursive($_GET,     $clean_fn);
				array_walk_recursive($_POST,    $clean_fn);
				array_walk_recursive($_COOKIE,  $clean_fn);
				array_walk_recursive($_REQUEST, $clean_fn);
			}
		}
	}


	/**
	 * Boots a web kernel
	 *
	 * @param null $request
	 */
	public static function bootWeb($request = null)
	{
		global $DP_CONFIG;

		self::bootstrapConfig();

		$env = 'prod';
		$debug = false;

		if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
			$env = 'dev';
			$debug = true;
		}

		self::ensureEnvFiles($env);
		// defer lib loading until we know we're not serving a cache

		if ($request) {
			$path = $request->getPathInfo();
			$base_path = $request->getBasePath();
			$request_uri = $request->getRequestUri();
			$request_method = $request->getMethod();
		} else {
			$path = self::getPathInfo();
			$base_path = self::getBasePath();
			$request_uri = self::getRequestUri();
			$request_method = self::getMethod();
		}

		$kernel = false;

		if (preg_match('#^/dp\-ping(/|\?|$)#', $path)) {
			header("Content-type: application/json");
			echo '{"deskpro": true, "interface": "user"}';
			exit;
		} elseif (preg_match('#^/(agent|admin|api|reports|billing)/dp-ping(/|\?|$)#', $path, $m)) {
			header("Content-type: application/json");
			echo '{"deskpro": true, "interface": "'.$m[1].'"}';
			exit;
		}

		$kernel_class = 'DeskPRO\\Kernel\\DpKernel';
		if (preg_match('#^/agent(/|\?|$)#', $path)) {
			define('DP_INTERFACE', 'agent');
		} elseif (preg_match('#^/adm(in)?(/|\?|$)#', $path)) {
			define('DP_INTERFACE', 'admin');
		} elseif (preg_match('#^/billing(/|\?|$)#', $path)) {
			define('DP_INTERFACE', 'billing');
		} elseif (preg_match('#^/reports(/|\?|$)#', $path)) {
			define('DP_INTERFACE', 'reports');
		} elseif (preg_match('#^/api(/|\?|$)#', $path)) {
			define('DP_INTERFACE', 'api');
		} elseif (preg_match('#^/install(/|\?|$)#', $path)) {

			if (dp_get_config('is_installed_flag')) {
				echo deskpro_install_basic_error("The database details in <var>config.php</var> are invalid or the database is not a valid DeskPRO database.<br/><br/>If this is a mistake and you intend to create a new installation into a new database, you must first delete the file <var>data/is_installed.dat</var> to make the installer function again.", 'Error');
				exit;
			}

			$kernel_class = 'DeskPRO\\Kernel\\InstallKernel';
			define('DP_INTERFACE', 'install');

			// Always force full URL with trailing slash
			if (strpos($request_uri, '/index.php/install/') === false) {
				header('Location: ' . $base_path . '/index.php/install/');
				exit;
			}

		} elseif (preg_match('#^/tech(/|\?|$)#i', $path)) {
			header('Location: ' . $base_path . '/agent');
			exit;
		} elseif (preg_match('#^/admincp(/|\?|$)#i', $path)) {
			header('Location: ' . $base_path . '/admin');
			exit;
		} elseif (preg_match('#^/file.php/?(.*?)$#i', $path, $m)) {
			$url = $base_path . '/file.php/' . $m[1] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
			header('Location: ' . $url);
			exit;
		} else {
			define('DP_INTERFACE', 'user');

			try {
				$res = self::_getCachedPageIfAvailable($request, $request_uri, $path, $request_method, function() use ($kernel_class, $env, $debug, &$kernel) {
					if (!$kernel) {
						KernelBooter::bootstrapLib($debug);
						KernelBooter::bootstrapEnv();

						$kernel = new $kernel_class($env, $debug);
					}
					return $kernel;
				});
			} catch (\Exception $e) {
				$res = false;
			}

			if ($res) {
				header('HTTP/1.1 200 OK');
				foreach ($res['headers'] AS $key => $headers) {
					foreach ($headers AS $val) {
						header("$key: $val");
					}
				}

				if (is_file(dp_get_data_dir() . '/helpdesk-offline.trigger')) {
					$content = KernelBooter::prepareCachedOutputForOffline($res['content']);
				} else {
					$content = KernelBooter::prepareCachedOutput($res);
				}
				echo $content;

				global $DP_CONFIG;
				if (!empty($DP_CONFIG['cache']['page_cache']['enable_hit_log'])) {
					if (empty($DP_CONFIG['cache']['page_cache']['hit_log_file'])) {
						$hit_log = dp_get_log_dir() . '/user-page-cache-hit.log';
					} else {
						$hit_log = $DP_CONFIG['cache']['page_cache']['hit_log_file'];
					}

					$fp = @fopen($hit_log, 'a');

					$scheme_host = ($request ? $request->getScheme().'://'.$request->getHttpHost() : self::getScheme().'://'.self::getHttpHost());

					$time = sprintf('%.4f', microtime(true) - DP_START_TIME);
					fwrite($fp, "[" . gmdate('Y-m-d H:i:s') . "] $scheme_host$request_uri (time: $time)\n");
					fclose($fp);
				}

				exit;
			}
		}

		// No access to install or dev from cloud
		if (defined('DPC_IS_CLOUD') && (DP_INTERFACE == 'dev' || DP_INTERFACE == 'install')) {
			exit;
		}

		#------------------------------
		# Handle request
		#------------------------------

		if (!$kernel) {
			self::bootstrapLib($debug);
			self::bootstrapEnv();
		}

		if (!$request) {
			$request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();
		}

		dp_pagelog_reset();
		dp_pagelog_set('user_agent', !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
		dp_pagelog_set('user_ip', dp_get_user_ip_address());
		dp_pagelog_set('request_id', defined('DP_REQUEST_ID') ? DP_REQUEST_ID : null);
		dp_pagelog_set('page_url', $request->getRequestUri());

		$trust_option = isset($GLOBALS['DP_CONFIG']['trust_proxy_data']) && $GLOBALS['DP_CONFIG']['trust_proxy_data'] ? $GLOBALS['DP_CONFIG']['trust_proxy_data'] : null;
		$trust_list = array();
		if ($trust_option) {
			if (!is_array($trust_option)) {
				$trust_option = array($trust_option);
			}
			foreach ($trust_option as $opt) {
				if (is_string($opt) && $opt[0] == '@') {
					$file = substr($opt, 1);
					// A relative file starts with ~
					if ($file[0] == '~') {
						$file = DP_ROOT . substr($file, 1);
					}

					if (file_exists($file)) {
						$inc_opts = @include($file);
						if ($inc_opts) {
							$trust_list = array_merge($trust_list, $inc_opts);
						}
					}
				} else {
					$trust_list[] = $opt;
				}
			}
		}

		if ($trust_list) {
			\Application\DeskPRO\HttpFoundation\Request::setTrustedProxies($trust_list);
			\Symfony\Component\HttpFoundation\Request::setTrustedProxies($trust_list);
		}

		$GLOBALS['DP_MAIN_REQUEST'] = $request;

		define('DP_REQUEST_URL', $request->getUri());

		try {
			if (!$kernel) {
				if ($kernel == 'DeskPRO\\Kernel\\InstallKernel') {
					$kernel = new $kernel_class($env, $debug);
				} else {
					$kernel = new $kernel_class($env, $debug, DP_INTERFACE);
				}
			}
			$response = $kernel->handle($request);
			if (DP_INTERFACE == 'user') {
				try {
					self::_updateCachedFile($response);
				} catch (\Exception $e) {}
			}

			if (defined('DP_REQUEST_ID')) {
				$response->headers->set('X-DeskPRO-RequestID', DP_REQUEST_ID);
			}

			$response->send();

			dp_pagelog_set('response_type', $response->headers->get('Content-Type'));
			dp_pagelog_set('response_code', $response->getStatusCode());
			dp_pagelog_set('response_size', strlen($response->getContent()));
		} catch (\Exception $e) {
			if ($e instanceof DBALException || $e instanceof \PDOException) {
				if ($e->getCode() == '2002' || $e->getCode() == '1049' || $e->getCode() == '1044' || $e->getCode() == '1045') {
					// This will show an error page if already installed, so the redirect to install wont happen
					deskpro_handle_boot_db_exception($e);

					header('Location: ' . $request->getBasePath() . '/index.php/install/');
					exit;
				}
				throw $e;
			} else {
				throw $e;
			}
		}
	}

	protected static function _getCachedPageIfAvailable($request, $request_uri, $path, $request_method, \Closure $get_kernel)
	{
		global $DP_CONFIG;
		if (!isset($DP_CONFIG['cache']['page_cache']['enable'])) {
			// on by default
			$DP_CONFIG['cache']['page_cache']['enable'] = true;
		}

		if (!$DP_CONFIG['cache']['page_cache']['enable']) {
			// turned off, don't check anything else
			return null;
		}

		$use_cache = false;
		$language_id = null;
		$cache_time = 0;

		if ($request_method == 'GET' && !isset($_GET['admin_portal_controls']) && !preg_match('#/widget/chat.html#', $path)) {
			if (!empty($_COOKIE['dp-guest-cache']) || (empty($_COOKIE['dpsid']) && empty($_COOKIE['dpreme']))) {
				if (!isset($_COOKIE['dpsid-agent']) && !isset($_COOKIE['dpsid-admin'])) {
					$use_cache = true;

					if (!empty($_COOKIE['dp-guest-cache'])) {
						$parts = explode('-', $_COOKIE['dp-guest-cache']);
						if (!empty($parts[1])) {
							$language_id = intval($parts[1]);
						}

						$cache_time = intval($parts[0]);
						if ($cache_time && $cache_time > time()) {
							$use_cache = false;
						}
					}
				}
			}
		}

		if (!$use_cache) {
			return null;
		}

		if (!$language_id && !empty($_COOKIE['dplid'])) {
			$language_id = intval($_COOKIE['dplid']);
		}

		if (!$language_id) {
			$languages = null;
			$lang_cache_file = dp_get_data_dir() . '/languages.cache';
			if (file_exists($lang_cache_file)) {
				$languages = @unserialize(file_get_contents($lang_cache_file));
			}

			if (!$languages) {
				try {
					$kernel = $get_kernel();
					$kernel->boot();
					$default = App::getSetting('core.default_language_id');
					$languages = App::getDb()->fetchAllKeyed("
						SELECT *
						FROM languages
					", array(), 'id');
					if (isset($languages[$default])) {
						$lang = $languages[$default];
						unset($languages[$default]);
						$languages = array($default => $lang) + $languages;
					}
				} catch (\Exception $e) {
					// errored - don't pull from the cache, probably not installed
					return null;
				}

				$cache_slam_file = $lang_cache_file . '.slam';
				if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
					$slam_fp = @fopen($cache_slam_file, 'w');
					if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
						@file_put_contents($lang_cache_file, serialize($languages), \LOCK_EX);
						@flock($slam_fp, \LOCK_UN);
						@fclose($slam_fp);
						@unlink($cache_slam_file);
					} else {
						@fclose($slam_fp);
					}
				}
			}

			$locales = array('');
			foreach ($languages AS $language) {
				$locales[] = $language['locale'];
			}

			$accept_languages = $request ? $request->getLanguages() : self::getLanguages();

			$locale = self::getPreferredLanguage($accept_languages, $locales);

			if ($locale) {
				// we have an exact locale match
				foreach ($languages AS $language) {
					if ($language['locale'] === $locale) {
						$language_id = $language['id'];
						break;
					}
				}
			} else {
				// look for a language match (as there isn't an exact locale match)
				foreach ($accept_languages AS $accept_language) {
					$accept_language = substr($accept_language, 0, 2);
					foreach ($languages AS $language) {
						if (!empty($language['locale']) && substr($language['locale'], 0, 2) == $accept_language) {
							$language_id = $language['id'];
							break 2;
						}
					}
				}
			}

			if (!$language_id) {
				$lang = reset($languages);
				$language_id = $lang ? $lang['id'] : 0;
			}
		}

		$ttl = isset($DP_CONFIG['cache']['page_cache']['ttl']) ? $DP_CONFIG['cache']['page_cache']['ttl'] : 900;

		$cache_dir = dp_get_tmp_dir() . '/page-cache';
		$base = substr(preg_replace('#[^a-z0-9_-]#i', '_', $request_uri), 0, 35);
		$scheme_host = ($request ? $request->getScheme().'://'.$request->getHttpHost() : self::getScheme().'://'.self::getHttpHost());
		$cache_base_filename = $base . '-' . md5($scheme_host . $request_uri) . '.cache';
		$cache_filename = $language_id . '-' . $cache_base_filename;
		$cache_file = $cache_dir . '/' . $cache_filename;

		if (file_exists($cache_file)) {
			$use_cache = false;
			if (time() - filemtime($cache_file) <= $ttl) {
				$use_cache = true;
			} else {
				$cache_slam_file = $cache_file . '.slam';
				if (file_exists($cache_slam_file) && time() - filemtime($cache_slam_file) <= 30) {
					// someone else is going to write it, use the stale data for a bit
					$use_cache = true;
				}
			}

			if ($use_cache) {
				$output = @unserialize(file_get_contents($cache_file));
				if (is_array($output)) {
					if ($output['compressed']) {
						$output['content'] = gzuncompress($output['content']);
					}

					return $output;
				}
			}
		}

		self::$_cache_file = $cache_base_filename;

		return null;
	}

	protected static function _updateCachedFile(\Symfony\Component\HttpFoundation\Response $response)
	{
		$person = App::getCurrentPerson();
		$logged_in = ($person && $person->getId());
		$skip_cache = true;

		if ($logged_in) {
			if (!empty($_COOKIE['dp-guest-cache'])) {
				\Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dp-guest-cache')->send();
			}
		} else {
			if (isset($GLOBALS['DP_SET_SKIP_CACHE']) && $GLOBALS['DP_SET_SKIP_CACHE']) {
				global $DP_CONFIG;
				$ttl = isset($DP_CONFIG['cache']['page_cache']['ttl']) ? $DP_CONFIG['cache']['page_cache']['ttl'] : 900;
				$cache_time = time() + $ttl;
			} else {
				$cache_time = !empty($_COOKIE['dp-guest-cache']) ? intval($_COOKIE['dp-guest-cache']) : 0;
				if ($cache_time < time()) {
					$cache_time = 0;
				}
			}

			$skip_cache = ($cache_time > 0);

			$value = $cache_time . '-' . App::getLanguage()->getId();
			if (empty($_COOKIE['dp-guest-cache']) || $value !== $_COOKIE['dp-guest-cache']) {
				\Application\DeskPRO\HttpFoundation\Cookie::makeCookie('dp-guest-cache', $value, 0)->send();
			}
		}

		if (!$logged_in && !$skip_cache && !$response->headers->has('X-DeskPRO-Private') && self::$_cache_file && strpos($response->headers->get('Content-Type'), 'text/html') === 0 && $response->getStatusCode() == 200) {
			$cache_dir = dp_get_tmp_dir() . '/page-cache';
			if (!is_dir($cache_dir)) {
				@mkdir($cache_dir, 0777);
			}

			$cache_filename = $cache_dir . '/' . App::getLanguage()->getId() . '-' . self::$_cache_file;

			$cache_slam_file = $cache_filename . '.slam';
			if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
				$slam_fp = @fopen($cache_slam_file, 'w');
				if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
					// don't take any of the cookies - they'll be things like sessions etc
					$store = array(
						'app_secret'   => App::$container ? App::$container->getSetting('core.app_secret') : null,
						'headers'      => $response->headers->all(),
						'content'      => $response->getContent(),
						'compressed'   => false
					);
					if (function_exists('gzcompress')) {
						$store['content'] = gzcompress($store['content']);
						$store['compressed'] = true;
					}

					@file_put_contents($cache_filename, serialize($store), \LOCK_EX);
					@flock($slam_fp, \LOCK_UN);
					@fclose($slam_fp);
					@unlink($cache_slam_file);
				} else {
					@fclose($slam_fp);
				}
			}
		}
	}

	public static function prepareCachedOutputForOffline($content, $message = null)
	{
		if ($message === null) {
			$message = 'Our helpdesk is temporarily offline for maintenance.';
		}

		$content = str_replace('<!--DP_OFFLINE_CACHE_PAGE_NOTE-->', "<div id=\"dp-offline-cache-note\">" . ($message ? "$message<br /><br />" : '') . "This is a cached page. Live pages will automatically return when the helpdesk comes back online.</div>", $content);
		$content = preg_replace(
			'/<!--DP_OFFLINE_CACHE_REMOVE_START-->.*<!--DP_OFFLINE_CACHE_REMOVE_END-->/siU',
			'',
			$content
		);

		return $content;
	}


	public static function prepareCachedOutput(array $res)
	{
		$content = $res['content'];

		if (!empty($res['app_secret'])) {
			if (!class_exists('Orb\Util\Util', false)) {
				require_once DP_ROOT.'/src/Orb/Util/Util.php';
			}
			if (!class_exists('Orb\Util\Strings', false)) {
				require_once DP_ROOT.'/src/Orb/Util/Strings.php';
			}
			$app_secret = $res['app_secret'];
			$content = preg_replace_callback('#<!\-\-DP_FORM_TOKEN\((.*?), (.*?)\)\-\->.*?<!\-\-DP_FORM_TOKEN_END\-\->#s', function($m) use ($app_secret) {
				$name = $m[1];
				$field_name = $m[2];

				$html = '<input type="hidden" name="' . $field_name . '" value="STATIC_' . \Orb\Util\Util::generateStaticSecurityToken(md5($app_secret . $name), 43200) . '" />';
				$html .= '<input type="hidden" name="_rt" value="STATIC_' . \Orb\Util\Util::generateStaticSecurityToken(md5($app_secret . 'request_token'), 43200) . '" class="dp_request_token" />';

				return $html;
			}, $content);
		}

		return $content;
	}


	/**
	 * Boots the CLI
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootCli($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp('cmd', $env, $debug);

		if (!$app) {
			return;
		}

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$app->setAutoExit(false);
		$return = $app->run();
		unset($GLOBALS['DP_IS_IN_CLI']);

		return $return;
	}


	/**
	 * Boots the CLI and runs the cron command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootCron($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp('cron', $env, $debug, true);

		if (!$app) {
			return;
		}

		if (isset($GLOBALS['DP_USING_TESTING_CONFIG'])) {
			echo "(Tests are running)\n";
			return;
		}

		$lock_file = dp_get_tmp_dir() . '/cron.lock';
		$lock_fp = null;

		// Use a file lock for better "cron is still running" detection
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			if (!in_array('-f', $_SERVER['argv']) && !in_array('--force', $_SERVER['argv'])) {
				$skip_lock_err = false;
				if (file_exists($lock_file)) {
					$t = (int)file_get_contents($lock_file);
					if ($t < time()-900) {
						$skip_lock_err = true;
						@unlink($lock_file);
					}
				}

				$lock_fp = @fopen($lock_file, 'c');
				@chmod($lock_file, 0777);
				if ($lock_fp) {
					if (!@flock($lock_fp, \LOCK_EX | \LOCK_NB)) {
						if (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv'])) {
							echo "Lock file still locked, cron already running: $lock_file\n";
						}
						if (!$skip_lock_err) {
							exit;
						}
					}

					@ftruncate($lock_fp, 0);
					@fwrite($lock_fp, time());
				}
			}
		}

		$check_twitter = false;
		$check_indexer = false;

		$do_upgrade = false;
		try {
			if (\Application\DeskPRO\App::getSetting('core.upgrade_time') && \Application\DeskPRO\App::getSetting('core.upgrade_time') <= time()) {
				$do_upgrade = true;
			}
		} catch (\Exception $e) {throw $e;}

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php

		if ($do_upgrade) {
			$argv = array();
			array_unshift($argv, 'cron.php', 'dp:internal-upgrade-runner');
		} else {
			$do_collation_change = false;
			try {
				if (\Application\DeskPRO\App::getSetting('core.db_collation_change')) {
					$do_collation_change = \Application\DeskPRO\App::getSetting('core.db_collation_change');
				}
			} catch (\Exception $e) {throw $e;}

			if ($do_collation_change) {
				\Application\DeskPRO\App::getDb()->executeQuery("
					DELETE FROM settings WHERE name = 'core.db_collation_change'
				");
				array_unshift($argv, 'cron.php', 'dp:db-collation-change', "--collation=$do_collation_change");
			} else {
				array_unshift($argv, 'cron.php', 'dp:worker-job'); // so we can add the command name in the right spot
			}

			$check_twitter = true;
			$check_indexer = true;
		}

		if ($check_twitter && !defined('DPC_IS_CLOUD') && \Application\DeskPRO\App::getConfig('enable_twitter')) {
			$twitter_ping = \Application\DeskPRO\App::getSetting('core.twitter_ping');
			if (!$twitter_ping || $twitter_ping < time() - 60) {
				if (\Application\DeskPRO\App::getDb()->fetchColumn("SELECT COUNT(*) FROM twitter_accounts")) {
					if (file_exists(dp_get_data_dir() . '/twitter.pid')) {
						$twitter_pid = intval(file_get_contents(dp_get_data_dir() . '/twitter.pid'));
					} else {
						$twitter_pid = null;
					}

					if ($twitter_pid !== 0) {
						// need to restart the twitter runner in the background

						$file = escapeshellarg(DP_ROOT . '/bin/twitter.php');
						$php_path = dp_get_php_path(false);

						if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
							// this is needed as we need a fake window to hide the process
							$php_path = str_replace('php-win.exe', 'php.exe', $php_path);
							$file = str_replace('/', '\\', $file);

							if (class_exists('\COM', false)) {
								$shell = new \COM("WScript.Shell");
								$shell->Run("$php_path $file", 0, false);
							} else {
								pclose(popen("start \"dptwitter\" /MIN $php_path $file", "r"));
							}
						} else {
							exec("nohup $php_path $file > /dev/null 2> /dev/null &");
						}
					}
				}
			}
		}

		if ($check_indexer && !defined('DPC_IS_CLOUD')) {
			$index_reset = \Application\DeskPRO\App::getSetting('elastica.requires_reset');
			if ($index_reset) {
				try {
					$id = mt_rand(10000,99999);
					\Application\DeskPRO\App::getDb()->insertIgnore('settings', array('name'  => 'elastica.requires_reset_started', 'value' => $id));

					$file = escapeshellarg(realpath(DP_ROOT . '/../cmd.php'));
					$args = 'dp:elastica:populate --auto-reset ' . $id;
					$php_path = dp_get_php_path(false);

					if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
						// this is needed as we need a fake window to hide the process
						$php_path = str_replace('php-win.exe', 'php.exe', $php_path);
						$file = str_replace('/', '\\', $file);

						if (class_exists('\COM', false)) {
							$shell = new \COM("WScript.Shell");
							$shell->Run("$php_path $file", 0, false);
						} else {
							pclose(popen("start \"dpindexer\" /MIN $php_path $file $args", "r"));
						}
					} else {
						exec("nohup $php_path $file $args > /dev/null 2> /dev/null &");
					}
				} catch (\Exception $e) {}
			}
		}

		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$app->setAutoExit(false);
		$return = $app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;

		if ($lock_fp) {
			@flock($lock_fp, \LOCK_UN);
			@fclose($lock_fp);
			@unlink($lock_file);
		}

		return $return;
	}


	/**
	 * Boot tests
	 */
	public static function bootTests()
	{
		define('DP_INTERFACE', 'cli');
		$GLOBALS['DP_IS_IN_CLI'] = true;
		KernelBooter::bootstrapConfig();
		KernelBooter::bootstrapLib(true);
		KernelBooter::bootstrapEnv();
	}


	/**
	 * Boots the CLI runs the import CLI command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootImport($env = 'prod', $debug = false)
	{
		static::ensureCli();

		echo "The version of DeskPRO you have downloaded does not handle importing.";

		echo "\n\nRefer to the DeskPRO knowledgebase for a link to the correct version:\n";
		echo "http://support.deskpro.com/kb/articles/116-upgrading-to-deskpro-v4";

		echo "\n\nYou should download the DeskPRO version mentioned in the above article\n";
		echo "and then try running this command again.\n";
		exit(1);

		$app = static::getCliApp('import', $env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php
		array_unshift($argv, 'import.php', 'dp:import', '--run'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$GLOBALS['DP_IS_INSTALL'] = true;
		$app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;
		$GLOBALS['DP_IS_INSTALL'] = false;
	}


	/**
	 * Boots the CLI runs the upgrade CLI command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootUpgrade($env = 'prod', $debug = false)
	{
		static::ensureCli();

		$app = static::getCliApp('upgrade', $env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove upgrade.php
		array_unshift($argv, 'upgrade.php', 'dp:upgrade'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$GLOBALS['DP_IS_INSTALL'] = true;
		$app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;
		$GLOBALS['DP_IS_INSTALL'] = false;
	}


	/**
	 * Creates a CLI kernel, and create an console app
	 *
	 * @param string $env
	 * @param bool $debug
	 * @return \Symfony\Bundle\FrameworkBundle\Console\Application
	 */
	public static function getCliApp($mode, $env = 'prod', $debug = false, $enforce_offline_mode = false)
	{
		global $DP_CONFIG;

		self::bootstrapConfig();

		if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
			$env = 'dev';
			$debug = true;
		}

		self::ensureEnvFiles($env);
		self::bootstrapLib($debug);
		self::bootstrapEnv();

		if (defined('DP_BUILDING')) {
			$debug = false;
		}

		define('DP_INTERFACE', 'cli');
		$kernel = new \DeskPRO\Kernel\DpKernel($env, $debug, DP_INTERFACE);
		$kernel->boot($mode);

		try {
			if ($mode == 'cron' && $kernel->isUpgradePending()) {
				if (in_array('--verbose', $_SERVER['argv'])) {
					echo "Upgrade pending\n";
				}
				return null;
			}
			if ($enforce_offline_mode || ($mode == 'cron' && !\Application\DeskPRO\App::getSetting('core.setup_initial'))) {
				if ($kernel->isHelpdeskOffline()) {
					if (in_array('--verbose', $_SERVER['argv'])) {
						echo "Helpdesk offline\n";
					}
					return null;
				}
			}
		} catch (\Doctrine\DBAL\DBALException $e) {
			global $DP_CONFIG;
			if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
				echo "DeskPRO is not yet installed. If you believe this a mistake, check your config.php\n";
				echo "file and ensure the database connection details are correct.\n";
				echo "\n";
				echo "The connection attempt resulted in the following error:\n[{$e->getCode()}] {$e->getMessage()}";
				echo "\n";
				exit;
			}

			// Otherwise unknown error we'll throw up
			throw $e;
		}

		if ($mode == 'cron') {
			$app = new CronApplication($kernel);
		} else {
			$app = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
		}
		$app->setCatchExceptions(false);
		return $app;
	}


	/**
	 * Ensures the current invocation is via the command-line
	 */
	public static function ensureCli()
	{
		if (php_sapi_name() != 'cli') {
			echo "This script must only be run from the CLI.\n";
			echo "Contact support@deskpro.com if you require assistance.\n";
			exit(1);
		}
	}


	/**
	 * If in prod mode, ensures that the build files etc exist.
	 * If not in prod mode, ensures that the cached ir exists and is writable.
	 */
	public static function ensureEnvFiles($env)
	{
		global $DP_CONFIG;
		$cache_dir = DP_ROOT.'/sys/cache';
		$web_dir = realpath(DP_ROOT . '/../web');

		$offline = false;
		if (is_file(dp_get_data_dir() . '/helpdesk-offline.trigger')) {
			$offline = true;
		}

		#------------------------------
		# Prod mode: make sure built
		#------------------------------

		if ($env == 'prod' && (!is_dir($cache_dir . '/prod'))) {
			if (php_sapi_name() == 'cli') {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflineMessage();
					exit(1);
				}
				echo <<<'TXT'
DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:

	1) Enable dev mode by editing /config.php and adding these lines:

		$DP_CONFIG['debug'] = array();
		$DP_CONFIG['debug']['dev'] = true;
		$DP_CONFIG['debug']['raw_assets'] = array('all');

	2) Or alternatively you can build DeskPRO by running app/bin/build/build.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<'HTML'
<p>DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:<br /><br /></p>

<p>1) Enable dev mode by editing <code>/config.php</code> and adding these lines:

<pre>
$DP_CONFIG['debug'] = array();
$DP_CONFIG['debug']['dev'] = true;
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build DeskPRO by running <code>app/bin/build/build.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO');
			}
			exit;

		#------------------------------
		# Dev mode, make sure cache dir writable
		#------------------------------

		} elseif ($env == 'dev' && (!is_dir($cache_dir) || !is_writable($cache_dir))) {
			if ($offline) {
				echo HelpdeskOfflineMessage::getOfflineMessage();
				exit(1);
			}
			if (php_sapi_name() == 'cli') {
				echo <<<TXT
DeskPRO is currently in dev mode which requires the cache directory at $cache_dir to be writable. Please
ensure this directory is writable and try again.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<HTML
<p>DeskPRO is currently in dev mode which requires the cache directory at <code>$cache_dir</code> to be writable. Please
make this directory writable and try again.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
			}

			exit;

		#------------------------------
		# Dev mode, not using raw assets, no build files
		#------------------------------

		} elseif ($env == 'dev' && (empty($DP_CONFIG['debug']['raw_assets']) && !is_file($web_dir.'/build/js/agent-all.js'))) {
			if (php_sapi_name() == 'cli') {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflineMessage();
					exit(1);
				}
				echo <<<'TXT'
You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:

	1) Enable raw assets by editing /config.php and adding this line:

		$DP_CONFIG['debug']['raw_assets'] = array('all');

	2) Or alternatively you can build assets by running app/bin/build/build-assetic.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<'HTML'
<p>You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:<br /><br /></p>

<p>1) Enable raw assets by editing <code>/config.php</code> and adding this line:

<pre>
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build assets by running <code>app/bin/build/build-assetic.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
			}
			exit;
		}
	}

	####################################################################################################################
	# Request Helpers
	####################################################################################################################

	/**
	 * @var string
	 */
	protected static $base_url;

	/**
	 * @var string
	 */
	protected static $base_path;

	/**
	 * @var string
	 */
	protected static $path_info;

	/**
	 * @var string
	 */
	protected static $request_uri;

	/**
	 * @var string
	 */
	protected static $method;

	/**
	 * @var string
	 */
	protected static $languages;

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getPathInfo()
	{
		if (self::$path_info !== null) {
			return self::$path_info;
		}

		$baseUrl = self::getBaseUrl();

		if (null === ($requestUri = self::getRequestUri())) {
			return '/';
		}

		$pathInfo = '/';

		// Remove the query string from REQUEST_URI
		if ($pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}

		if ((null !== $baseUrl) && (false === ($pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)))))) {
			// If substr() returns false then PATH_INFO is set to an empty string
			return '/';
		} elseif (null === $baseUrl) {
			return $requestUri;
		}

		self::$path_info = (string)$pathInfo;
		return self::$path_info;
	}


	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getBaseUrl()
	{
		if (self::$base_url !== null) {
			return self::$base_url;
		}

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$strpos = 'stripos';
			$filter = 'strtolower';
		} else {
			$strpos = 'strpos';
			$filter = function($in) { return $in; };
		}

		$filename = $filter(basename((isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : null)));

		if ($filter(basename((isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null))) === $filename) {
			$baseUrl = (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null);
		} elseif ($filter(basename((isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null))) === $filename) {
			$baseUrl = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null);
		} elseif ($filter(basename((isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null))) === $filename) {
			$baseUrl = (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null); // 1and1 shared hosting compatibility
		} else {
			// Backtrack up the script_filename to find the portion matching
			// php_self
			$path	= (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
			$file	= (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '');
			$segs	= explode('/', trim($file, '/'));
			$segs	= array_reverse($segs);
			$index   = 0;
			$last	= count($segs);
			$baseUrl = '';
			do {
				$seg	 = $segs[$index];
				$baseUrl = '/'.$seg.$baseUrl;
				++$index;
			} while (($last > $index) && (false !== ($pos = $strpos($path, $baseUrl))) && (0 != $pos));
		}

		// Does the baseUrl have anything in common with the request_uri?
		$requestUri = self::getRequestUri();

		if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
			// full $baseUrl matches
			return $baseUrl;
		}

		if ($baseUrl && 0 === $strpos($requestUri, dirname($baseUrl))) {
			// directory portion of $baseUrl matches
			return rtrim(dirname($baseUrl), '/');
		}

		$truncatedRequestUri = $requestUri;
		if (($pos = strpos($requestUri, '?')) !== false) {
			$truncatedRequestUri = substr($requestUri, 0, $pos);
		}

		$basename = basename($baseUrl);
		if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
			// no match whatsoever; set it blank
			return '';
		}

		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of baseUrl. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
			$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
		}

		self::$base_url = rtrim($baseUrl, '/');
		return self::$base_url;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getBasePath()
	{
		if (self::$base_path !== null) {
			return self::$base_path;
		}

		$filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
		$baseUrl = self::getBaseUrl();
		if (empty($baseUrl)) {
			return '';
		}

		if (basename($baseUrl) === $filename) {
			$basePath = dirname($baseUrl);
		} else {
			$basePath = $baseUrl;
		}

		if ('\\' === DIRECTORY_SEPARATOR) {
			$basePath = str_replace('\\', '/', $basePath);
		}

		return rtrim($basePath, '/');
	}


	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getRequestUri()
	{
		if (self::$request_uri !== null) {
			return self::$request_uri;
		}

		$requestUri = '';

		if ((isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null) && false !== stripos(PHP_OS, 'WIN')) {
			// check this first so IIS will catch
			$requestUri = (isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null);
		} elseif ((isset($_SERVER['IIS_WasUrlRewritten']) ? $_SERVER['IIS_WasUrlRewritten'] : null) == '1' && (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null) != '') {
			// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
			$requestUri = (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null);
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null);
			// HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
			$schemeAndHttpHost = self::getScheme().'://'.self::getHttpHost();
			if (strpos($requestUri, $schemeAndHttpHost) === 0) {
				$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			// IIS 5.0, PHP as CGI
			$requestUri = (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : null);
			if ((isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null)) {
				$requestUri .= '?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null);
			}
		}

		self::$request_uri = $requestUri;
		return self::$request_uri;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getScheme()
	{
		return self::isSecure() ? 'https' : 'http';
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function isSecure()
	{
		return (
			(strtolower((isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null)) == 'on' || (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null) == 1)
			||
			((isset($_SERVER['SSL_HTTPS']) ? $_SERVER['SSL_HTTPS'] : null) == 1)
		);
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getHttpHost()
	{
		$scheme = self::getScheme();
		$port   = self::getPort();

		if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
			return self::getHost();
		}

		return self::getHost().':'.$port;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getPort()
	{
		return (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null);
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getHost()
	{
		if (!$host = (isset($_SERVER['HOST']) ? $_SERVER['HOST'] : null)) {
			if (!$host = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null)) {
				$host = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
			}
		}

		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);

		return trim($host);
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getMethod()
	{
		if (null === self::$method) {
			self::$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
			if ('POST' === self::$method) {
				if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
					self::$method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
				} else if (isset($_GET['_method'])) {
					self::$method = strtoupper($_GET['_method']);
				}
			}
		}

		return self::$method;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getLanguages()
	{
		if (null !== self::$languages) {
			return self::$languages;
		}

		$languages = self::splitHttpAcceptHeader(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
		self::$languages = array();
		foreach ($languages as $lang => $q) {
			if (strstr($lang, '-')) {
				$codes = explode('-', $lang);
				if ($codes[0] == 'i') {
					// Language not listed in ISO 639 that are not variants
					// of any listed language, which can be registered with the
					// i-prefix, such as i-cherokee
					if (count($codes) > 1) {
						$lang = $codes[1];
					}
				} else {
					for ($i = 0, $max = count($codes); $i < $max; $i++) {
						if ($i == 0) {
							$lang = strtolower($codes[0]);
						} else {
							$lang .= '_'.strtoupper($codes[$i]);
						}
					}
				}
			}

			self::$languages[] = $lang;
		}

		return self::$languages;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function splitHttpAcceptHeader($header)
	{
		if (!$header) {
			return array();
		}

		$values = array();
		foreach (array_filter(explode(',', $header)) as $value) {
			// Cut off any q-value that might come after a semi-colon
			if (preg_match('/;\s*(q=.*$)/', $value, $match)) {
				$q	 = (float) substr(trim($match[1]), 2);
				$value = trim(substr($value, 0, -strlen($match[0])));
			} else {
				$q = 1;
			}

			if (0 < $q) {
				$values[trim($value)] = $q;
			}
		}

		arsort($values);
		reset($values);

		return $values;
	}

	/**
	 * Modified to take 2 args
	 *
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public static function getPreferredLanguage(array $preferredLanguages, array $locales = null)
	{
		if (empty($locales)) {
			return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
		}

		if (!$preferredLanguages) {
			return $locales[0];
		}

		$preferredLanguages = array_values(array_intersect($preferredLanguages, $locales));

		return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
	}

	public static function pagelogSaveStat()
	{
		if (!isset($GLOBALS['DP_DB_CON']['db'])) {
			return;
		}
		$db = $GLOBALS['DP_DB_CON']['db'];
		$row = array(
			'date_created'  => gmdate('Y-m-d H:i:s'),
			'request_id'    => dp_pagelog_get('request_id'),
			'user_agent'    => dp_pagelog_get('user_agent') ?: '',
			'user_ip'       => dp_pagelog_get('user_ip') ?: '0.0.0.0',
			'page_id'       => dp_pagelog_get('page_id') ?: '',
			'page_url'      => dp_pagelog_get('page_url') ?: '',
			'response_type' => dp_pagelog_get('response_type') ?: '',
			'response_code' => dp_pagelog_get('response_code') ?: '',
			'response_size' => dp_pagelog_get('response_size') ?: '',
			'query_count'   => dp_pagelog_get('query_count') ?: '',
			'time_php'      => sprintf("%.4f", dp_pagelog_get('time_php') ?: 0.00),
			'time_db'       => sprintf("%.4f", dp_pagelog_get('time_db') ?: 0.00),
			'time_end'      => sprintf("%.4f", dp_pagelog_get('time_end') ?: 0.00),
		);
		$db->insert('log_request_stats', $row);
	}

	public static function pagelogUpdateUsertime($request_id, $set_time)
	{
		if (!isset($GLOBALS['DP_DB_CON']['db'])) {
			return;
		}
		$db = $GLOBALS['DP_DB_CON']['db'];

		$m = null;
		if (!preg_match('#^(\d{4})(\d{2})(\d{2})(\d{2})_#', $request_id, $m)) {
			return;
		}

		$date_str = "{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}";

		$set_time = sprintf("%.4f", floatval($set_time));

		App::getDb()->executeUpdate("
			UPDATE log_request_stats
			SET time_userend = ?
			WHERE date_created BETWEEN ? AND ? AND request_id = ?
		", array(
			$set_time,
			"$date_str:00:00",
			"$date_str:59:59",
			$request_id
		));
	}

	/**#@+
	 * Handling of shutdown stack and xdebug traces
	 */
	private static function DeskPRO_Done_MarkerCheck() {}
	public static function DeskPRO_Done()
	{
		static $called = false;
		if ($called) return;
		$called = true;

		\DpShutdown::run();

		if (dp_pagelog_get('request_id') && $save_pagelog_info = dp_get_config('save_pagelog_info')) {
			if ($save_pagelog_info === true) {
				$save_pagelog_info = 'DeskPRO\\Kernel\\KernelBooter';
			}
			try {
				call_user_func(array($save_pagelog_info, 'pagelogSaveStat'));
			} catch (\Exception $e) {}

			$m = null;
			if (isset($_REQUEST['__dp_reqtime'])) {
				$_REQUEST['__dp_reqtime'];
			}
			if (isset($_REQUEST['__dp_reqtime']) && preg_match('#^([a-zA-Z0-9_]+)_t([0-9_]+)$#', $_REQUEST['__dp_reqtime'], $m)) {
				$request_id = $m[1];
				$set_time = floatval(str_replace('_', '.', $m[2]));
				if ($request_id && $set_time > 0.000) {
					try {
						call_user_func(array($save_pagelog_info, 'pagelogUpdateUsertime'), $request_id, $set_time);
					} catch (\Exception $e) {}
				}
			}
		}

		if (defined('DP_APC_STATS_KEY')) {
			if (isset($GLOBALS['DP_QUERY_COUNT'])) {
				$val = @apc_fetch(DP_APC_STATS_KEY.'.query_count');
				if (!$val) $val = array();

				$time = intval(date('YmdH'));
				if (!isset($val[$time])) {
					$val[$time] = 0;
				}

				$val[$time] += $GLOBALS['DP_QUERY_COUNT'];

				if (count($val[$time]) > 23) {
					ksort($val, \SORT_NUMERIC);
					array_shift($val);
				}

				@apc_store(DP_APC_STATS_KEY.'.query_count', $val);
			}
		}

		if (!defined('DP_DEBUG_TRACE_FILE')) {
			return;
		}

		self::DeskPRO_Done_MarkerCheck();
		xdebug_stop_trace();

		if (isset($GLOBALS['DP_CONFIG']['debug']['enable_debug_trace_keep']) AND $GLOBALS['DP_CONFIG']['debug']['enable_debug_trace_keep']) {
			return;
		}

		$fp = @fopen(DP_DEBUG_TRACE_FILE, 'r');
		if ($fp) {
			@fseek($fp, -150000, \SEEK_END);
			$chunk = @fread($fp, 150000);
			@fclose($fp);

			if (strpos($chunk, 'DeskPRO_Done_MarkerCheck') !== false) {
				@unlink(DP_DEBUG_TRACE_FILE);
			}
		}
	}
	/**#@-*/
}
