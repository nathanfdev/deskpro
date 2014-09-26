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

namespace Application\DeskPRO\ServerPhpInfo;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Env;
use Orb\Util\Util;

class ServerPhpInfo
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	/**
	 * @var string
	 */

	protected $config_hash;

	public function __construct(EntityManager $em)
	{
		$this->em          = $em;
		$this->config_hash = md5_file(DP_CONFIG_FILE);
	}

	/**
	 * @param bool $noencode
	 *
	 * @return array
	 */

	public function getPhpInfo($noencode = false)
	{
		return $this->_getInfo($noencode);
	}

	/**
	 * @param bool $noencode
	 *
	 * @return array
	 */

	protected function _getInfo($noencode = false)
	{
		$config_hash = md5_file(DP_CONFIG_FILE);

		#------------------------------
		# Binary paths
		#------------------------------

		$binary_paths = array(
			'php'       => dp_get_config('php_path'),
			'mysql'     => dp_get_config('mysql_path'),
			'mysqldump' => dp_get_config('mysqldump_path')
		);

		#------------------------------
		# Web PHP
		#------------------------------

		$web_php               = array();
		$web_php['php_config'] = array(
			'version'           => phpversion(),
			'memory_limit'      => Env::getMemoryLimit(),
			'memory_limit_real' => DP_REAL_MEMSIZE,
			'error_log'         => ini_get('error_log'),
			'error_log_real'    => DP_REAL_ERROR_LOG,
		);

		ob_start();
		phpinfo();

		$phpinfo = ob_get_clean();

		preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

		if (isset($m[1])) {

			$phpinfo = $m[1];
		}

		$web_php['phpinfo']              = $phpinfo;
		$web_php['ini_path']             = Env::getPhpIniPathFromInfo($web_php['phpinfo']);
		$web_php['effective_max_upload'] = Env::getEffectiveMaxUploadSize();

		#------------------------------
		# CLI PHP
		#------------------------------

		$cli_php = array('phpinfo' => null, 'php_config' => null);

		if (file_exists(dp_get_data_dir() . '/cli-phpinfo.html')) {

			$phpinfo             = file_get_contents(dp_get_data_dir() . '/cli-phpinfo.html');
			$cli_php['ini_path'] = Env::getPhpIniPathFromInfo($phpinfo);

			if (strpos($phpinfo, '<body') === false) {

				if (!$noencode) {

					$phpinfo = '<code>' . nl2br(htmlspecialchars($phpinfo)) . '</code>';
				}
			} else {

				preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

				if (isset($m[1])) {

					$phpinfo = $m[1];
				}
			}

			$cli_php['phpinfo'] = $phpinfo;
		}

		if (file_exists(dp_get_data_dir() . '/cli-server-reqs-check.dat')) {

			$data = file_get_contents(dp_get_data_dir() . '/cli-server-reqs-check.dat');
			$data = @unserialize($data);

			$cli_php['php_config'] = $data;

			if (isset($cli_php['php_config']['memory_limit_real'])) {

				if ($cli_php['php_config']['memory_limit_real'] == -1) {

					$cli_php['effective_max_upload'] = -1;

				} else {

					$cli_php['effective_max_upload'] = $cli_php['php_config']['memory_limit_real'] / 3;
				}
			}
		}

		$has_apc = false;

		if (function_exists('apc_store') && ini_get('apc.enabled')) {

			$has_apc = true;
		}

		$has_wincache = false;

		if (extension_loaded('wincache') && ini_get('wincache.ocenabled')) {

			$has_wincache = true;
		}

		$debug_settings = array();

		foreach (dp_get_config('debug') as $k => $v) {

			if (!$v) {

				continue;
			}

			if (is_array($v)) {

				foreach ($v as $sk => $sv) {

					if (!$sv) {

						continue;
					}

					if (!is_scalar($sv)) {

						$debug_settings[$k . '.' . $sk] = print_r($sv, 1);

					} else {

						$debug_settings[$k . '.' . $sk] = $sv;
					}
				}
			} else {

				if (!is_scalar($v)) {

					$debug_settings[$k] = print_r($v, 1);

				} else {

					$debug_settings[$k] = $v;
				}
			}
		}

		if (dp_get_config('cache.page_cache')) {

			foreach (dp_get_config('cache.page_cache') as $k => $v) {

				$debug_settings['cache.page_cache.' . $k] = $v;
			}
		}

		if (dp_get_config('SETTINGS')) {

			foreach (dp_get_config('SETTINGS') as $k => $v) {

				$debug_settings['SETTINGS.' . $k] = $v;
			}
		}

		$debug_settings['rewrite_urls'] = print_r(dp_get_config('rewrite_urls', false), true);

		$memtest_link = App::getSetting('core.deskpro_url') . '?_sys=memtest';
		$memtest_link .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'memtest', 86400);

		$web_php_link = App::getSetting('core.deskpro_url') . '?_sys=phpinfo';
		$web_php_link .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'phpinfo', 86400);

		$cli_php_link = App::getSetting('core.deskpro_url') . '?_sys=phpinfo';
		$cli_php_link .= '&cli=1&_=' . Util::generateStaticSecurityToken($this->config_hash . 'phpinfo', 86400);

		$apc_link = App::getSetting('core.deskpro_url') . '?_sys=apc';
		$apc_link .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'apc', 86400);

		$opcache_link = null;
		if (version_compare(phpversion(), '5.5.0', '>=') && extension_loaded('Zend OPcache') && (int) ini_get('opcache.enable')) {
			$opcache_link = App::getSetting('core.deskpro_url') . '?_sys=opcache';
			$opcache_link .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'opcache', 86400);
		}

		$wincache_link = App::getSetting('core.deskpro_url') . '?_sys=wincache';
		$wincache_link .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'wincache', 86400);

		return array(
			'binary_paths'   => $binary_paths,
			'web_php'        => $web_php,
			'cli_php'        => $cli_php,
			'config_hash'    => $config_hash,
			'has_apc'        => $has_apc,
			'has_wincache'   => $has_wincache,
			'debug_settings' => $debug_settings,
			'memtest_link'   => $memtest_link,
			'web_php_link'   => $web_php_link,
			'cli_php_link'   => $cli_php_link,
			'apc_link'       => $apc_link,
			'opcache_link'   => $opcache_link,
			'wincache_link'  => $wincache_link,
		);
	}
}