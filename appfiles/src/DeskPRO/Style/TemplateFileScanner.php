<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Style;
use Symfony\Components\DependencyInjection\Container;

/**
 * The style system uses templates from the database first, and falls back onto the filesystem.
 * For us to show the user in an interface which templates can be editted we need a way to get
 * a list of templates in the fs, and be able to map them to files.
 */
class TemplateFileScanner
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
	 *     'ExampleBundle:Example:index'   => '/src/Application/ExampleBundle/views/Example/index.twig',
	 *     'WhateverBundle::layout'        => '/src/Bundle/WhateverBundle/views/layout.twig',
	 * )
	 * </code>
	 *
	 * @param string $bundle
	 * @return array
	 */
	public function getBundleTemplates($bundle)
	{
		$bundle_dir = null;
		$bundle_ns = null;

		foreach ($this->bundle_dirs as $ns => $dir) {
			if (strpos($bundle, $ns) === 0) {
				$bundle_dir = $dir . '/' . str_replace('\\', '/', str_replace($ns, '', $bundle));
				$bundle_ns = $ns;
				break;
			}
		}

		if (!$bundle_dir) {
			return array();
		}

		$view_dir = $bundle_dir . '/Resources/views';

		$finder = new \Symfony\Components\Finder\Finder();
		$finder->files()->name('*.twig')->in($view_dir);

		$templates = array();
		foreach ($finder as $file) {
			// /somepath/SomeBundle/Resources/views/Something/index.twig
			// -> SomeBundle:Something:index
			$tplname = str_replace($view_dir . '/', ':', $filepath);
			$tplname = str_replace('.twig', '', $tplname);
			$tplname = str_replace('/', ':', $tplname);
			$tplname = $bundle . ':' . $tplname;

			$templates[$tplname] = $file;
		}

		return $templats;
	}


	
	/**
	 * Get templates for all known bundles.
	 *
	 * @return array
	 */
	public function getTemplates()
	{
		$templates = array();

		foreach ($this->bundles as $bundle) {
			$templates[$bundle] = $this->getBundleTemplates($bundle);
		}

		return $templates;
	}
}
