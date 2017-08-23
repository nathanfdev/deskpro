<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
