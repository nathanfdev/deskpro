<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Settings
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Settings;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * This class fethces settings
 */
class Settings implements \ArrayAccess
{
	/**
	 * An array of group=>paths
	 * @var array
	 */
	protected $settings_paths = array();


	/**
	 * Plain database connection for raw queries
	 * @var Application\DeskPRO\DBAL\Connection
	 */
	protected $db;


	/**
	 * Settings we've loaded so far
	 * @var arrau
	 */
	protected $settings = array();


	/**
	 * An array of groups that we need to load in the next batch
	 * @var array
	 */
	protected $_pending_groups = array();


	/**
	 * An array of groups we've already loaded
	 * @var array
	 */
	protected $_loaded_groups = array();

	/**
	 * Have loaded custom settings yet?
	 * @var bool
	 */
	protected $_has_loaded_db = false;



	public function __construct(array $settings_paths, \Application\DeskPRO\DBAL\Connection $db = null)
	{
		$this->settings_paths = $settings_paths;
		$this->db = $db;
	}



	/**
	 * Get the value of a setting
	 *
	 * @param  string $name The name of the setting
	 * @return mixed
	 */
	public function get($name)
	{
		if (!$name) return '';

		if (!isset($this->settings[$name])) {
			$check_group = $this->getGroupFromName($name);

			if (!in_array($check_group, $this->_loaded_groups)) {
				$this->_pending_groups[] = $check_group;

				$this->_loadPendingGroups();
				return $this->get($name);
			}

			return null;
		}

		return $this->settings[$name];
	}



	/**
	 * Manually set the value for one or more settings. Note that these values are
	 * temporary, they are NOT persisted. This is mainly useful for code overrides
	 * or the like.
	 *
	 * @param array $settings
	 */
	public function setTemporarySettingValues(array $settings)
	{
		$this->settings = array_merge($this->settings, $settings);
	}



	/**
	 * Add a group of settings we want to load.
	 *
	 * @param  $group
	 */
	public function loadGroups($group)
	{
		for ($i = 0, $max = func_num_args(); $i < $max; $i++) {
			$group = func_get_arg($i);
			if (!in_array($group, $this->_loaded_groups)) {
				$this->pending_load[] = $group;
			}
		}
	}


	/**
	 * When an unknown setting is encountered in a group we haven't loaded yet,
	 * we'll load all pending groups.
	 */
	protected function _loadPendingGroups()
	{
		if (!$this->_pending_groups) {
			return;
		}

		$this->_pending_groups = array_unique($this->_pending_groups);
		$this->_pending_groups = Arrays::removeFalsey($this->_pending_groups);

		#------------------------------
		# Load from filesystem first
		#------------------------------

		foreach ($this->_pending_groups as $group) {
			if (strpos($group, '_') !== false) {
				list($key, $name) = explode('_', $group, 2);
			} else {
				$key = $group;
				$name = $group;
			}

			// We dont know about these settings?
			if (!isset($this->settings_paths[$key])) {
				trigger_error("Unknown settings group `$group`", \E_USER_WARNING);
				continue;
			}

			$path = $this->settings_paths[$key] . '/' . $name . '.php';

			$group_settings = require($path);
			$this->settings = array_merge($this->settings, $group_settings);
		}

		unset($group_settings);

		#------------------------------
		# Load from db (user-specified overrides)
		#------------------------------

		if (!$this->_has_loaded_db) {

			$this->_has_loaded_db = true;

			if (($db_settings = App::getCache('common')->load('settings')) === false) {
				$db_settings = $this->db->fetchAllKeyValue("
					SELECT name, value
					FROM settings
				");

				App::getCache('common')->save($db_settings, null, array('settings'));
			}

			$this->settings = array_merge($this->settings, $db_settings);
		}

		$this->_loaded_groups = array_merge($this->_loaded_groups, $this->_pending_groups);
		$this->_pending_groups = array();

		#------------------------------
		# Config overrides
		#------------------------------

		// Always merge with those from config file, they are effectively
		// hard-coded overrides (ex useful if something broke and you have to disable)
		$config_settings = App::getConfig('SETTINGS');
		if ($config_settings) {
			$this->settings = array_merge($this->settings, $config_settings);
		}
	}



	/**
	 * Get the group from the name of a setting.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function getGroupFromName($name)
	{
		$pos = strpos($name, '.');
		if ($pos === false) {
			return false;
		}

		return substr($name, 0, $pos);
	}



	public function offsetExists($offset)
	{
		return $this->get($offset) !== null;
	}

	public function offsetSet($offset, $value)
	{
		throw new BadMethodCallException('You cannot set settings');
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetUnset($offset)
	{
		throw new BadMethodCallException('You cannot unset settings');
	}
}