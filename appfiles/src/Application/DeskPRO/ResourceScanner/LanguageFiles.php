<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ResourceScanner;

use \Application\DeskPRO\App;
use \Orb\Util\Arrays;

/**
 * The lang system uses phrases from the DB, and then falls back on the
 * English phrases defined in the filesystem. This scans all the bundles for
 * phrase files
 */
class LanguageFiles
{
	public function getGroupsInAllBundles()
	{
		$all_bundle_info = App::getApplicationBundleInfo();

		$phrasegroups = array();
		foreach ($all_bundle_info as $bundle => $bundle_info) {
			$groups = $this->getGroupsInBundle($bundle);
			if ($groups) {
				$phrasegroups[$bundle] = $groups;
			}
		}

		return $phrasegroups;
	}



	/**
	 * Get an array of language groups in a given bundle
	 *
	 * @param  $bundle
	 * @return array
	 */
	public function getGroupsInBundle($bundle)
	{
		$all_bundle_info = App::getApplicationBundleInfo();
		if (!isset($all_bundle_info[$bundle])) {
			return array();
		}

		$bundle_info = $all_bundle_info[$bundle];

		$dir = $bundle_info['path'] . '/Resources/language';
		if (!is_dir($dir)) {
			return array();
		}

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('*.php')->in($dir);

		$groups = array();
		foreach ($finder as $filepath) {

			// Groups are shortname_subname
			// Or if its the main core group of a bundle, simply shortname

			$name = str_replace($dir . '/', '', $filepath);
			$name = str_replace('.php', '', $name);

			if ($name != $bundle_info['shortname']) {
				$name = $bundle_info['shortname'] . '_' . $name;
			}

			$groups[] = $name;
		}
		sort($groups, \SORT_STRING);

		return $groups;
	}



	/**
	 * Get the filepath for a group
	 *
	 * @param  $groupname
	 * @return null|string
	 */
	public function getPathForGroup($groupname)
	{
		if (strpos($groupname, '_') !== false) {
			$bundle_shortname = explode('_', $groupname, 2);
		} else {
			$bundle_shortname = $groupname;
		}

		$bundle = App::getBundleFromShortname($bundle_shortname);

		$all_bundle_info = App::getApplicationBundleInfo();
		if (!isset($all_bundle_info[$bundle])) {
			return null;
		}

		$bundle_info = $all_bundle_info[$bundle];

		$path = $bundle_info['path'] . '/Resources/language/' . $groupname . '.php';

		return $path;
	}
}
