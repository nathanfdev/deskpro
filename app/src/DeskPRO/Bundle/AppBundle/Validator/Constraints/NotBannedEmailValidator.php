<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\EntityRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class NotBannedEmailValidator.
 */
class NotBannedEmailValidator extends ConstraintValidator
{
    /**
     * @var EntityRepository\BanEmail
     */
    private $ban_email_repository;

    /**
     * Constructor.
     *
     * @param EntityRepository\BanEmail $ban_email_repository
     */
    public function __construct(EntityRepository\BanEmail $ban_email_repository)
    {
        $this->ban_email_repository = $ban_email_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotBannedEmail) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\NotBannedEmail');
        }

        $emails        = (array) $value;
        $banned_emails = array_filter($emails, function ($email) {
            return $this->ban_email_repository->isEmailBanned($email);
        });

        foreach ($banned_emails as $email) {
            $this->context->addViolation($constraint->message, [
                'email' => $email,
            ]);
        }
    }
}
