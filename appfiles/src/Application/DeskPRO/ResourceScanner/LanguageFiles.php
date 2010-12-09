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
use Symfony\Component\DependencyInjection\Container;
use Orb\Util\Arrays;

/**
 * Scans the filesystem for language files
 */
class LanguageFiles
{
	protected $bundle_dirs = array();
	protected $bundles = array();

	public function __construct(Container $container)
	{
		$this->bundle_dirs = $container->getParameter('kernel.bundle_dirs');
		$bundles = $container->getParameter('kernel.bundles');

		// Filter out those we dont want
		$filter_out = array('Symfony\\');
		foreach ($bundles as $k => $v) {
			$add = true;
			foreach ($filter_out as $str) {
				if (strpos($v, $str) === 0) {
					$add = false;
					break;
				}
			}

			if ($add) {
				$this->bundles[] = $v;
			}
		}
	}



	/**
	 * Scan a bundle directory for all language files, and return a map of groups
	 * and the corresponding file:
	 *
	 * <code>
	 * array(
	 *     'example'            => '/src/Application/ExampleBundle/language/example.php',
	 *     'whatever_core'      => '/src/Bundle/WhateverBundle/language/core.php',
	 * )
	 * </code>
	 *
	 * @param string $bundle
	 * @return array
	 */
	public function getBundleGroups($bundle)
	{
		$bundle_dir = null;
		$bundle_name = null;

		foreach ($this->bundle_dirs as $ns => $dir) {
			if (strpos($bundle, $ns) === 0) {
				$bundle_dir = $dir . str_replace('\\', '/', str_replace($ns, '', $bundle));
				// Remove the bundlename at the end cuz its duplciated
				$bundle_dir = substr($bundle_dir, 0, strrpos($bundle_dir, '/'));

				$bundle_name = substr($bundle_dir, strrpos($bundle_dir, '/')+1);
				$bundle_name = str_replace('Bundle', '', $bundle_name);
				$bundle_name = strtolower($bundle_name);
				break;
			}
		}

		$lang_dir = $bundle_dir . '/Resources/language';

		if (!$bundle_dir OR !is_dir($bundle_dir) OR !is_dir($lang_dir)) {
			return array();
		}

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('*.php')->in($lang_dir);

		$groups = array();
		foreach ($finder as $filepath) {

			$groupname = basename($filepath, '.php');
			if ($groupname != $bundle_name) {
				$groupname = $bundle_name . '_' . $groupname;
			}

			// /somepath/SomeBundle/Resources/language/whatever.php
			// -> some_whatever

			$groups[$groupname] = $filepath;
		}

		return $groups;
	}



	/**
	 * Get templates for all known bundles.
	 *
	 * @return array
	 */
	public function getGroups($nameonly = false)
	{
		$groups = array();

		foreach ($this->bundles as $bundle) {
			$groups[$bundle] = $this->getBundleGroups($bundle);
			if ($nameonly) {
				$groups[$bundle] = array_keys($groups[$bundle]);
			}
		}

		$groups = Arrays::removeFalsey($groups);

		return $groups;
	}



	public function getPhrasesInFile($file, $just_names = false)
	{
		$phrases = include($file);

		if ($just_names) {
			return array_keys($phrases);
		}

		return $phrases;
	}
}
