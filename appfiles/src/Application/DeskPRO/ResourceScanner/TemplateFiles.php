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
 * The style system uses templates from the database first, and falls back onto the filesystem.
 * For us to show the user in an interface which templates can be editted we need a way to get
 * a list of templates in the fs, and be able to map them to files.
 */
class TemplateFiles
{
	public function getTempaltesInAllBundles()
	{
		$all_bundle_info = App::getApplicationBundleInfo();

		$templates = array();
		foreach ($all_bundle_info as $bundle => $bundle_info) {
			$tpls = $this->getTemplatesInBundle($bundle);
			if ($tpls) {
				$templates[$bundle] = $tpls;
			}
		}

		return $templates;
	}

	/**
	 * Get an array of templates in a given bundle
	 *
	 * @param  $bundle
	 * @return array
	 */
	public function getTemplatesInBundle($bundle)
	{
		$all_bundle_info = App::getApplicationBundleInfo();
		if (!isset($all_bundle_info[$bundle])) {
			return array();
		}

		$bundle_info = $all_bundle_info[$bundle];

		$dir = $bundle_info['path'] . '/Resources/views';

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('*.twig')->in($dir);

		$templates = array();
		foreach ($finder as $filepath) {

			$tplname = str_replace($dir . '/', ':', $filepath);
			$tplname = str_replace('/', ':', $tplname);
			if (substr_count($tplname, ':') < 2) {
				$tplname = ':' . $tplname; // for layouts that are in top dir, MyBundle::layout
			}
			$tplname = $bundle . $tplname;

			$templates[] = $tplname;
		}

		sort($templates, \SORT_STRING);

		return $templates;
	}



	/**
	 * Get the filepath for a template
	 *
	 * @param  $template
	 * @return null|string
	 */
	public function getPathForTemplate($template)
	{
		if (substr_count($template, ':') != 2) {
			return null;
		}

		list ($bundle, $section, $name) = explode(':', $template, 3);

		$all_bundle_info = App::getApplicationBundleInfo();
		if (!isset($all_bundle_info[$bundle])) {
			return null;
		}
		$bundle_info = $all_bundle_info[$bundle];

		$path = $bundle_info['path'] . '/Resources/views/';
		if ($section) {
			$path .= $section . '/';
		}
		$path .= $name;

		return $path;
	}
}
