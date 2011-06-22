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
use \Application\DeskPRO\Entity;

class TicketFilter extends EntityRepository
{
	/**
	 * Gets an array of all global filters.
	 *
	 * This is mainly used in admin for listing.
	 *
	 * @return array
	 */
	public function getAllGlobalFilters()
	{
		$filters = $this->getEntityManager()->createQuery("
			SELECT q
			FROM DeskPRO:TicketFilter q INDEX BY q.id
			WHERE q.is_global = true
			ORDER BY q.title ASC
		")->execute();

		return $filters;
	}


	/**
	 * Gets an array of all team filters, grouped by agent team id.
	 *
	 * This is mainly used in admin for listing.
	 *
	 * @return array
	 */
	public function getAllTeamFilters()
	{
		$filters = $this->getEntityManager()->createQuery("
			SELECT q
			FROM DeskPRO:TicketFilter q INDEX BY q.id
			LEFT JOIN q.agent_team at
			WHERE q.agent_team IS NOT NULL
			ORDER BY at.name ASC, q.title ASC
		")->execute();

		$grouped_filters = array();

		foreach ($filters as $filter) {
			$team_id = $filter->agent_team['id'];

			if (!isset($grouped_filters[$team_id])) {
				$grouped_filters[$team_id] = array('team' => $filter->agent_team, 'filters' => array());
			}

			$grouped_filters[$team_id]['filters'][] = $filter;
		}

		return $grouped_filters;
	}


	/**
	 * Gets an array of all agent filters, grouped by agent id.
	 *
	 * This is mainly used in admin for listing.
	 *
	 * @return array
	 */
	public function getAllAgentFilters()
	{
		$filters = $this->getEntityManager()->createQuery("
			SELECT q
			FROM DeskPRO:TicketFilter q INDEX BY q.id
			LEFT JOIN q.person p
			WHERE q.agent_team IS NULL AND q.is_global = false
			ORDER BY p.name ASC, q.title ASC
		")->execute();

		$grouped_filters = array();

		foreach ($filters as $filter) {
			$agent_id = $filter->person['id'];

			if (!isset($grouped_filters[$agent_id])) {
				$grouped_filters[$agent_id] = array('person' => $filter->person, 'filters' => array());
			}

			$grouped_filters[$agent_id]['filters'][] = $filter;
		}

		return $grouped_filters;
	}


	/**
	 *
	 * @param  $type
	 * @return void
	 */
	public function getFiltersForType($type)
	{
		switch ($type) {
			case 'global':
				$filters = $this->getEntityManager()->createQuery("
					SELECT q
					FROM DeskPRO:TicketFilter q INDEX BY q.id
					WHERE q.is_global = true
					ORDER BY q.title ASC
				")->execute();
				break;

			case 'team':
				$filters = $this->getEntityManager()->createQuery("
					SELECT q
					FROM DeskPRO:TicketFilter q INDEX BY q.id
					WHERE q.is_global = true
					ORDER BY q.title ASC
				")->execute();
				break;
		}
	}

	/**
	 * Find all ticket filters that a person can see.
	 *
	 * @param mixed $person_id
	 * @return array
	 */
	public function getFiltersForPerson($person_id)
	{
		if ($person_id instanceof Person) {
			$person_id = $perosn_id['id'];
		}

		$filters = $this->getEntityManager()->createQuery("
			SELECT q
			FROM DeskPRO:TicketFilter q INDEX BY q.id
			WHERE q.person = ?1 OR q.is_global = true
			ORDER BY q.title ASC
		")->setParameter(1, $person_id)->execute();

		return $filters;
	}

	public function getTicketFilterFromVar($var)
	{
		$ticket_filter_id = null;

		if (is_int($var) OR ctype_digit($var)) {
			$ticket_filter_id = (int)$var;
		} elseif (\is_object($var)) {
			if ($var instanceof Entity\TicketFilter) {
				return $var;
			}
		} elseif (isset($var['ticket_filter'])) {
			return $var['ticket_filter'];
		}

		if ($ticket_filter_id) {
			return $this->find($ticket_filter_id);
		}

		return null;
	}
}