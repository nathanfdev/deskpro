<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketDupeValidator.
 */
class TicketDupeMessageValidator extends ConstraintValidator
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
        if (!$constraint instanceof TicketDupeMessage) {
            throw new UnexpectedTypeException($constraint, TicketDupeMessage::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof TicketMessage) {
            throw new UnexpectedTypeException($value, TicketMessage::class);
        }

        // don't check already created messages
        if ($value->getId()) {
            return;
        }

        // check duplicate messages only for persisted tickets
        // otherwise check duplicate tickets in a separate validator, see TicketDupeValidator
        if (!$value->getTicket() || !$value->getTicket()->getId()) {
            return;
        }
        if (!$value->getPerson() || !$value->getPerson()->getId()) {
            return;
        }

        $value->resetHashCode();

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $messageRepo */
        $messageRepo = $this->em->getRepository(TicketMessage::class);
        if ($messageRepo->checkDupeMessage($value, $value->getTicket())) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketDupeMessage::DUPE_TICKET_MESSAGE)
                ->addViolation()
            ;
        }
    }
}
