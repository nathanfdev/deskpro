<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;

/**
 * Scans the permtable template to extract the names of permissions so we can dynamically create
 * the "all" permission group.
 */
class AgentGroupPermScanner
{
	protected $path;

	/**
	 * @var array
	 */
	protected $perm_names = null;

	public function __construct($path = null)
	{
		if (!$path) {
			$path = DP_ROOT.'/src/Application/AdminBundle/Resources/views/Agents/edit-agent-permtable.html.twig';
		}

		$this->path = $path;
	}


	protected function load()
	{
		if ($this->perm_names !== null) return;
		$this->perm_names = array();

		$content = file_get_contents($this->path);
		$m = null;
		preg_match_all('#<!\-\-\s*PERMISSION:(.*?)\s*\-\->#',$content, $m, PREG_SET_ORDER);

		foreach ($m as $match) {
			$this->perm_names[] = $match[1];
		}
	}


	/**
	 * Get the names of all the permissions
	 *
	 * @return array
	 */
	public function getNames()
	{
		$this->load();
		return $this->perm_names;
	}
}
