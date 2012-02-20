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

class PersonEmailValidating extends \Doctrine\ORM\EntityRepository
{
	public function getForPerson($person)
	{
		return $this->getEntityManager()->createQuery("
			SELECT e
			FROM DeskPRO:PersonEmailValidating e
			WHERE e.person = ?1
			GROUP BY e.email
			ORDER BY e.id DESC
		")->setParameters(array(1=> $person))->execute();
	}

	public function getEmail($email_address)
	{
		return $this->getEntityManager()->createQuery("
			SELECT e
			FROM DeskPRO:PersonEmailValidating e
			WHERE e.email = ?1
		")->setParameters(array(1=> $email_address))->setMaxResults(1)->getOneOrNullResult();
	}
}
