<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class Register implements \ArrayAccess
{
	/** @var array  */
	protected static $prop_names = array(
		'name' => 1, 'email' => 1, 'password' => 1, 'password2' => 1,
		'language_id' => 1, 'no_validation' => 1, 'custom_fields' => 1,
	);

	/** @var string */
	public $name;
	/** @var string */
	public $email;
	/** @var string */
	public $password;
	/** @var string */
	public $password2;
	/** @var int */
	public $language_id = 1;

	/** @var bool */
	public $no_validation;
	/** @var array */
	public $custom_fields = array();

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefPerson[]
	 */
	protected $_custom_fields;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function save()
	{
		// Depending on how we got here,
		// the person might already exist based on an email
		// address (eg theyre fully registrering from some validation linke)
		$person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->email);

		if ($person) {
			$person->addPropertyChangedListener(App::getOrm()->getUnitOfWork());
		}

		if (!$person) {
			$person = Person::newRegularPerson();
		}

		if ($this->language_id && $lang = App::getDataService('Language')->get($this->language_id)) {
			$person->language = $lang;
		}

		$this->em = App::getOrm();
		$this->em->getConnection()->beginTransaction();

		try {

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
					$person->is_confirmed = false;
				} else {
					$person->addEmailAddressString($this->email);
					$person->is_user = true;
				}
			}

			if (App::getSetting('core.agent_validation')) {
				$person->is_agent_confirmed = false;
			}

			$person->name = $this->name;
			$person->setPassword($this->password);
			$this->em->persist($person);
			$this->em->flush();

			if ($this->custom_fields) {
				App::getSystemService('PersonFieldsManager')->saveFormToObject($this->custom_fields, $person);
			}

			if ($email_validating) {
				$this->em->persist($email_validating);
				$this->em->flush();
			}

			$this->em->getConnection()->commit();

			if ($email_validating) {
				$message = App::getMailer()->createMessage();
				$message->setTo($email_validating->email, $this->name);
				$message->setTemplate('DeskPRO:emails_user:register-validate.html.twig', array(
					'vemail' => $email_validating
				));
				App::getMailer()->send($message);
			} else {
				$message = App::getMailer()->createMessage();
				$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
				$message->setTemplate('DeskPRO:emails_user:register-welcome.html.twig', array(
					'person' => $person
				));
				App::getMailer()->send($message);
			}

			$send_notify = new \Application\DeskPRO\Notifications\NewRegistrationNotification($person);
			$send_notify->send();

			return $person;
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}
	}

	/**
	 * @param array $custom_fields
	 */
	public function setCustomFields(array $custom_fields)
	{
		$this->_custom_fields = $custom_fields;
	}

	public function offsetExists($offset)        { return (isset(self::$prop_names[$offset]) && isset($this->$offset)); }
    public function offsetGet($offset)           { if (isset(self::$prop_names[$offset])) return $this->$offset; }
    public function offsetSet($offset, $value)   { if (isset(self::$prop_names[$offset])) $this->$offset = $value; }
    public function offsetUnset($offset)         { if (isset(self::$prop_names[$offset])) $this->$offset = null; }
}
