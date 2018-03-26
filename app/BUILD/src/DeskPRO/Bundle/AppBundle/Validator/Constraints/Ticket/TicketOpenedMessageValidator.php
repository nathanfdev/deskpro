<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketOpenedMessageValidator.
 */
class TicketOpenedMessageValidator extends ConstraintValidator
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * Constructor.
     *
     * @param AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketOpenedMessage) {
            throw new UnexpectedTypeException($constraint, TicketOpenedMessage::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof TicketMessage) {
            throw new UnexpectedTypeException($value, TicketMessage::class);
        }

        $ticket = $value->getTicket();
        if (!$ticket instanceof Ticket) {
            throw new UnexpectedTypeException($value, Ticket::class);
        }

        if ($this->appEnv->hasRuntimeVar('dp.is_importing')) {
            // allow to create ticket messages for archived tickets within import process
            return;
        }

        if ($ticket->isArchived()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketOpenedMessage::TICKET_OPENED)
                ->addViolation()
            ;
        }
    }
}
