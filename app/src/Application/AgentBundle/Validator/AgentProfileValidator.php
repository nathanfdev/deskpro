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
     * @param  \Application\AgentBundle\Form\Model\SettingsProfile $profile
     * @return bool
     */
    protected function checkIsValid($profile)
    {
        $this->profile = $profile;

        if (!PhoneNumbers::looksEmpty($this->profile->primary_phone_number_text)) {
            if (!PhoneNumbers::isValid($this->profile->primary_phone_number_text)) {
                $this->addError('phone_number.invalid');
            }
        }

        $validator = new \Orb\Validator\StringLength(array('min' => 3));
        if (!$validator->isValid($this->profile->name)) {
            $this->addError('name.short');
        }

        if (!\Orb\Validator\StringEmail::isValueValid($this->profile->email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($this->profile->email)) {
            $this->addError('email.invalid');
        } else {
            $check_exist = App::getDb()->fetchColumn("
                SELECT person_id
                FROM people_emails
                WHERE email = ?
            ", array($this->profile->email));
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
                $this->addError('password.invalid.' . $error);
            } elseif ($this->profile->password != $this->profile->password2) {
                $this->addError('password.mismatch');
            }
        }

        if ($this->profile->new_emails) {
            foreach ($this->profile->new_emails as $new_email) {
                if (!\Orb\Validator\StringEmail::isValueValid($new_email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($new_email)) {
                    $this->addError('email.invalid');
                } else {
                    $check_exist = App::getDb()->fetchColumn("
                        SELECT person_id
                        FROM people_emails
                        WHERE email = ?
                    ", array($new_email));
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
