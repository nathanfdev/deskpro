<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class TicketPriority extends EntityRepository
{
	protected $priority_names = null;

	/**
	 * @return array
	 */
	public function getPriorityNames()
	{
		if ($this->priority_names !== null) return $this->priority_names;

		$db = App::getDb();
		$this->priority_names = $db->feetchAllKeyValue("
			SELECT id, title
			FROM ticket_priorities
			ORDER BY priority ASC
		");

		return $this->priority_names;
	}
}