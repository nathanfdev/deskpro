<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Orb\Data\Countries;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CountryCodeValidator.
 */
class CountryCodeValidator extends ChoiceValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CountryCode) {
            throw new UnexpectedTypeException($constraint, CountryCode::class);
        }

        if (!$value) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!Countries::isCountry(strtoupper($value))) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(CountryCode::INVALID_COUNTRY_CODE)
                ->addViolation()
            ;
        }
    }
}
