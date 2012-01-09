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
use Application\DeskPRO\Entity\Person as PersonEntity;
use Orb\Util\Arrays;

class TicketFilterSubscription extends EntityRepository
{
	public function getForAgent(PersonEntity $person)
	{
		$results = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:TicketFilterSubscription s
			LEFT JOIN s.filter f
			WHERE s.person = ?1
		")->execute(array(1=> $person));

		$ret = array();

		foreach ($results as $s) {
			$ret[$s->filter->id] = $s;
		}

		return $ret;
	}

	/**
	 * Return an array of subscription info for all agents in $people, optionally only for $filters.
	 *
	 * Returned array structure:
	 * <code>
	 * array(
	 *     // agend id => TicketFilterSubscription[]
	 *     123 => array(
	 *         14 => TicketFilterSubscription['email_new', ...],
	 *         // filter id => TicketFilterSubscription
	 *     )
	 * )
	 * </code>
	 *
	 * @param array $people
	 * @param array $filters
	 * @return array
	 */
	public function getForAgents(array $people, array $filters = null)
	{
		$people_ids = array();
		foreach ($people as $p) {
			if (is_numeric($p)) {
				$people_ids[] = $p;
			} else {
				$people_ids[] = $p->id;
			}
		}
		$filter_ids = array();
		foreach ($filters as $f) {
			if (is_numeric($f)) {
				$filter_ids[] = $f;
			} else {
				$filter_ids[] = $f->id;
			}
		}

		$people_ids = Arrays::removeFalsey($people_ids);
		$filter_ids = Arrays::removeFalsey($filter_ids);

		if (!$people_ids) {
			return array();
		}

		$people_ids = implode(',', $people_ids);
		$filter_ids = implode(',', $filter_ids);

		if ($filter_ids) {
			$results = $this->getEntityManager()->createQuery("
				SELECT s
				FROM DeskPRO:TicketFilterSubscription s
				LEFT JOIN s.filter f
				LEFT JOIN s.person a
				WHERE s.person IN ($people_ids) AND s.filter IN ($filter_ids)
			")->execute();
		} else {
			$results = $this->getEntityManager()->createQuery("
				SELECT s
				FROM DeskPRO:TicketFilterSubscription s
				LEFT JOIN s.filter f
				LEFT JOIN s.person a
				WHERE s.person IN ($people_ids)
			")->execute();
		}

		$ret = array();

		foreach ($results as $s) {
			$agent_id = $s->person->id;
			$filter_id = $s->filter->id;

			if (!isset($ret[$agent_id])) $ret[$agent_id] = array();

			$ret[$agent_id][$filter_id] = $s;
		}

		return $ret;
	}
}
