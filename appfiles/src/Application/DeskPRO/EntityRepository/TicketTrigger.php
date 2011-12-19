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
use Orb\Util\Numbers;
use Orb\Util\Arrays;

use \Doctrine\ORM\EntityRepository;

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
			WHERE trig.sys_name IS NULL
		")->execute();

		$grouped = array();

		foreach ($triggers as $tr) {
			if (!isset($grouped[$tr->event_trigger])) {
				$grouped[$tr->event_trigger] = array();
			}

			$grouped[$tr->event_trigger][] = $tr;
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
		")->execute();

		return $triggers;
	}


	/**
	 * Get only triggers with urgency terms or actions
	 *
	 * @return array
	 */
	public function getUrgencyTriggers()
	{
		$triggers = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE trig.sys_name IS NULL AND trig.has_urgency = true
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
			")->execute();
		} else {
			$triggers = $this->getEntityManager()->createQuery("
				SELECT trig
				FROM DeskPRO:TicketTrigger trig INDEX BY trig.sys_name
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
			WHERE trig.event_trigger IN ($events)
			AND trig.is_enabled = true
		")->execute();

		return $triggers;
	}
}
