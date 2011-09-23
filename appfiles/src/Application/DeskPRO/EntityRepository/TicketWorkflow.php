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

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class TicketWorkflow extends EntityRepository
{
	protected $_workflow_names = null;

	public function findByTitle($title)
	{
		try {
			$workflow = $this->getEntityManager()->createQuery("
				SELECT w
				FROM DeskPRO:TicketWorkflow w
				WHERE w.title LIKE ?1
			")->setParameter(1, "%$title%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $workflow;
	}

	protected function _loadWorkflowNames()
	{
		if ($this->_workflow_names !== null) return;

		if (($this->_workflow_names = App::getCache('common')->load('workflow_names')) === false) {

			$db = App::getDb();
			$this->_workflow_names = $db->fetchAllKeyValue("
				SELECT id, title
				FROM ticket_workflows
				ORDER BY display_order ASC
			");

			App::getCache('common')->save($this->_workflow_names, null, array('ticket_workflows'));
		}
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


	/**
	 * Count all workflows that exist
	 *
	 * @return int
	 */
	public function countAll()
	{
		return App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_workflows");
	}


	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('ticket_workflows'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
