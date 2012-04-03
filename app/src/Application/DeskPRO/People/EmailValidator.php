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
*/

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

		try {
			if (!$exist_email) {
				$email = new PersonEmail();
				$email->email = $this->validating_email->email;
				$email->date_created = $this->validating_email->date_created;
				$email->date_validated = new \DateTime();
				$email->person = $this->person;

				$this->person->addEmailAddress($email);
				$this->em->persist($email);
			} else {
				$email = $exist_email;
			}

			$this->person->is_confirmed = true;
			$this->em->persist($this->person);
			$this->em->flush();

			// Find tickets with this email awaiting validation
			$this->ticket_ids = $this->em->getRepository('DeskPRO:Ticket')->getTicketIdsWithValidatingEmail($this->validating_email);
			if ($this->ticket_ids) {
				foreach ($this->ticket_ids as $ticket_id) {
					$ticket = $this->em->find('DeskPRO:Ticket', $ticket_id);
					$ticket->status = 'awaiting_agent';

					$ticket->person_email_validating = null;
					$ticket->person_email = $email;

					$this->em->persist($ticket);
					$this->em->flush();
				}
			}

			// Validate the attached objects
			foreach ($this->validating_email->validating_content as $validating_object) {
				list($entity_name, $entity_id) = $validating_object;

				if (strpos($entity_name, 'Application\\DeskPRO\\Entity\\') === 0) {
					$entity_name = str_replace('Application\\DeskPRO\\Entity\\', 'DeskPRO:', $entity_name);
				}

				switch ($entity_name) {
					case 'DeskPRO:Feedback':
						$feedback = App::findEntity('DeskPRO:Feedback', $entity_id);
						if (!$feedback) {
							break;
						}

						$feedback->validating = null;
						if ($feedback->status_code == 'hidden.validating') {
							$feedback->status = 'visible';
						}

						App::getOrm()->transactional(function ($em) use ($feedback) {
							$em->persist($feedback);
							$em->flush();
						});

						break;

					case 'DeskPRO:ArticleComment':
					case 'DeskPRO:DownloadComment':
					case 'DeskPRO:FeedbackComment':
					case 'DeskPRO:NewsComment':
						$comment = App::findEntity($entity_name, $entity_id);
						if (!$comment) {
							break;
						}

						$comment->validating = null;
						if ($comment->status == 'validating') {
							$comment->status = 'visible';
						}

						App::getOrm()->transactional(function ($em) use ($comment) {
							$em->persist($comment);
							$em->flush();
						});
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

	public function getValidatingEmail()
	{
		return $this->validating_email;
	}
}
