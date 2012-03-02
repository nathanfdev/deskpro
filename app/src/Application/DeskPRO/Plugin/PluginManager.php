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
 * @subpackage Addons
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
