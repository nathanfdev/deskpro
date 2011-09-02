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
use Application\DeskPRO\Entity\Organization as OrganizationEntity;

use \Doctrine\ORM\EntityRepository;

class OrganizationNote extends EntityRepository
{
	public function getNotesForOrganization(OrganizationEntity $org)
	{
		return $this->getEntityManager()->createQuery("
			SELECT n
			FROM DeskPRO:OrganizationNote n
			WHERE n.organization = ?1
			ORDER BY n.id DESC
		")->execute(array(1=> $org));
	}
}
