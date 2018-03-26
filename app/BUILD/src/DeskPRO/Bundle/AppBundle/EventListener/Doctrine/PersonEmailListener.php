<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Entity\PersonEmail;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PersonEmailListener.
 */
class PersonEmailListener
{
    /**
     * @var EmailAccountManager
     */
    private $emailAccountManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param EmailAccountManager $emailAccountManager
     * @param ValidatorInterface  $validator
     */
    public function __construct(EmailAccountManager $emailAccountManager, ValidatorInterface $validator)
    {
        $this->emailAccountManager = $emailAccountManager;
        $this->validator           = $validator;
    }

    /**
     * @param PersonEmail $email
     */
    public function verifyEmailAddress(PersonEmail $email)
    {
        // Email address should be validated by the time we get here,
        // this is a failsafe check
        if (defined('DP_TESTS_RUNNING')) {
            return;
        }

        $emailAddress = $email->getEmail();

        if (!$emailAddress) {
            throw new \RuntimeException('Email address is empty');
        }
        if (!is_string($emailAddress)) {
            throw new \RuntimeException('Email address is not a string value');
        }

        // check if it has valid format
        $errors = $this->validator->validate($emailAddress, [new Assert\Email(['strict' => true])]);
        if (count($errors)) {
            throw new \RuntimeException("`{$emailAddress}`` is not valid email address");
        }

        // check if it's not an account email
        if ($this->emailAccountManager->findAccountForEmailAddress($emailAddress)) {
            throw new \RuntimeException("`{$emailAddress}`` is an a gateway account address");
        }
    }
}
