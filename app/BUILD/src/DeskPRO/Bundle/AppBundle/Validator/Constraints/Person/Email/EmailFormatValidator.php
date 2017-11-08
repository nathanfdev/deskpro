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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Override basic symfony email validator to apply both strict and non-strict checks to make sure the email has correct format.
 */
class EmailFormatValidator extends \Symfony\Component\Validator\Constraints\EmailValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Email) {
            throw new UnexpectedTypeException($constraint, Email::class);
        }

        // check email with strict=false to apply basic .+\@\S+\.\S+ regexp
        $hasViolations = false;

        if ($constraint->strict) {
            $nonStrictConstraint         = clone $constraint;
            $nonStrictConstraint->strict = false;

            parent::validate($value, $nonStrictConstraint);

            /** @var ConstraintViolation $violation */
            foreach ($this->context->getViolations() as $violation) {
                if ($violation->getPropertyPath() === $this->context->getPropertyPath()) {
                    $hasViolations = true;
                }
            }
        }

        // check with original constraint
        if (!$hasViolations) {
            parent::validate($value, $constraint);
        }
    }
}
