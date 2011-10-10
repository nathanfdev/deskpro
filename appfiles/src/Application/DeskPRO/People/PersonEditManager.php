<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Mail
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

use Doctrine\ORM\EntityManager;

use Orb\Util\Strings;
use Orb\Util\Util;

class PersonEditManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	public function deleteUser(Person $person)
	{
		$this->em->beginTransaction();

		try {
			$this->em->remove($person);
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}

	public function mergeUsers(Person $person, Person $other_person)
	{

	}
}
