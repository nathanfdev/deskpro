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
class PluginManager
{
	protected $em;
	protected $plugins;

	public function __construct($em)
	{
		$this->em = $em;
	}

	protected function _initPlugins()
	{
		if ($this->plugins !== null) return;
		
		$this->plugins = $this->em->createQuery("
			SELECT p
			FROM DeskPRO:Plugin p INDEX BY p.id
		")->execute();
	}

	public function addPlugin($plugin)
	{
		$this->_initPlugins();
		$this->plugins[$plugin['id']] = $plugin;
	}

	public function hasPlugin($plugin_id)
	{
		$this->_initPlugins();
		return isset($this->plugins[$plugin_id]);
	}

	public function getResourcesPath($plugin_id)
	{
		$this->_initPlugins();
		if (!isset($this->plugins[$plugin_id])) {
			return null;
		}

		return $this->plugins[$plugin_id]['resources_path'];
	}
}