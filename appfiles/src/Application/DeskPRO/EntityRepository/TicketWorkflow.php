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
	protected $_workflow_names = null;

	protected function _loadWorkflowNames()
	{
		if ($this->_workflow_names !== null) return;

		$db = App::getDb();
		$this->_workflow_names = $db->fetchAllKeyValue("
			SELECT id, title
			FROM ticket_workflows
			ORDER BY title ASC
		");
	}

	public function getWorkflowNames($for_ids = null)
	{
		$this->_loadWorkflowNames();

		if ($for_ids === null) {
			return $this->_workflow_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_workflow_names[$id])) {
				$ret[] = $this->_workflow_names[$id];
			}
		}

		return $ret;
	}
}