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
 * @category Entities
 */

namespace Application\DeskPRO\People\Agents;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class AgentDelete
{
	/** @var \Application\DeskPRO\Entity\Person  */
	private $agent;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;


	/**
	 * @param Person $agent
	 * @param EntityManager $em
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Person $agent, EntityManager $em)
	{
		if (!$agent->is_agent) {
			throw new \InvalidArgumentException();
		}

		$this->agent  = $agent;
		$this->em     = $em;
		$this->db     = $em->getConnection();
	}


	/**
	 * Makes the agent account a user account instead
	 */
	public function deleteToUser()
	{
		$this->agent->is_agent              = false;
		$this->agent->can_agent             = false;
		$this->agent->can_admin             = false;
		$this->agent->can_billing           = false;
		$this->agent->can_reports           = false;
		$this->agent->was_agent             = true;
		$this->agent->override_display_name = ''; // only settable for agents currently
		$this->em->persist($this->agent);

		$this->db->beginTransaction();
		try {

			$this->em->flush();

			// Specific department permissions are agent-only feature, remove those
			// Users get them from their usergroups
			$this->db->executeUpdate("
				DELETE FROM department_permissions
				WHERE person_id = ?
			", array($this->agent->id));

			// Agent groups
			$agent_groups = $this->em->getRepository('DeskPRO:Usergroup')->getAgentUsergroups();
			if ($agent_groups) {
				$agent_group_ids = Arrays::flattenToIndex($agent_groups, 'id');
				$this->db->executeUpdate("
					DELETE FROM person2usergroups
					WHERE person_id = ? AND usergroup_id IN (?)
				", array($this->agent->id, $agent_group_ids), array(\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY));
			}

			// Assigned tickets
			$this->db->executeUpdate("
				UPDATE tickets SET agent_id = NULL
				WHERE agent_id = ?
			", array($this->agent->id));
			$this->db->executeUpdate("
				UPDATE tickets_search_active SET agent_id = NULL
				WHERE agent_id = ?
			", array($this->agent->id));

			// Filters
			$this->db->executeUpdate("
				DELETE FROM ticket_filters
				WHERE person_id = ?
			", array($this->agent->id));

			// Subscriptions
			$this->db->executeUpdate("
				DELETE FROM ticket_filter_subscriptions
				WHERE person_id = ?
			", array($this->agent->id));

			// Agent team
			$this->db->delete('agent_team_members', array('person_id' => $this->agent->getId()));

			// Permission overrides
			$this->db->delete('permissions', array('person_id' => $this->agent->getId()));

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return true;
	}


	/**
	 * Marks the agent account as deleted
	 */
	public function softDelete()
	{
		$this->agent->is_deleted = true;
		$this->agent->can_admin  = false; // to be safe

		// Remove their permissions
		$this->db->delete('department_permissions'     , array('person_id' => $this->agent->getId()));
		$this->db->delete('permissions'                , array('person_id' => $this->agent->getId()));
		$this->db->delete('agent_team_members'         , array('person_id' => $this->agent->getId()));
		$this->db->delete('ticket_filter_subscriptions', array('person_id' => $this->agent->getId()));

		// Any open tickets should be unassigned
		$this->db->executeUpdate("
			UPDATE tickets SET agent_id = NULL
			WHERE agent_id = ? AND status IN ('awaiting_agent')
		", array($this->agent->id));
		$this->db->executeUpdate("
			UPDATE tickets_search_active SET agent_id = NULL
			WHERE agent_id = ? AND status IN ('awaiting_agent')
		", array($this->agent->id));

		$this->em->persist($this->agent);
		$this->em->flush();

		return true;
	}
}