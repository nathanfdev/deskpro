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

		$this->plugins = $em->createQuery("
			SELECT p
			FROM DeskPRO:Plugin p INDEX BY p.id
			WHERE p.is_enabled = true
		");
	}

	public function hasPlugin($plugin_id)
	{
		return isset($this->plugins[$plugin_id]);
	}

	public function getResourcesPath($plugin_id)
	{
		if (!isset($this->plugins[$plugin_id])) {
			return null;
		}

		return $this->plugins[$plugin_id]['resources_path'];
	}
}