<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Person extends EntityRepository
{
	protected $agent_names = null;

	/**
	 * @return array
	 */
	public function getAgentNames()
	{
		if ($this->agent_names !== null) return $this->agent_names;

		$db = App::getDb();
		$this->agent_names = $db->feetchAllKeyValue("
			SELECT id, CONCAT_WS(' ', first_name, last_name) AS full_name
			FROM people
			WHERE is_agent = 1
			ORDER BY full_name ASC
		");

		return $this->agent_names;
	}
}