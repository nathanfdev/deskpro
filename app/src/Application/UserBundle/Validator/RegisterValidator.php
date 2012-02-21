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

namespace Application\UserBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Validator\AbstractValidator;

class RegisterValidator extends AbstractValidator
{
	/**
	 * @var \Application\UserBundle\Form\Model\Register
	 */
	protected $register;

	protected function checkIsValid($register)
	{
		$this->register = $register;

		$validator = new \Orb\Validator\StringLength(array('min' => 2));
		if (!$validator->isValid($this->register->name)) {
			$this->addError('name.short');
		}

		if (!\Orb\Validator\StringEmail::isValueValid($this->register->email)) {
			$this->addError('email.invalid');
		} else {
			$check_exist = App::getDb()->fetchColumn("
				SELECT person_id
				FROM people_emails
				WHERE email = ?
			", array($this->register->email));
			if ($check_exist) {
				$this->addError('email.in_use');
			}
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 5));
		if (!$validator->isValid($this->register->password)) {
			$this->addError('password.short');
		} elseif ($this->register->password != $this->register->password2) {
			$this->addError('password.mismatch');
		}

		if ($this->errors) {
			return false;
		}

		return true;
	}
}
