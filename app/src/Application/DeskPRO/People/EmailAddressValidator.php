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
     * @param  string $email
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
     * Check if a person has any banned emails
     *
     * @param  Person $person
     * @return bool
     */
    public function personHasBannedEmail(Person $person)
    {
        foreach ($person->emails as $email) {
            if ($this->ban_repos->isEmailBanned($email->email)) {
                return true;
            }
        }

        return false;
    }
}
