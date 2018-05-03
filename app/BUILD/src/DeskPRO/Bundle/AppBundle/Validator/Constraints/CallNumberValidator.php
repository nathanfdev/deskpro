<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Egulias\EmailValidator\EmailValidator;
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

    /**
     * @param string     $value
     * @param CallNumber $constraint
     */
    protected function validateSip($value, CallNumber $constraint)
    {
        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        if (preg_match('/\s/', $value)) {
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;
        $email   = preg_replace('/^sip:/', '', $value);

        if (!$email) {
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;
        }

        $strictValidator = new EmailValidator();
        if (!preg_match('/^.+\@\S+\.\S+$/', $email) || !$strictValidator->isValid($email, false, true)) {
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;
        }
    }
}
