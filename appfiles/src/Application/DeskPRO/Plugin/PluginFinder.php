<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Plugin;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;

use Symfony\Component\Finder\Finder;

/**
 * This finds plugins that exist in the DeskPRO file structure
 */
class PluginFinder
{
	protected $base_path;
	protected $max_depth = 2;

	protected $found = null;

	public function __construct($base_path = null, $max_depth = 2)
	{
		if ($base_path === null) {
			$base_path = DP_ROOT.'/plugins';
		}

		$this->base_path = $base_path;
		$this->max_depth = $max_depth;
	}

	
	/**
	 * Find all available plugins
	 *
	 * @return array
	 */
	public function findPlugins()
	{
		if ($this->found !== null) return $this->found;

		$finder = new Finder();
		$finder->files()
			   ->depth('< ' . $this->max_depth)
			   ->name('PluginPackage.php')
			   ->in($this->base_path);

		//SplFileInfo
		foreach ($finder as $file) {
			require_once($file->getRealPath());
			$classname = $this->getClassnameFromFile($file->getRealPath());

			$this->found[$classname::getName()] = array(
				'class'           => $classname,
				'class_file'      => str_replace($this->base_path, '', $file->getRealPath()),
				'name'            => $classname::getName(),
				'title'           => $classname::getTitle(),
				'description'     => $classname::getDescription(),
				'version'         => $classname::getVersion()
			);
		}

		return $this->found;
	}

	
	/**
	 * Get info of one speciifc plugin
	 */
	public function getPluginInfo($name)
	{
		$this->findPlugins();
		if (!isset($this->found[$name])) return null;

		return $this->found[$name];
	}

	
	/**
	 * Get the classname for a file given the standard naming convention
	 *
	 * @param  $filename
	 * @return mixed
	 */
	public function getClassnameFromFile($filename)
	{
		$classname = str_replace($this->base_path . '/', '', $filename);
		$classname = str_replace(DIRECTORY_SEPARATOR, '\\', $classname);
		$classname = \preg_replace('#\.php$#', '', $classname);

		return $classname;
	}
}