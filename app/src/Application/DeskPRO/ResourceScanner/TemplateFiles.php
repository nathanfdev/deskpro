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
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

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
