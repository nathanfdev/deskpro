<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Entity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PhoneNumberValidator.
 */
class PhoneNumberValidator extends AbstractNumberValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PhoneNumber) {
            throw new UnexpectedTypeException($constraint, PhoneNumber::class);
        }

        if (!$value) {
            return;
        }
        if ($value instanceof Entity\AbstractPhoneNumber) {
            $checkValue = $value->getNumberFormatted();
        } elseif (is_scalar($value)) {
            $checkValue = $value;
        } else {
            throw new UnexpectedTypeException($value, implode(', ', ['string', PhoneNumber::class]));
        }

        $this->validatePhoneNumber($checkValue, $constraint);
    }
}
