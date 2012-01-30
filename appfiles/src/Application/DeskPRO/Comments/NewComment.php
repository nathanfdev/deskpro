<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Comments
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Comments;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;

use Orb\Util\Arrays;

use Symfony\Component\Form;

class NewComment implements \Application\DeskPRO\People\PersonContextInterface
{
	protected $class;
	protected $assignments;

	public $name = '';
	public $email = '';
	public $content = '';

	protected $person_context = null;

	public function __construct($class, Person $person, array $assignments)
	{
		$this->class = $class;
		$this->person_context = $person;
		$this->assignments = $assignments;
	}

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	public function save()
	{
		$obj = new $this->class();

		$validating = null;
		$person = null;

		App::getOrm()->beginTransaction();

		try {
			if ($this->person_context && !$this->person_context->isGuest()) {
				$person = $this->person_context;
			} else {
				$email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->email);

				if ($email) {
					$validating = 'existing';

					$person = $email->person;

					$email_validating = new PersonEmailValidating();
					$email_validating->email = $email->email;
					$email_validating->person = $person;
					App::getOrm()->persist($email_validating);

				} else {
					$validating = 'new';

					$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->email);

					if (!$email_validating) {
						$person = Person::newContactPerson();
						$person->name = $this->name;
						App::getOrm()->persist($person);

						$email_validating = new PersonEmailValidating();
						$email_validating->email = $this->email;
						$email_validating->person = $person;
						App::getOrm()->persist($email_validating);

					} else {
						$person = $email_validating->person;
					}
				}
			}

			$obj->person = $person;
			$obj->name = $person->name;
			if ($person->getPrimaryEmailAddress()) {
				$obj->email = $person->getPrimaryEmailAddress();
			} else if ($email_validating) {
				$obj->email = $email_validating->email;
			}

			$obj->validating = $validating;
			$obj->visitor = App::getSession()->getVisitor();
			$obj->content = htmlspecialchars($this->content);

			// TODO visibility based on setting
			$obj->status = 'visible';

			foreach ($this->assignments as $k => $v) {
				$obj[$k] = $v;
			}

			App::getOrm()->persist($obj);
			App::getOrm()->flush();

			if ($email_validating) {
				$email_validating->addValidatingContent($this->class, $obj->id);
				App::getOrm()->flush();
			}

			// Send confirmation email
			if ($email_validating) {
				App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($obj, $person, $email_validating, $email, $validating) {

					if ($validating == 'existing') {
						$email_to       = $email->email;
						$email_subject  = $tr->phrase('user_emails.subj_newcomment_validate');
					} elseif ($validating == 'new') {
						$email_to       = $email_validating->email;
						$email_subject  = $tr->phrase('user_emails.subj_newcomment_validate');
					}

					$vars = array(
						'comment' => $obj,

						'person' => $person,
						'email_validating' => $email_validating,
						'email' => $email,
						'validating' => $validating,
					);
					$email_body = App::get('templating')->render('DeskPRO:emails_user:comment-new.html.twig', $vars);

					$message = App::getMailer()->createMessage();
					$message->setTo($email_to, $person->getDisplayName());
					$message->setSubject($email_subject);
					$message->setBody($email_body, 'text/html');
					$message->enableQueueHint();

					App::getMailer()->send($message);
				});
			}

			App::getOrm()->commit();

			return $obj;

		} catch (\Exception $e) {
			App::getOrm()->rollback();
			throw $e;
		}
	}
}
