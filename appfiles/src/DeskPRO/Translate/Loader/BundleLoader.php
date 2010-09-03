<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Translate\Loader;

/**
 * Loads core from CoreBundle core.php, and tech_whatever as TechBundle whatever.php etc.
 */
class BundleLoader implements LoaderInterface
{
	protected $bundle_paths;

	/**
	 * @param array $bundle_paths BundleName=>Path
	 */
	public function __construct(array $bundle_paths = array())
	{
		$this->bundle_paths = array();

		foreach ($bundle_paths as $name => $path) {

			// Conver SomeNamedBundle to somenamed
			$name = strtolower($name);
			$name = str_replace('bundle', '', $name);

			$this->bundle_paths[$name] = $path;
		}
	}



	public function load($groups)
	{
		if (!is_array($groups)) $groups = array($groups);

		$phrases = array();

		foreach ($groups as $group) {

			$group = strtolower($group);

			$bundle_parts = explode('_', $group, 2);
			if (!isset($bundle_parts[1])) $bundle_parts[1] = $bundle_parts[0];

			list($bundle_name, $name) = $bundle_parts;

			if (!isset($this->bundle_paths[$bundle_name])) {
				throw new \InvalidArgumentException("No bundle named `$group`");
			}

			$filepath = $this->bundle_paths[$bundle_name] . '/' . $name . '.php';
			
			if (!is_file($filepath)) {
				return array();
			}

			$phrases[$group] = include($filepath);
		}

		return $phrases;
	}
}