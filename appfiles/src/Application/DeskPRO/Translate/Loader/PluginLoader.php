<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * Loads phrases from plugin directories in a similar manner as the BundleLoader loads
 * them from the bundle directories.
 */
class BundleLoader implements LoaderInterface
{
	protected $plugin_manager;

	public function __construct($plugin_manager)
	{
		$this->plugin_manager = $plugin_manager;
	}



	public function load($groups, $language)
	{
		// We dont actually use lang here. the bundle loader
		// is always english, used as the default.

		if (!is_array($groups)) $groups = array($groups);

		$phrases = array();

		foreach ($groups as $group) {

			$group = strtolower($group);

			$name_parts = explode('_', $group, 2);
			if (!isset($name_parts[1])) $name_parts[1] = $name_parts[0];

			list($plugin_name, $name) = $name_parts;

			if (!$this->plugin_manager->hasPlugin($plugin_name)) {
				continue;
			}

			$filepath = $this->plugin_manager->getResourcesPath($plugin_name) . '/language/' . $name . '.php';

			if (!is_file($filepath)) {
				return array();
			}

			$phrases[$group] = include($filepath);
		}

		return $phrases;
	}
}
