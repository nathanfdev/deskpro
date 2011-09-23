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
use Application\DeskPRO\Entity\Person as PersonEntity;

use \Doctrine\ORM\EntityRepository;

class PersonNote extends EntityRepository
{
	public function getNotesForPerson(PersonEntity $person)
	{
		return $this->getEntityManager()->createQuery("
			SELECT n
			FROM DeskPRO:PersonNote n
			WHERE n.person = ?1
			ORDER BY n.id DESC
		")->execute(array(1=> $person));
	}
}
