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
use Application\DeskPRO\Entity\Organization as OrganizationEntity;

class PersonActivity extends \Doctrine\ORM\EntityRepository
{
	public function getForPerson(PersonEntity $person, $max = 30, $offset = 0)
	{
		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:PersonActivity a
			WHERE a.person = ?1
			ORDER BY a.id DESC
		")->setMaxResults($max)->setFirstResult($offset)->execute(array(1=>$person));
	}

	public function getForOrganization(OrganizationEntity $org, $max = 30, $offset = 0)
	{
		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:PersonActivity a
			LEFT JOIN a.person p
			WHERE p.organization = ?1
			ORDER BY a.id DESC
		")->setMaxResults($max)->setFirstResult($offset)->execute(array(1=>$org));
	}
}
