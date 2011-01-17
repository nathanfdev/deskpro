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
 * The style system uses templates from the database first, and falls back onto the filesystem.
 * For us to show the user in an interface which templates can be editted we need a way to get
 * a list of templates in the fs, and be able to map them to files.
 */
class TemplateFiles
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
	 * Scan a bundle directory for all templates, and return a map of template names
	 * and the corresponding file:
	 *
	 * <code>
	 * array(
	 *     'ExampleBundle:Example:index'   => '/src/Application/ExampleBundle/views/Example/index.twig.html',
	 *     'WhateverBundle::layout'        => '/src/Bundle/WhateverBundle/views/layout.twig.html',
	 * )
	 * </code>
	 *
	 * @param string $bundle
	 * @return array
	 */
	public function getBundleTemplates($bundle)
	{
		$bundle_dir = null;
		$bundle_name = null;

		foreach ($this->bundle_dirs as $ns => $dir) {
			if (strpos($bundle, $ns) === 0) {
				$bundle_dir = $dir . str_replace('\\', '/', str_replace($ns, '', $bundle));
				// Remove the bundlename at the end cuz its duplciated
				$bundle_dir = substr($bundle_dir, 0, strrpos($bundle_dir, '/'));

				$bundle_name = substr($bundle_dir, strrpos($bundle_dir, '/')+1);
				break;
			}
		}

		$view_dir = $bundle_dir . '/Resources/views';

		if (!$bundle_dir OR !is_dir($bundle_dir) OR !is_dir($view_dir)) {
			return array();
		}

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('*.twig.html')->in($view_dir);

		$templates = array();
		foreach ($finder as $filepath) {
			// /somepath/SomeBundle/Resources/views/Something/index.twig.html
			// -> SomeBundle:Something:index
			$tplname = str_replace($view_dir . '/', ':', $filepath);
			$tplname = str_replace('.twig.html', '', $tplname);
			$tplname = str_replace('/', ':', $tplname);
			if (substr_count($tplname, ':') < 2) {
				$tplname = ':' . $tplname; // for layouts that are in top dir, MyBundle::layout
			}
			$tplname = $bundle_name . $tplname;

			$templates[$tplname] = $filepath;
		}

		return $templates;
	}


	
	/**
	 * Get templates for all known bundles.
	 *
	 * @return array
	 */
	public function getTemplates($nameonly = false)
	{
		$templates = array();

		foreach ($this->bundles as $bundle) {
			$templates[$bundle] = $this->getBundleTemplates($bundle);
			if ($nameonly) {
				$templates[$bundle] = array_keys($templates[$bundle]);
			}
		}

		$templates = Arrays::removeFalsey($templates);

		return $templates;
	}
}
