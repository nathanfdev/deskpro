<?php

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
