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

class PersonEmail extends \Doctrine\ORM\EntityRepository
{
	public function getEmail($email_address)
	{
		return $this->getEntityManager()->createQuery("
			SELECT e
			FROM DeskPRO:PersonEmail e
			WHERE e.email = ?1
		")->setParameters(array(1=> $email_address))->setMaxResults(1)->getOneOrNullResult();
	}
}
