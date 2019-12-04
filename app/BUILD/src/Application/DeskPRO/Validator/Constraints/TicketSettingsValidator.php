<?php

namespace Application\DeskPRO\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class TicketSettingsValidator
 *
 * @package Application\DeskPRO\Validator\Constraints
 */
class TicketSettingsValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof TicketSettingsRefConstraint &&
            $this->context->getObject()->ref_custom_enabled
        ) {
            $matches = [];
            if (preg_match($constraint->getInvalidTokenRegexp(), $value, $matches)) {
                $this->context
                    ->buildViolation($constraint->message, [
                        'type'    => TicketSettingsRefConstraint::TYPE_TOKEN,
                        'pattern' => $matches[0]
                    ])
                    ->setCode($constraint->invalidTokenCode)
                    ->addViolation();
            }

            if (preg_match($constraint->getInvalidCharRegexp(), $value, $matches)) {
                $this->context
                    ->buildViolation($constraint->message, [
                        'type'    => TicketSettingsRefConstraint::TYPE_CHAR,
                        'pattern' => $matches[0]
                    ])
                    ->setCode($constraint->invalidCharCode)
                    ->addViolation();
            }
        }
    }
}
