<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\DBAL\Connection;
use Orb\Util\Strings;

class ServerStats
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	public function getStats()
	{
		$stats = array();

		#------------------------------
		# PHP info
		#------------------------------

		$stats['php_version'] = phpversion();
		$stats['php_memory_limit'] = \Orb\Util\Env::getMemoryLimit();

		if (function_exists('apc_cache_info')) {
			$stats['php_has_apc'] = true;
		}

		if (function_exists('mb_get_info')) {
			$stats['php_has_mbstring'] = true;
		}

		if (function_exists('gd_info')) {
			$stats['php_has_gd'] = true;
		}

		if (class_exists('Imagick', false)) {
			$stats['php_has_imagick'] = true;
		}

		if (class_exists('Gmagick', false)) {
			$stats['php_has_gmagick'] = true;
		}

		#------------------------------
		# MySQL info
		#------------------------------

		try {
			$stats['mysql_version'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'version'", array(), 1);
			$stats['mysql_read_buffer_size'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'read_buffer_size'", array(), 1);
			$stats['mysql_default_storage_engine'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'default_storage_engine'", array(), 1);
			$stats['mysql_join_buffer_size'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'join_buffer_size'", array(), 1);
			$stats['mysql_key_buffer_size'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'key_buffer_size'", array(), 1);
			$stats['mysql_max_allowed_packet'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'max_allowed_packet'", array(), 1);
			$stats['mysql_max_tmp_tables'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'max_tmp_tables'", array(), 1);
			$stats['mysql_max_user_connections'] = $this->db->fetchColumn("SHOW VARIABLES LIKE 'max_user_connections'", array(), 1);
			foreach ($this->db->fetchAllKeyValue("SHOW VARIABLES LIKE '%innodb%'") as $k => $v) {
				$stats["mysql_$k"] = $v;
			}
		} catch (\Exception $e) {}

		#------------------------------
		# OS / Server info
		#------------------------------

		if (strpos(strtoupper(PHP_OS), 'WIN') === 0) {
			$stats['server_os'] = 'win';
		} elseif (strpos(strtoupper(PHP_OS), 'DARWIN') === 0) {
			$stats['server_os'] = 'mac';
		} elseif (strpos(strtoupper(PHP_OS), 'FREEBSD') === 0) {
			$stats['server_os'] = 'freebsd';
		} elseif (strpos(strtoupper(PHP_OS), 'LINUX') === 0) {
			$stats['server_os'] = 'linux';
		} else {
			$stats['server_os'] = PHP_OS;
		}

		$stats['server_uname'] = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');

		#------------------------------
		# Web server
		#------------------------------

		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			if (strpos(strtoupper($_SERVER['SERVER_SOFTWARE']), 'APACHE') !== false) {
				$stats['web_server'] = 'apache';
			} elseif (strpos(strtoupper($_SERVER['SERVER_SOFTWARE']), 'IIS') !== false) {
				$stats['web_server'] = 'iis';
			} elseif (strpos(strtoupper($_SERVER['SERVER_SOFTWARE']), 'NGINX') !== false) {
				$stats['web_server'] = 'nginx';
			} elseif (strpos(strtoupper($_SERVER['SERVER_SOFTWARE']), 'CHEROKEE') !== false) {
				$stats['web_server'] = 'cherokee';
			} elseif (strpos(strtoupper($_SERVER['SERVER_SOFTWARE']), 'LIGHTTPD') !== false) {
				$stats['web_server'] = 'lighttpd';
			} else {
				$stats['web_server'] = $_SERVER['SERVER_SOFTWARE'];
			}

			$stats['server_software'] = $_SERVER['SERVER_SOFTWARE'];

			if (function_exists('apache_get_modules')) {
				$stats['has_mod_rewrite'] = in_array('mod_rewrite', apache_get_modules());
			}
		}

		return $stats;
	}
}
