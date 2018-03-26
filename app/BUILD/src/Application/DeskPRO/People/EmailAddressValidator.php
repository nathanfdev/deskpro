<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\BanEmail;
use Orb\Validator\StringEmail;

class EmailAddressValidator
{
    /**
     * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
     */
    private $account_manager;

    /**
     * @var \Application\DeskPRO\EntityRepository\BanEmail
     */
    private $ban_repos;

    /**
     * @var \Orb\Validator\StringEmail
     */
    private $format_validator;

    /**
     * @param EmailAccountManager $account_manager
     * @param BanEmail            $ban_repos
     */
    public function __construct(EmailAccountManager $account_manager, BanEmail $ban_repos)
    {
        $this->account_manager  = $account_manager;
        $this->ban_repos        = $ban_repos;
        $this->format_validator = new StringEmail();
    }

    /**
     * Check if a user inputted email address is valid.
     *
     * @param string $email
     *
     * @return bool
     */
    public function isValidUserEmail($email)
    {
        if (!$email) {
            return false;
        }
        if (!$this->format_validator->isValid($email)) {
            return false;
        }
        if ($this->account_manager->findAccountForEmailAddress($email)) {
            return false;
        }
        if ($this->ban_repos->isEmailBanned($email)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a person has any banned emails.
     *
     * @param Person $person
     *
     * @return bool
     */
    public function personHasBannedEmail(Person $person)
    {
        foreach ($person->emails as $email) {
            if ($this->ban_repos->isEmailBanned($email->email)) {
                return $email->email;
            }
        }

        return false;
    }
}
