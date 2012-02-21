<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function save()
	{
		$this->em = App::getOrm();
		$this->em->getConnection()->beginTransaction();

		try {
			$person = Person::newRegularPerson();

			$email_validating = null;
			if (App::getSetting('core.email_validation')) {
				$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->email);
				if (!$email_validating) {
					$email_validating = new PersonEmailValidating();
					$email_validating->email = $this->person->email;
					$email_validating->person = $person;
				}

				$person->is_user = false;
			} else {
				$person->addEmailAddressString($this->email);
				$person->is_user = true;
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



				$email_body = App::get('templating')->render('DeskPRO:emails_user:register-validate.html.twig', array(
					'vemail' => $email_validating
				));

				$message = App::getMailer()->createMessage();
				$message->setTo($email_validating->email, $this->name);
				$message->setSubject('Validate your email address');
				$message->setBody($email_body, 'text/html');
				App::getMailer()->send($message);
			}

			return $person;
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}
	}
}
