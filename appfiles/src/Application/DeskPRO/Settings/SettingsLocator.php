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
 * This class locates a settings file based on the group name
 */
class SettingsLocator implements \ArrayAccess
{
	protected $settings_paths = array();
	protected $failed_paths = array();

	/**
	 * @param array $settings_paths Initial array of known paths
	 */
	public function __construct(array $settings_paths = array())
	{
		$this->settings_paths = $settings_paths;
	}


	/**
	 * When a path is not found, we can try to look it up here. Allows for lazy-mapping.
	 *
	 * @param  $key
	 * @return void
	 */
	public function initPath($key)
	{
		// Already tried to locate
		if (isset($this->settings_paths[$key]) OR isset($this->failed_paths[$key])) {
			return;
		}

		$plugin_manager = App::get('deskpro.plugin_manager');
		if ($plugin_manager->hasPlugin($key)) {
			$this->settings_paths[$key] = $plugin_manager->getResourcesPath($key) . '/settings';
		} else {
			$this->failed_paths[$key] = true;
		}
	}

	
	public function offsetExists($offset)
	{
		$this->initPath($offset);
		return isset($this->settings_paths);
	}

	public function offsetGet($offset)
	{
		$this->initPath($offset);
		return $this->settings_paths[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->settings_paths[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->settings_paths[$offset]);
	}
}