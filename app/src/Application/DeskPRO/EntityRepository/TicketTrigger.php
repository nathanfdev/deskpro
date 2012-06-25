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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Orb\Util\Numbers;
use Orb\Util\Arrays;

use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity\Person as PersonEntity;

class TicketTrigger extends EntityRepository
{
	/**
	 * Get all event-based triggers (that is, not time-based)
	 *
	 * @param bool $only_enabeld
	 * @param bool $include_sys
	 * @return array
	 */
	public function getEventTriggers($only_enabeld = true, $include_sys = true)
	{
		if ($include_sys) {
			$include_sys = '';
		} else {
			$include_sys = 'AND trig.sys_name IS NULL';
		}

		if ($only_enabeld) {
			$only_enabeld = 'AND trig.is_enabled = true';
		} else {
			$only_enabeld = '';
		}

		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE
				trig.event_trigger NOT LIKE 'time_%'
				$include_sys
				$only_enabeld
			ORDER BY trig.run_order ASC
		")->execute();

		return $triggers;
	}


	/**
	 * Get events grouped by their trigger type
	 *
	 * @return array
	 */
	public function getGroupedTriggers()
	{
		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			ORDER BY trig.run_order ASC
		")->execute();

		$grouped = array();

		foreach ($triggers as $tr) {
			$group = $tr->event_trigger;
			$sub_group = null;
			if (strpos($group, 'time_') === 0) {
				$sub_group = $group;
				$group = 'time';
			}

			if (!isset($grouped[$group])) {
				$grouped[$group] = array();
			}

			if ($sub_group) {
				if (!isset($grouped[$group][$sub_group])) {
					$grouped[$group][$sub_group] = array();
				}
				$grouped[$group][$sub_group][] = $tr;
			} else {
				$grouped[$group][] = $tr;
			}
		}

		return $grouped;
	}


	/**
	 * Get all time-based triggers (aka escalations)
	 *
	 * @param bool $only_enabeld
	 * @param bool $include_sys
	 * @return array
	 */
	public function getTimeTriggers($only_enabeld = true, $include_sys = true)
	{
		if ($include_sys) {
			$include_sys = '';
		} else {
			$include_sys = 'AND trig.sys_name IS NULL';
		}

		if ($only_enabeld) {
			$only_enabeld = 'AND trig.is_enabled = true';
		} else {
			$only_enabeld = '';
		}

		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE
				trig.event_trigger LIKE 'time_%'
				$include_sys
				$only_enabeld
			ORDER BY trig.run_order ASC
		")->execute();

		return $triggers;
	}


	/**
	 * Get all system triggers with a certain prefix, and index by the sysname
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function getSystemTriggers($prefix = null)
	{
		if ($prefix) {
			$triggers = $this->getEntityManager()->createQuery("
				SELECT trig
				FROM DeskPRO:TicketTrigger trig INDEX BY trig.sys_name
				WHERE trig.sys_name LIKE '{$prefix}.%'
				ORDER BY trig.run_order ASC
			")->execute();
		} else {
			$triggers = $this->getEntityManager()->createQuery("
				SELECT trig
				FROM DeskPRO:TicketTrigger trig INDEX BY trig.sys_name
				ORDER BY trig.run_order ASC
			")->execute();
		}

		return $triggers;
	}


	/**
	 * Find all triggers that should be run/tested for a given event type.
	 *
	 * @return array
	 */
	public function getTriggersForEvents(array $events)
	{
		if (!$events) {
			return array();
		}

		$db = App::getDb();
		$events = $db->quoteIn($events);

		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE
				trig.event_trigger IN ($events)
				AND trig.is_enabled = true
			ORDER BY trig.run_order ASC
		")->execute();

		return $triggers;
	}


	/**
	 * Search for triggers that force an email notification on an agent.
	 *
	 * @param \Application\DeskPRO\Entity\Person $agent
	 * @return array
	 */
	public function findTriggersForcingNotificationForAgent(PersonEntity $agent)
	{
		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE trig.is_enabled = true
		")->execute();

		$ret = array();

		$find_codes = array('agent.' . $agent->id);
		foreach ($agent->getHelper('Agent')->getTeamIds() as $tid) {
			$find_codes[] = 'agent_team.' . $tid;
		}

		foreach ($triggers as $tr) {
			foreach ($tr->actions as $action) {
				if ($action['type'] != 'add_agent_notify') {
					continue;
				}

				foreach ($find_codes as $code) {
					if (in_array($code, $action['options']['codes'])) {
						$ret[] = $tr;
						break;
					}
				}
			}
		}

		return $ret;
	}
}
