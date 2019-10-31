<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CallNumberValidator.
 */
class CallNumberValidator extends AbstractNumberValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CallNumber) {
            throw new UnexpectedTypeException($constraint, CallNumber::class);
        }

        if (!$value) {
            return;
        }

        if (!is_string($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;
        }

        if (preg_match('/^sip:/', $value)) {
            $this->validateSip($value, $constraint);
        } else {
            $this->validatePhoneNumber($value, $constraint);
        }
    }
}
