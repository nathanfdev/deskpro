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
 * @category Settings
 */

namespace Application\DeskPRO\Settings;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;


/**
 * This class fethces settings
 */
class Settings implements \ArrayAccess, \IteratorAggregate, \Countable
{
	/**
	 * File to fetch defaults from
	 * @var array
	 */
	private $default_settings_file = array();

	/**
	 * Array of array(group => array(settings)) for default settings read in with getDefault()
	 * @var array
	 */
	private $default_settings = null;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * Settings we've loaded so far
	 * @var array
	 */
	private $settings = null;

	/**
	 * @var \DateTimeZone
	 */
	private $default_timezone;

	/**
	 * Virtual settings are not real settings, but depend on other states. For example,
	 * 'core.interact_require_login' isn't a real setting, it is true depending on the registration mode.
	 *
	 * This is a map of varname => callback
	 *
	 * @var array
	 */
	private $virtual_settings = array();


	/**
	 * @param string     $default_settings_file
	 * @param Connection $db
	 */
	public function __construct($default_settings_file, Connection $db = null)
	{
		$this->default_settings_file = $default_settings_file;
		$this->db = $db;

		$this->virtual_settings['core.interact_require_login'] = function($settings) {
			return !$settings->get('core.reg_enabled') || $settings->get('core.reg_required');
		};

		$this->virtual_settings['default_timezone'] = function($settings) {
			return $settings->getDefaultTimezone();
		};

		$this->virtual_settings['tickets_enable_like_search'] = function($settings) {
			if ($settings['core_tickets.enable_like_search_mode'] == 'auto') {
				if ($settings['core_tickets.enable_like_search_auto']) {
					return true;
				} else {
					return false;
				}
			} else {
				if ($settings['core_tickets.enable_like_search_mode'] && $settings['core_tickets.enable_like_search_mode'] != 'off') {
					return true;
				} else {
					return false;
				}
			}
		};
	}


	/**
	 * Loads settings
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	private function _loadSettings()
	{
		$this->settings = array();
		$this->default_settings = array();

		if ($this->default_settings_file) {
			$this->default_settings = require($this->default_settings_file);
		}

		$this->settings = $this->default_settings;

		if ($this->db) {
			$this->settings = array_merge($this->settings, $this->db->fetchAllKeyValue("
				SELECT name, value
				FROM settings
			"));
		}

		if (isset($GLOBALS['DP_CONFIG']['SETTINGS']) && is_array($GLOBALS['DP_CONFIG']['SETTINGS'])) {
			$this->settings = array_merge($this->settings, $GLOBALS['DP_CONFIG']['SETTINGS']);
		}
	}


	/**
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function reloadSettings()
	{
		$this->_loadSettings();
	}


	/**
	 * Get the value of a setting
	 *
	 * @param $name
	 * @param null $default
	 * @return mixed|null
	 */
	public function get($name, $default = null)
	{
		if (!$name) return $default;

		if ($this->settings === null) {
			$this->_loadSettings();
		}

		if (isset($this->virtual_settings[$name])) {
			return call_user_func($this->virtual_settings[$name], $this);
		}

		return isset($this->settings[$name]) ? $this->settings[$name] : $default;
	}


	/**
	 * This loads the default for a value as defined in the setting file
	 *
	 * @param string $name
	 * @return null
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function getDefault($name)
	{
		if (!$name) return null;

		if ($this->settings === null) {
			$this->_loadSettings();
		}

		return isset($this->default_settings[$name]) ? $this->default_settings[$name] : null;
	}


	/**
	 * Get the default values for an entire group
	 *
	 * @param string $group
	 * @param bool $short  True to strip off the group name, false to include the group name in the key
	 * @return array
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function getDefaultGroup($group, $short = true)
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		$group_dot = $group.".";
		$len = strlen($group_dot);

		$ret = array();
		foreach ($this->default_settings as $k => $v) {
			if (substr($k, 0, $len) === $group_dot) {
				if ($short) {
					$k_short = substr($k, $len);
					$ret[$k_short] = $v;
				} else {
					$ret[$k] = $v;
				}
			}
		}

		return $ret;
	}


	/**
	 * Get all settings in a group
	 *
	 * @param string $group
	 * @return array
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function getGroup($group)
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		$group_dot = $group.".";
		$len = strlen($group_dot);

		$ret = array();
		foreach ($this->settings as $k => $v) {
			if (substr($k, 0, $len) === $group_dot) {
				$k_short = substr($k, $len);
				$ret[$k_short] = $v;
			}
		}

		return $ret;
	}



	/**
	 * Manually set the value for one or more settings. Note that these values are
	 * temporary, they are NOT persisted. This is mainly useful for code overrides
	 * or the like.
	 *
	 * @param array $settings
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function setTemporarySettingValues(array $settings)
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		$this->settings = array_merge($this->settings, $settings);
	}


	/**
	 * Persist a new value for a setting, and update this as well
	 *
	 * @param string $setting
	 * @param string $value
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function setSetting($setting, $value)
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		$this->db->beginTransaction();
		try {

			if ($value !== null) {
				if ($value === true) $value = '1';
				else if ($value === false) $value = '0';

				$this->db->executeUpdate("
					INSERT INTO settings
						(name, value)
					VALUES
						(?, ?)
					ON DUPLICATE KEY UPDATE
						value = VALUES(value)
				", array($setting, $value));
			} else {
				$this->db->delete('settings', array('name' => $setting));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$this->settings[$setting] = $value;
	}


	/**
	 * @return \DateTimeZone
	 */
	public function getDefaultTimezone()
	{
		if ($this->default_timezone !== null) {
			return $this->default_timezone;
		}

		try {
			$this->default_timezone = new \DateTimeZone($this->get('core.default_timezone'));
		} catch (\Exception $e) {
			$this->default_timezone = new \DateTimeZone('UTC');
		}

		return $this->default_timezone;
	}


	public function offsetExists($offset)
	{
		return $this->get($offset) !== null;
	}

	public function offsetSet($offset, $value)
	{
		throw new \BadMethodCallException('You cannot set settings');
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('You cannot unset settings');
	}

	public function count()
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		return count($this->settings);
	}

	public function getIterator()
	{
		if ($this->settings === null) {
			$this->_loadSettings();
		}

		return new \ArrayIterator($this->settings);
	}
}
