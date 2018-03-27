<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketDupeValidator.
 */
class TicketDupeValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketDupe) {
            throw new UnexpectedTypeException($constraint, TicketDupe::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof Ticket) {
            throw new UnexpectedTypeException($value, Ticket::class);
        }

        $value->recomputeHash();

        /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
        $ticketRepo = $this->em->getRepository(Ticket::class);
        if ($ticketRepo->checkDupeTicket($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketDupe::DUPE_TICKET)
                ->addViolation()
            ;
        }
    }
}
