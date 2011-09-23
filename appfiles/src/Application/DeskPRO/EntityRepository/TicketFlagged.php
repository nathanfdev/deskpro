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
use Application\DeskPRO\Entity;

use \Doctrine\ORM\EntityRepository;

use Orb\Util\Arrays;

class TicketFlagged extends EntityRepository
{
	public function getFlagsForTickets($tickets, Entity\Person $person)
	{
		$ids = Arrays::flattenToIndex($tickets, 'id');

		if (!$ids) return array();

		return App::getDb()->fetchAllKeyValue("
			SELECT ticket_id, color
			FROM tickets_flagged
			WHERE ticket_id IN(" . implode(',', $ids) . ") AND person_id = ?
		", array($person['id']));
	}

	public function getCountsForPerson(Entity\Person $person)
	{
		return App::getDb()->fetchAllKeyValue("
			SELECT color, COUNT(*)
			FROM tickets_flagged
			WHERE person_id = ?
			GROUP BY color
		", array($person['id']));
	}
}
