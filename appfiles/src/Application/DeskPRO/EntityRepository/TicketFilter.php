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