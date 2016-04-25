<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
