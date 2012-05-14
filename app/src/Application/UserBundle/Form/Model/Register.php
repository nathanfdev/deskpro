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
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmailValidating;

class Register
{
	public $name;
	public $email;
	public $password;
	public $password2;

	public $no_validation;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function save()
	{
		$this->em = App::getOrm();
		$this->em->getConnection()->beginTransaction();

		try {

			// Depending on how we got here,
			// the person might already exist based on an email
			// address (eg theyre fully registrering from some validation linke)
			$person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->email);

			if (!$person) {
				$person = Person::newRegularPerson();
			}

			$email_validating = null;
			if (!$person->findEmailAddress($this->email)) {
				if (!$this->no_validation && App::getSetting('core.email_validation')) {
					$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->email);
					if (!$email_validating) {
						$email_validating = new PersonEmailValidating();
						$email_validating->email = $this->email;
						$email_validating->person = $person;
					}

					$person->is_user = false;
				} else {
					$person->addEmailAddressString($this->email);
					$person->is_user = true;
				}
			}

			$person->name = $this->name;
			$person->setPassword($this->password);
			$this->em->persist($person);
			$this->em->flush();

			if ($email_validating) {
				$this->em->persist($email_validating);
				$this->em->flush();
			}

			$this->em->getConnection()->commit();

			if ($email_validating) {
				$tr = App::getTranslator();

				$message = App::getMailer()->createMessage();
				$message->setTo($email_validating->email, $this->name);
				$message->setTemplate('DeskPRO:emails_user:register-validate.html.twig', array(
					'vemail' => $email_validating
				));
				App::getMailer()->send($message);
			}

			return $person;
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}
	}
}
