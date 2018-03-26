<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Entity\PersonEmail;

/**
 * Class NotSystemEmailValidator.
 */
class NotSystemEmailValidator extends AbstractEmailValidator
{
    /**
     * @var EmailAccountManager
     */
    private $emailAccountManager;

    /**
     * Constructor.
     *
     * @param EmailAccountManager $email_account_manager
     */
    public function __construct(EmailAccountManager $email_account_manager)
    {
        $this->emailAccountManager = $email_account_manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function isValidEmail(PersonEmail $value)
    {
        return !$this->emailAccountManager->findAccountForEmailAddress($value->getEmail());
    }
}
