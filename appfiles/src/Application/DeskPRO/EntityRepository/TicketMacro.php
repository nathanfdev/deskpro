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

class TicketMacro extends EntityRepository
{
	public function getMacrosForPerson(Entity\Person $person)
	{
		// TODO sort out permissions etc

		return $this->findAll();
	}
}