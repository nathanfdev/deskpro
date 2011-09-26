<?php

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;

/**
 * This works with emails waiting for validation to turn them into real
 * emails on a user, and processes other things that might need to happen
 * after that. For example, if the user has tickets awaiting validation
 * because of an email, then we need to process all of those too.
 */
class EmailValidator
{
	/**
	 * @var \Application\DeskPRO\Entity\PersonEmailValidating
	 */
	protected $validating_email;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $ticket_ids = array();

	/**
	 * @param int         $id        The validating email address to fetch
	 * @param null|string $auth_code Optionally verify this ID too
	 * @return \Application\DeskPRO\People\EmailValidator
	 */
	public static function createFromId($id, $auth_code = null)
	{
		$validating_email = App::findEntity('DeskPRO:PersonEmailValidating', $id);
		if (!$validating_email) {
			return null;
		}

		if ($auth_code !== null && $validating_email->auth != $auth_code) {
			return null;
		}

		return new self($validating_email);
	}

	public function __construct(PersonEmailValidating $validating_email)
	{
		$this->validating_email = $validating_email;
		$this->person = $validating_email->person;

		$this->em = App::getOrm();
		$this->db = $this->em->getConnection();
	}

	public function getPerson()
	{
		return $this->person;
	}

	/**
	 * Validate the email address and return the newly created PersonEmail
	 *
	 * @throws \Exception|\OutOfBoundsException
	 * @return \Application\DeskPRO\Entity\PersonEmail
	 */
	public function validate()
	{
		$this->em->getConnection()->beginTransaction();

		$exist_email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($this->validating_email->email);
		if ($exist_email) {
			throw new \OutOfBoundsException("Email already exists", 100);
		}

		try {

			$email = new PersonEmail();
			$email->email = $this->validating_email->email;
			$email->date_created = $this->validating_email->date_created;
			$email->date_validated = new \DateTime();
			$email->person = $this->person;

			$this->em->persist($email);

			$this->person->addEmailAddress($email);


			$this->em->persist($email);

			$this->em->flush();

			// Find tickets with this email awaiting validation
			$this->ticket_ids = $this->em->getRepository('DeskPRO:Ticket')->getTicketIdsWithValidatingEmail($this->validating_email);
			if ($this->ticket_ids) {
				foreach ($this->ticket_ids as $ticket_id) {
					$ticket = $this->em->find('DeskPRO:Ticket', $ticket_id);
					$ticket->status = 'open';

					$ticket->person_email_validating = null;
					$ticket->person_email = $email;

					$this->em->persist($ticket);
					$this->em->flush();
				}
			}

			$this->em->remove($this->validating_email);
			$this->em->flush();

			$this->em->getConnection()->commit();

			return $email;

		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}
	}

	public function getTicketIds()
	{
		return $this->ticket_ids;
	}
}
