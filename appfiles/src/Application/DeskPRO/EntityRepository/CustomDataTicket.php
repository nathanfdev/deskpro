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

class CustomDataTicket extends EntityRepository
{
	public function getDataForTicket(Entity\Ticket $ticket)
	{
		return $this->_em->createQuery("
			SELECT d
			FROM DeskPRO:CustomDataTicket d INDEX BY d.field_id
			WHERE d.ticket = ?1
		")->setParameter(1, $ticket)->execute();
	}
}
