<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Util\PhoneNumbers;
use Orb\Validator\AbstractValidator;

class AgentProfileValidator extends AbstractValidator
{
    /**
     * @var \Application\AgentBundle\Form\Model\SettingsProfile
     */
    protected $profile;

    /**
     * @param \Application\AgentBundle\Form\Model\SettingsProfile $profile
     *
     * @return bool
     */
    protected function checkIsValid($profile)
    {
        $this->profile = $profile;

        if (!PhoneNumbers::looksEmpty($this->profile->primary_phone['number'])) {
            if (!PhoneNumbers::isValid($this->profile->primary_phone['number'])) {
                $this->addError('phone_number.invalid');
            }
        }

        $validator = new \Orb\Validator\StringLength(['min' => 3]);
        if (!$validator->isValid($this->profile->name)) {
            $this->addError('name.short');
        }

        if (!\Orb\Validator\StringEmail::isValueValid($this->profile->email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($this->profile->email)) {
            $this->addError('email.invalid');
        } else {
            $check_exist = App::getDb()->fetchColumn('
                SELECT person_id
                FROM people_emails
                WHERE email = ?
            ', [$this->profile->email]);
            if ($check_exist && $check_exist != $this->profile->getPerson()->getId()) {
                $this->addError('email.in_use');
            }
        }

        if ($this->profile->password) {
            /** @var \Application\DeskPRO\People\PasswordPolicyValidator $password_validator */
            $password_validator = App::$container->getSystemService('password_policy_validator');

            $error = null;
            if (!$password_validator->checkPassword($this->profile->password, $this->profile->getPerson(), $error)) {
                $this->addError('password.invalid');
                $this->addError('password.invalid.'.$error);
            } elseif ($this->profile->password != $this->profile->password2) {
                $this->addError('password.mismatch');
            }
        }

        if ($this->profile->new_emails) {
            foreach ($this->profile->new_emails as $new_email) {
                if (!\Orb\Validator\StringEmail::isValueValid($new_email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($new_email)) {
                    $this->addError('email.invalid');
                } else {
                    $check_exist = App::getDb()->fetchColumn('
                        SELECT person_id
                        FROM people_emails
                        WHERE email = ?
                    ', [$new_email]);
                    if ($check_exist && $check_exist != $this->profile->getPerson()->getId()) {
                        $this->addError('email.in_use');
                    }
                }
            }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
