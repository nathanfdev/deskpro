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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build\Helper201405;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Orb\Util\OptionsArray;

class UsersourceUpgrader
{
	/**
	 * @var DeskproContainer
	 */
	private $container;

	/**
	 * @var array
	 */
	private $info;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $options;


	/**
	 * @param DeskproContainer $container
	 * @param array            $info
	 */
	public function __construct(DeskproContainer $container, array $info)
	{
		$this->container = $container;

		$this->info = $info;
		$this->type = $info['source_type'];
		$this->options = unserialize($info['options']);
	}


	/**
	 * Upgrade the usersource
	 */
	public function upgrade()
	{
		switch ($this->info['source_type']) {
			case 'active_directory':
				$this->upgradeActiveDirectory();
				break;
			case 'db_table_php_password_check':
				$this->upgradeCustomDb();
				break;
			case 'ez_publish':
				$this->upgradeEzPublish();
				break;
			case 'facebook':
				$this->upgradeFacebook();
				break;
			case 'google':
				$this->upgradeGoogle();
				break;
			case 'ldap':
				$this->upgradeLdap();
				break;
			case 'php_bb_2':
				$this->upgradePhpbb();
				break;
			case 'php_bb_3':
				$this->upgradePhpbb();
				break;
			case 'twitter':
				$this->upgradeTwitter();
				break;
			case 'vbulletin':
				$this->upgradeVbulletin();
				break;
			case 'xenforo':
				$this->upgradeXenForo();
				break;
			case 'joomla':
				$this->upgradeJoomla();
				break;
			case 'magento':
				$this->upgradeMagento();
				break;
			default:
				$this->updateUsersource($this->type, $this->options, null);
				break;
		}
	}


	/**
	 * @param string $package_name
	 * @param array  $app_settings
	 * @return AppInstance
	 */
	private function createApp($package_name, array $app_settings)
	{
		$manager = $this->container->getAppManager();
		$package = $manager->getPackage($package_name);

		$app = new AppInstance();
		$app->package = $package;
		$app->title = $package->title;
		$app->setSettings($app_settings);
		$this->container->getEm()->persist($app);
		$this->container->getEm()->flush();

		return $app;
	}


	/**
	 * @param string $adapter_class
	 * @param array $adapter_settings
	 * @param AppInstance $app
	 */
	private function updateUsersource($adapter_class, $adapter_settings, AppInstance $app = null)
	{
		$this->container->getDb()->update('usersources', array(
			'source_type' => $adapter_class,
			'options'     => json_encode($adapter_settings),
			'app_id'      => $app ? $app->id : null
		), array('id' => $this->info['id']));
	}


	/**
	 * Upgrade an ActiveDirectory source
	 */
	private function upgradeActiveDirectory()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\ActiveDirectory';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_active_directory';
		$o = new OptionsArray($adapter_settings);
		$app_settings = array(
			'port'              => $o->get('port', ''),
			'host'              => $o->get('host', ''),
			'base_dn'           => $o->get('baseDn', ''),
			'service_username'  => $o->get('username', ''),
			'service_password'  => $o->get('password', ''),
			'domain_name'       => $o->get('accountDomainName'),
			'domain_name_short' => $o->get('accountDomainNameShort'),
			'filter'            => $o->get('accountFilterFormat'),
		);

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a custom DB source
	 */
	private function upgradeCustomDb()
	{
		$o = new OptionsArray($this->options);
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\DbTablePhpPasswordCheck';

		$php = $o->get('password_php', '');
		$php .= "\n\n\$is_valid = \$pass;\n";

		$adapter_settings = array(
			'connection_options' => array(
				'driver'   => 'pdo_mysql',
				'host'     => null,
				'port'     => null,
				'dbname'   => '',
				'user'     => $o->get('db_username', ''),
				'password' => $o->get('db_password', '')
			),
			'table'            => $o->get('table', ''),
			'field_id'         => $o->get('field_id', ''),
			'field_username'   => $o->get('field_username', ''),
			'field_email'      => $o->get('field_email', ''),
			'field_password'   => $o->get('field_password', ''),
			'field_first_name' => $o->get('field_first_name', ''),
			'field_last_name'  => $o->get('field_last_name', ''),
			'password_php'     => $php,
		);

		$params = $this->parseDsn($o->get('db_dsn'));
		if (isset($params['driver'])) {
			$adapter_settings['connection_options']['driver'] = $params['driver'];
		}
		if (isset($params['host'])) {
			$adapter_settings['connection_options']['host'] = $params['host'];
		}
		if (isset($params['port'])) {
			$adapter_settings['connection_options']['port'] = $params['port'];
		}
		if (isset($params['dbname'])) {
			$adapter_settings['connection_options']['dbname'] = $params['dbname'];
		}

		$app_package_name = 'deskpro_us_db';
		$app_settings = array(
			'db_type'          => $adapter_settings['connection_options']['driver'],
			'db_host'          => $adapter_settings['connection_options']['host'],
			'db_port'          => $adapter_settings['connection_options']['port'],
			'db_name'          => $adapter_settings['connection_options']['dbname'],
			'db_username'      => $adapter_settings['connection_options']['user'],
			'db_password'      => $adapter_settings['connection_options']['password'],
			'db_with_options'  => '',
			'table'            => $adapter_settings['table'],
			'field_id'         => $adapter_settings['field_id'],
			'field_username'   => $adapter_settings['field_username'],
			'field_email'      => $adapter_settings['field_email'],
			'field_password'   => $adapter_settings['field_password'],
			'field_first_name' => $adapter_settings['field_first_name'],
			'field_last_name'  => $adapter_settings['field_last_name'],
			'password_php'     => $adapter_settings['password_php'],
		);

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * @param string $dsn
	 * @return array
	 */
	private function parseDsn($dsn)
	{
		if (preg_match('#^(\w+):(.*?)$#', $dsn, $m)) {
			$driver = $m[1];
			$info = $m[2];
		} else {
			$driver = 'mysql';
			$info = $dsn;
		}

		switch ($driver) {
			case 'mysql':
				$driver = 'pdo_mysql';
				break;
		}

		$params = array();
		$params['driver'] = $driver;

		$parts = explode(';', $info);
		foreach ($parts as $p) {
			if (strpos($p, '=') === false) continue;
			list ($name, $value) = explode('=', $p, 2);
			$name = trim($name);
			$value = trim($value);

			$params[$name] = $value;
		}

		return $params;
	}


	/**
	 * Upgrade an ezC source
	 */
	private function upgradeEzPublish()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\EzPublish';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_ezpublish';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a Facebook source
	 */
	private function upgradeFacebook()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Facebook';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_facebook';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a Google source
	 */
	private function upgradeGoogle()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Google';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_google';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade an LDAP source
	 */
	private function upgradeLdap()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Ldap';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_ldap';
		$o = new OptionsArray($adapter_settings);
		$app_settings = array(
			'port'              => $o->get('port', ''),
			'host'              => $o->get('host', ''),
			'base_dn'           => $o->get('baseDn', ''),
			'service_username'  => $o->get('username', ''),
			'service_password'  => $o->get('password', ''),
			'filter'            => $o->get('accountFilterFormat'),
		);

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a phpBB2 or phpBB3 source
	 */
	private function upgradePhpbb()
	{
		if ($this->type == 'php_bb_2') {
			$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\PhpBb2';
		} else {
			$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\PhpBb3';
		}
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_phpbb';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a twitter source
	 */
	private function upgradeTwitter()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Twitter';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_twitter';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a XF source
	 */
	private function upgradeVbulletin()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Vbulletin';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_vbulletin';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}

	/**
	 * Upgrade a XF source
	 */
	private function upgradeXenForo()
	{
		$adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Xenforo';
		$adapter_settings = $this->options;

		$app_package_name = 'deskpro_us_xenforo';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}


	/**
	 * Upgrade a Joomla source
	 */
	private function upgradeJoomla()
	{
		$adapter_class = 'deskpro_us_joomla\\Usersource\\Adapter\\Joomla';
		$adapter_settings = array(
			'joomla_url'    => $this->container->getSetting('Joomla.joomla_url'),
			'joomla_secret' => $this->container->getSetting('Joomla.joomla_secret'),
		);

		$app_package_name = 'deskpro_us_joomla';
		$app_settings = $adapter_settings;

		$app = $this->createApp($app_package_name, $app_settings);
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
	}

	/**
	 * Upgrade a Joomla source
	 */
	private function upgradeMagento()
	{
		$adapter_class = 'deskpro_magento\\Usersource\\Adapter\\Magento';
		$adapter_settings = array(
			'url'      => $this->container->getSetting('Magento.url'),
			'api_user' => $this->container->getSetting('Magento.api_user'),
			'api_key'  => $this->container->getSetting('Magento.api_key'),
			'sso_js'   => !empty($this->options['sso_js']) && $this->options['sso_js'] ? true : false
		);

		// Should already have a magento app from previous upgrade step

		$manager = $this->container->getAppManager();
		$app = $manager->getPackageApp('deskpro_magento');
		if (!$app) {
			return;
		}
		$this->updateUsersource($adapter_class, $adapter_settings, $app);
		$settings = $app->getSettings();
		$settings['enable_usersource'] = true;
		$settings['enable_sso'] = $adapter_settings['sso_js'];
		$app->setSettings($settings);
		$this->container->getEm()->persist($app);
		$this->container->getEm()->flush($app);
	}
}