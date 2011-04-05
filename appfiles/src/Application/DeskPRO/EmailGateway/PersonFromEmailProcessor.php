<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;

/**
 * This finds a user based on the email sent, or creates a new user
 * from it.
 */
class PersonFromEmailProcessor
{
	/**
	 * When we have any email from a user, perform basic routines on the user its from.
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function passPerson(EmailAddress $from, Entity\Person $person)
	{
		if (!$person['first_name'] AND !$person['last_name']) {
			$from = $this->reader->getFromAddress();
			if ($from->getName()) {
				$person['name'] = $from->getName();
				App::getOrm()->persist($person);
			}
		}
	}



	/**
	 * Finds a person based on the From in the email address.
	 *
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findPerson(EmailAddress $from)
	{
		$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($from->getEmail());
		if ($person) {
			$this->passPerson($from, $person);
			return $person;
		}

		return null;
	}



	/**
	 * Creates a person based on the From email address.
	 *
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function createPerson(EmailAddress $from)
	{
		$person = Entity\Person::newRegularPerson();
		$person['name'] = $from->getName();

		App::getOrm()->persist($person);
		APp::getOrm()->flush();

		$email = new Entity\PersonEmail();
		$email['email'] = $from->getEmail();
		$person->addEmailAddress($email);

		App::getOrm()->persist($email);
		APp::getOrm()->flush();

		return $person;
	}
}