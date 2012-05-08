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

namespace Application\DeskPRO\Feedback;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;
use Application\DeskPRO\Entity\Visitor;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Rating;

/**
 * New feedback acts as the processor and domain object for a newfeedback form
 */
class NewFeedback implements \Application\DeskPRO\People\PersonContextInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public $require_login = false;
	public $person_name = '';
	public $person_email = '';
	public $category_id = 0;
	public $title = '';
	public $content = '';
	public $attach_blobs = array();

	public function __construct(Visitor $visitor = null, Person $person = null)
	{
		$this->em = App::getOrm();

		$this->visitor = $visitor;

		if ($person && !$person->isGuest()) {
			$this->person_name = $person->name;
			if ($person->primary_email) {
				$this->person_email = $person->primary_email_address;
			}
		}

		if ($visitor && $visitor->name && !$this->person_name) {
			$this->person_name = $visitor->name;
		}
		if ($visitor && $visitor->email && !$this->person_email) {
			$this->person_email = $visitor->email;
		}
	}

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	public function getPersonContext()
	{
		return $this->person_context;
	}


	/**
	 * @param array $attach_ids
	 */
	public function setAttachBlobs(array $attach_auth_ids)
	{
		foreach ($attach_auth_ids as $auth_id) {
			$blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthId($auth_id);
			if ($blob) {
				$this->attach_blobs[] = $blob;
			}
		}
	}


	public function save()
	{
		$this->em->getConnection()->beginTransaction();

		try {

			#------------------------------
			# Handle the person first
			#------------------------------

			$status = 'visible';
			$validating = null;

			$person = null;
			$email = null;
			$email_validating = null;

			if ($this->person_context->isGuest()) {

				$email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($this->person_email);
				$email_validating = $this->em->getRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person_email);

				// Email already exists on an account
				// Means use the same person, but depending on the setting we
				// might require the user to log in (in which case the ticket is a temp ticket for a bit)
				if ($email) {
					if (App::getSetting('core.existing_account_login')) {
						$person = $email->person;
						$person->name = $this->person_name;
						$this->require_login = true;

					} else {
						$person = $email->person;
						$person->name = $this->person_name;
					}

					$email_validating = null;

				// Email doesnt exist,
				// Might already be validating, or we might require validation based on the setting
				} elseif ($email_validating || App::getSetting('core.email_validation')) {
					$validating = 'new';
					if (!$email_validating) {
						$person = Person::newContactPerson();
						$person->name = $this->person_name;
						$this->em->persist($person);

						$email_validating = new PersonEmailValidating();
						$email_validating->email = $this->person_email;
						$email_validating->person = $person;
						$this->em->persist($email_validating);

					} else {
						$person = $email_validating->person;
					}

				// If we get here, then its a new user and we dont require validation
				// Note a user isnt a "user" at this point, they cant log in etc,
				// no validation just means they dont need to validate to get their ticket reads
				} else {
					$person = Person::newContactPerson();
					$person->name = $this->person_name;
					$this->em->persist($person);

					$email = new PersonEmail();
					$email->email = $this->person_email;
					$email->person = $person;
					$person->addEmailAddress($email);
					$this->em->persist($email);

					$email_validating = null;
				}
			} else {
				$person = $this->person_context;

				if ($this->person_name) {
					$person->name = $this->person_name;
					$this->em->persist($person);
				}
			}

			$this->em->flush();

			$feedback = new Feedback();

			$feedback['title']        = $this->title;
			$feedback['content']      = $this->content;
			$feedback['category_id']  = $this->category_id;
			$feedback['status']       = Feedback::STATUS_NEW;
			$feedback['date_created'] = new \DateTime();
			$feedback['validating']   = $validating;
			$feedback['person']       = $person;

			if ($this->require_login) {
				$feedback->setStatusCode('hidden.temp');
			} elseif ($validating) {
				$feedback->setStatusCode('hidden.user_validating');
			} else {
				// Visible stuff always starts off as validating,
				//the meaing just changes based on setting. ie they could
				// be visible to end users or hidden. An agent always needs to approve or dismiss
				// it.
				$feedback->setStatusCode('hidden.validating');
			}

			$this->em->persist($feedback);

			foreach ($this->attach_blobs as $blob) {
				$attach = new \Application\DeskPRO\Entity\FeedbackAttachment();
				$attach->person   = $person;
				$attach->feedback = $feedback;
				$attach->blob     = $blob;

				$feedback->addAttachment($attach);
				$this->em->persist($attach);
			}

			$this->em->persist($feedback);
			$this->em->flush();

			$rating = Rating::create(1);
			$rating->person = $person;
			if ($this->visitor) {
				$rating->visitor = $this->visitor;
			}
			$feedback->addRating($rating);

			$this->em->persist($rating);
			$this->em->persist($feedback);

			$this->em->flush();

			if ($email_validating) {
				$email_validating->addValidatingContent('DeskPRO:Feedback', $feedback->id);
				$this->em->flush();
			}

			$this->em->commit();

			// Send confirmation email
			App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($feedback, $person, $email_validating, $email, $validating) {

				if ($validating == 'existing') {
					$email_to       = $email->email;
					$email_subject  = $tr->phrase('user.emails.subj_newfeedback_validate');
				} elseif ($validating == 'new') {
					$email_to       = $email_validating->email;
					$email_subject  = $tr->phrase('user.emails.subj_newfeedback_validate');
				} else {
					$email_to       = $person->primary_email_address;
					$email_subject  = $tr->phrase('user.emails.subj_newfeedback');
				}

				$vars = array(
					'feedback' => $feedback,
					'person' => $person,
					'email_validating' => $email_validating,
					'email' => $email,
					'validating' => $validating,
				);
				$email_body = App::get('templating')->render('DeskPRO:emails_user:feedback-new.html.twig', $vars);

				$message = App::getMailer()->createMessage();
				$message->setTo($email_to, $person->getDisplayName());
				$message->setSubject($email_subject);
				$message->setBody($email_body, 'text/html');
				$message->enableQueueHint();

				App::getMailer()->send($message);
			});

		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $feedback;
	}
}
