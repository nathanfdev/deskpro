<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketLinkValidator.
 */
class TicketLinkValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketLink) {
            throw new UnexpectedTypeException($constraint, TicketLink::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof Ticket) {
            throw new UnexpectedTypeException($value, Ticket::class);
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        if ($value->getParentTicket() && $value->getParentTicket() === $value) {
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketLink::LINK_ITSELF)
                ->atPath('parent_ticket')
                ->addViolation()
            ;
        } elseif ($value->getChildrenTickets()->contains($value)) {
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketLink::LINK_ITSELF)
                ->atPath('children_tickets')
                ->addViolation()
            ;
        }
    }
}
