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

namespace Application\DeskPRO\Ideas;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;
use Application\DeskPRO\Entity\Visitor;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\Rating;

/**
 * New feedback acts as the processor and domain object for a newfeedback form
 */
class NewIdea implements \Application\DeskPRO\People\PersonContextInterface
{
	public $category_id = 0;
	public $title = '';
	public $content = '';

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public $person_name = '';
	public $person_email = '';

	public function __construct(Visitor $visitor = null, Person $person = null)
	{
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

	public function save()
	{
		App::getOrm()->beginTransaction();

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

				$email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->person_email);

				if ($email) {
					$validating = 'existing';

					$person = $email->person;

					$email_validating = new PersonEmailValidating();
					$email_validating->email = $email->email;
					$email_validating->person = $person;
					App::getOrm()->persist($email_validating);

				} else {
					$validating = 'new';

					$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person_email);

					if (!$email_validating) {
						$person = Entity\Person::newContactPerson();
						$person->name = $this->person_name;
						App::getOrm()->persist($person);

						$email_validating = new PersonEmailValidating();
						$email_validating->email = $this->person_email;
						$email_validating->person = $person;
						App::getOrm()->persist($email_validating);

					} else {
						$person = $email_validating->person;
					}
				}
			} else {
				$person = $this->person_context;

				if ($this->person_name) {
					$person->name = $this->person_name;
					App::getOrm()->persist($person);
				}
			}

			App::getOrm()->flush();

			$feedback = new Idea();

			$feedback['title']        = $this->title;
			$feedback['content']      = $this->content;
			$feedback['category_id']  = $this->category_id;
			$feedback['status']       = Idea::STATUS_NEW;
			$feedback['date_created'] = new \DateTime();
			$feedback['validating']   = $validating;

			if ($validating) {
				// TODO visibility based on setting
				$feedback->setStatusCode('hidden.validating');
			}

			App::getOrm()->persist($feedback);
			App::getOrm()->flush();

			$rating = Rating::create(1);
			$rating->person = $person;
			if ($this->visitor) {
				$rating->visitor = $this->visitor;
			}
			$feedback->addRating($rating);

			App::getOrm()->persist($rating);
			App::getOrm()->persist($feedback);

			App::getOrm()->flush();

			if ($email_validating) {
				$email_validating->addValidatingContent('DeskPRO:Idea', $feedback->id);
				App::getOrm()->flush();
			}

			App::getOrm()->commit();

			// Send confirmation email
			App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($feedback, $person, $email_validating, $email, $validating) {

				if ($validating == 'existing') {
					$email_to       = $email->email;
					$email_subject  = $tr->phrase('user_emails.subj_newfeedback_validate');
				} elseif ($validating == 'new') {
					$email_to       = $email_validating->email;
					$email_subject  = $tr->phrase('user_emails.subj_newfeedback_validate');
				} else {
					$email_to       = $person->primary_email_address;
					$email_subject  = $tr->phrase('user_emails.subj_newfeedback');
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
			App::getOrm()->rollback();
			throw $e;
		}

		return $feedback;
	}
}
