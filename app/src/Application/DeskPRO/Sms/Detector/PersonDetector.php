<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Sms\Detector;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Doctrine\ORM\EntityManager;

class PersonDetector
{
	/**
	 * @var EntityManager
	 */
	private $em;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 *  use number to find a person that sent us an sms
	 *
	 * @param string $from_number
	 * @return \Application\DeskPRO\Entity\Person|null
	 */
	public function detectWithFromNumber($from_number = null)
	{
		return $this->em->getRepository('DeskPRO:Person')->findOneByPhoneNumber($from_number);
	}


	/**
	 * creates a person with the given phone number
	 *
	 * @param string $from_number
	 * @return Person
	 * @throws \Doctrine\DBAL\ConnectionException
	 */
	public function createPersonWithNumber($from_number)
	{
		$this->em->getConnection()->beginTransaction();

		$person = $this->detectWithFromNumber($from_number);

		if ($person) {
			$this->em->getConnection()->commit();

			return $person;
		}

		$person                  = Person::newContactPerson();
		$person->creation_system = 'gateway.person';
		$person->is_confirmed    = true;
		$from_number = new PhoneNumber($from_number);
		$person->setPrimaryPhoneNumber($from_number);

		if (App::getSetting('core.agent_validation')) {
			$person->is_agent_confirmed = false;
		}

		$this->em->persist($person);
		$this->em->persist($from_number);
		$this->em->flush();

		$this->em->getConnection()->commit();

		return $person;
	}
}
