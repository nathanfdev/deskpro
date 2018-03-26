<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\TicketCategory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketLeafCategoryValidator.
 */
class TicketLeafCategoryValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_null($value)) {
            return;
        }

        if (!$value instanceof TicketCategory) {
            throw new UnexpectedTypeException($value, TicketCategory::class);
        }
        if (!$constraint instanceof TicketLeafCategory) {
            throw new UnexpectedTypeException($constraint, TicketLeafCategory::class);
        }

        if ($value->getChildren()->count()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketLeafCategory::NOT_ASSIGNABLE_CATEGORY)
                ->addViolation()
            ;
        }
    }
}
