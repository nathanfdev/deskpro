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

namespace Cloud;

use \PDO;

class CloudConfig
{
	private function __construct() { }

	/**
	 * @var \PDO
	 */
	private static $db;

	/**
	 * @var bool
	 */
	private static $closed = false;

	/**
	 * @var array
	 */
	private static $config;

	/**
	 * @var string
	 */
	private static $vendor_url;

	/**
	 * @var string
	 */
	private static $error_contact;

	/**
	 * @var array
	 */
	private static $builds_path;

	/**
	 * @var array
	 */
	private static $datastore_path;

	/**
	 * Fills the normal DeskPRO configuration from an incoming web request
	 */
	public static function loadFromWeb()
	{
		if (defined('DPC_SITE_ID')) {
			return;
		}

		self::getConfig(null);

		if (!empty($_SERVER['HTTP_HOST'])) {
			$siteinfo = self::getSiteInfoFromDomain($_SERVER['HTTP_HOST']);
		} else {
			$siteinfo = null;
		}

		if (!$siteinfo || $siteinfo['sys_disabled']) {
			header("Location: " . self::getVendorUrl());
			exit();
		}

		self::setLoadedSite($siteinfo);
		self::close();
	}


	/**
	 * Fills normal DeskPRO configuration from CLI commands. We're looking for --dpc-site-id here.
	 */
	public static function loadFromCli()
	{
		$_SERVER['argv_real'] = $_SERVER['argv'];

		if (($id_k = array_search('--dpc-site-id', $_SERVER['argv'])) === false || !isset($_SERVER['argv'][$id_k+1])) {
			echo "Missing Site ID\n";
			exit(1);
		}

		$site_id = $_SERVER['argv'][$id_k+1];
		$siteinfo = self::getSiteInfoFromId($site_id);

		if (!$siteinfo) {
			echo "Invalid Site ID\n";
			exit(1);
		}

		// Remove --dpc-site-id from arguments
		$_SERVER['argv'] = array();
		foreach ($_SERVER['argv_real'] as $k => $v) {
			if ($k == $id_k || $k == ($id_k+1)) {
				continue;
			}

			$_SERVER['argv'][] = $v;
		}

		self::setLoadedSite($siteinfo);
		self::close();
	}


	/**
	 * @param array $siteinfo
	 */
	public static function setLoadedSite(array $siteinfo)
	{
		// Append the siteid to all PHP calls
		define('DP_PHP_BIN_ARGS',        "--dpc-site-id {$siteinfo['id']}");

		define('DPC_IS_CLOUD',           true);
		define('DPC_SITE_ID',            $siteinfo['id']);
		define('DPC_SITE_DOMAIN',        $siteinfo['master_domain']);
		define('DPC_SITE_DOMAIN_ALT',    $siteinfo['custom_domain']);
		define('DPC_SITE_DATADIR',       self::getConfig('datastore_path') . '/' . str_replace('.', '_', DPC_SITE_DOMAIN));
		define('DPC_SITE_BUILD_NUM',     $siteinfo['build_number']);
		define('DPC_ACCOUNT_ID',         $siteinfo['account_id']);
		define('DPC_AGENTS',             $siteinfo['agents']);
		define('DPC_DEMO_EXPIRE',        $siteinfo['is_demo'] ? $siteinfo['demo_expire_at'] : 0);
		define('DP_DATABASE_HOST',       $siteinfo['db_host']);
		define('DP_DATABASE_USER',       $siteinfo['db_user']);
		define('DP_DATABASE_PASSWORD',   $siteinfo['db_password']);
		define('DP_DATABASE_NAME',       $siteinfo['db_name']);
		define('DP_TECHNICAL_EMAIL',     'team@deskpro.com');

		if (!is_dir(DPC_SITE_DATADIR)) {
			mkdir(DPC_SITE_DATADIR, 0777, true);
			mkdir(DPC_SITE_DATADIR . '/backups', 0777);
			mkdir(DPC_SITE_DATADIR . '/debug', 0777);
			mkdir(DPC_SITE_DATADIR . '/files', 0777);
			mkdir(DPC_SITE_DATADIR . '/logs', 0777);
			mkdir(DPC_SITE_DATADIR . '/tmp', 0777);
		}

		if (!defined('DP_ROOT')) {
			define('DP_ROOT',  self::getBuildsPath() . '/' . $siteinfo['build_number'] . '/app');
			define('DP_WEB_ROOT',  self::getBuildsPath() . '/' . $siteinfo['build_number']);

			define('DP_CONFIG_FILE', __DIR__.'/dp-config.php');
		}
	}


	/**
	 * Get the URL of the vendor website.
	 *
	 * @return string
	 */
	public static function getVendorUrl()
	{
		if (!self::$vendor_url) {
			self::getConfig(null);
		}

		return self::$vendor_url;
	}


	/**
	 * Get the email address for errors
	 *
	 * @return string
	 */
	public static function getErrorContact()
	{
		if (!self::$vendor_url) {
			self::getConfig(null);
		}

		return self::$error_contact;
	}


	/**
	 * Get the email address for errors
	 *
	 * @return string
	 */
	public static function getBuildsPath()
	{
		if (!self::$builds_path) {
			self::getConfig(null);
		}

		return self::$builds_path;
	}


	/**
	 * Get the email address for errors
	 *
	 * @return string
	 */
	public static function getDatastorePath()
	{
		if (!self::$builds_path) {
			self::getConfig(null);
		}

		return self::$datastore_path;
	}


	/**
	 * @param string $key
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getConfig($key, $default = null)
	{
		if (self::$closed) {
			throw new \Exception("Already closed");
		}

		if (!self::$config) {
			self::$config = require __DIR__.'/config.php';

			self::$vendor_url     = self::$config['vendor_url'];
			self::$error_contact  = self::$config['error_contact'];
			self::$builds_path    = self::$config['builds_path'];
			self::$datastore_path = self::$config['datastore_path'];
		}

		// Null key just means laod config
		if (!$key) return null;

		return isset(self::$config[$key]) ? self::$config[$key] : $default;
	}


	/**
	 * Closes access to potentially 'dangerous' methods, such as database access.
	 *
	 * @throws \Exception
	 */
	public static function close()
	{
		if (self::$closed) {
			throw new \Exception("Already closed");
		}

		self::$closed = true;
		self::$db = null;
		self::$config = null;
	}


	/**
	 * @return \PDO
	 */
	public static function getDb()
	{
		if (self::$closed) {
			throw new \Exception("Already closed");
		}

		if (self::$db) {
			return self::$db;
		}

		self::$db = new \PDO(
			sprintf("mysql:host=%s;dbname=%s", self::getConfig('db_host'), self::getConfig('db_name')),
			self::getConfig('db_user'),
			self::getConfig('db_password')
		);

		return self::$db;
	}


	/**
	 * @param int $id
	 * @return array
	 */
	public static function getSiteInfoFromId($id)
	{
		$stmt = self::getDb()->prepare("
			SELECT
				cloud_sites.*,
				cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
			FROM cloud_sites
			LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
			WHERE cloud_sites.id = ? AND cloud_sites.build_number > 0 AND cloud_accounts.is_offline = 0
			LIMIT 1
		");
		$stmt->execute(array($id));

		$site = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $site;
	}


	/**
	 * @param string $domain
	 * @return array
	 */
	public static function getSiteInfoFromDomain($domain)
	{
		$stmt = self::getDb()->prepare("
			SELECT
				cloud_sites.*,
				cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
			FROM cloud_sites
			LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
			WHERE (cloud_sites.master_domain = ? OR cloud_sites.custom_domain = ?) AND cloud_sites.build_number > 0 AND cloud_accounts.is_offline = 0
			LIMIT 1
		");
		$stmt->execute(array($domain, $domain));

		$site = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $site;
	}
}