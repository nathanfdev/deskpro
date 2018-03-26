<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LeafProductValidator.
 */
class TicketLeafProductValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_null($value)) {
            return;
        }

        if (!$value instanceof Product) {
            throw new UnexpectedTypeException($value, Product::class);
        }
        if (!$constraint instanceof TicketLeafProduct) {
            throw new UnexpectedTypeException($constraint, TicketLeafProduct::class);
        }

        if ($value->getChildren()->count()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketLeafProduct::NOT_ASSIGNABLE_PRODUCT)
                ->addViolation()
            ;
        }
    }
}
