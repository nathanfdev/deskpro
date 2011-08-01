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
use \Application\DeskPRO\Entity;

use \Doctrine\ORM\EntityRepository;

class TicketLog extends EntityRepository
{
	public function getLogsForTicket(Entity\Ticket $ticket)
	{
		$query = $this->_em->createQuery("
			SELECT log
			FROM DeskPRO:TicketLog log INDEX BY log.id
			WHERE log.ticket = ?1
			ORDER BY log.id ASC
		")->setParameter(1, $ticket);

		return $query->execute();
	}
}