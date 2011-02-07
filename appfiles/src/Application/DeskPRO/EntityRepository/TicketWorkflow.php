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

class TicketWorkflow extends EntityRepository
{
	protected $workflow_names = null;

	/**
	 * @return array
	 */
	public function getWorkflowNames()
	{
		if ($this->workflow_names !== null) return $this->workflow_names;

		$db = App::getDb();
		$this->workflow_names = $db->fetchAllKeyValue("
			SELECT id, title
			FROM ticket_workflows
			ORDER BY title ASC
		");

		return $this->workflow_names;
	}
}